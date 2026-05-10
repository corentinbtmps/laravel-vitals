<?php

declare(strict_types=1);

namespace LaravelVitals\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Models\Audit;
use LaravelVitals\Storage\ReportRepository;
use LaravelVitals\Support\AuditException;
use LaravelVitals\Support\AuditOptions;
use Throwable;

/**
 * Runs a single audit: resolves the driver, invokes it with a signed
 * X-Vitals-Audit-Id header, persists the raw report, and updates the
 * vitals_audits row with scores + Core Web Vitals + status.
 *
 * The signed header is what allows the backend telemetry middleware
 * (Plan 3) to correlate a request back to this audit.
 */
final class RunAuditJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $auditId,
    ) {
    }

    public function handle(
        LighthouseDriver $driver,
        ReportRepository $reports,
        \LaravelVitals\Recommendations\RecommendationBuilder $builder,
        \LaravelVitals\Notifications\Channels\VitalsNotifier $notifier,
    ): void
    {
        $audit = Audit::with('url')->findOrFail($this->auditId);

        $audit->update(['status' => 'running', 'started_at' => now()]);

        $signature = hash_hmac('sha256', $audit->id, (string) config('app.key'));
        $headerValue = $audit->id . '.' . $signature;

        $options = AuditOptions::default()
            ->withDevice($audit->device)
            ->withExtraHeader('X-Vitals-Audit-Id', $headerValue)
            ->withAuditId($audit->id);

        $url = $audit->url;

        if ($url === null) {
            $audit->update([
                'status' => 'failed',
                'error'  => 'Associated URL record not found.',
                'completed_at' => now(),
            ]);
            throw new AuditException(
                "Audit {$audit->id}: associated URL record not found.",
                auditId: $audit->id,
                driver: $audit->driver,
            );
        }

        try {
            $report = $driver->audit($url, $options);

            $path    = $reports->store($audit->id, $report->rawJson);
            $details = \LaravelVitals\Support\LighthouseReport::extractDetails($report->rawJson);

            $audit->update([
                'status'               => 'completed',
                'score_performance'    => $report->scores['performance'],
                'score_accessibility'  => $report->scores['accessibility'],
                'score_best_practices' => $report->scores['best_practices'],
                'score_seo'            => $report->scores['seo'],
                'lcp_ms'               => $report->metrics['lcp_ms'],
                'cls'                  => $report->metrics['cls'],
                'inp_ms'               => $report->metrics['inp_ms'],
                'ttfb_ms'              => $report->metrics['ttfb_ms'],
                'fcp_ms'               => $report->metrics['fcp_ms'],
                'si_ms'                => $report->metrics['si_ms'],
                'tbt_ms'               => $report->metrics['tbt_ms'],
                'report_path'          => $path,
                'completed_at'         => now(),
                'details'              => $details,
            ]);

            $telemetry = \LaravelVitals\Models\BackendTelemetry::where('audit_id', $audit->id)->first();
            $builder->buildFor($audit, $report, $telemetry);

            $notifier->send('audit_completed', new \LaravelVitals\Notifications\AuditCompleted($audit->refresh()));
        } catch (AuditException $e) {
            $audit->update([
                'status' => 'failed',
                'error'  => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        } catch (Throwable $e) {
            $audit->update([
                'status' => 'failed',
                'error'  => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw new AuditException(
                "Unexpected error during audit {$audit->id}: " . $e->getMessage(),
                auditId: $audit->id,
                driver: $audit->driver,
                previous: $e,
            );
        }
    }
}
