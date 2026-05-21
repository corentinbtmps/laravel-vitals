<?php

declare(strict_types=1);

namespace LaravelVitals;

use Closure;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LaravelVitals\Contracts\LighthouseDriver;
use LaravelVitals\Drivers\LighthouseDriverManager;
use LaravelVitals\Enums\AuditStatus;
use LaravelVitals\Enums\Device;
use LaravelVitals\Jobs\RunAuditJob;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use LaravelVitals\Storage\ReportRepository;
use LaravelVitals\Support\UrlSeeder;

/**
 * Public service object. Exposes the package's main API: dashboard
 * authorization plus audit dispatch.
 */
final class Vitals
{
    private ?Closure $authorizeCallback = null;

    private ?string $driverOverride = null;

    public function __construct(
        private readonly LighthouseDriverManager $drivers,
        private readonly UrlSeeder $seeder,
    ) {
    }

    public function authorize(Closure $callback): self
    {
        $this->authorizeCallback = $callback;
        return $this;
    }

    public function authorizeCallback(): ?Closure
    {
        return $this->authorizeCallback;
    }

    /**
     * Override the LighthouseDriver for the next call.
     */
    public function driver(string $name): self
    {
        $this->driverOverride = $name;
        return $this;
    }

    /**
     * Run a single audit. Synchronous execution by default; pass $sync = false
     * to dispatch via the queue instead.
     */
    public function audit(Url|string $urlOrLabel, ?Device $device = null, bool $sync = true): Audit
    {
        $this->seeder->sync();

        $url = $urlOrLabel instanceof Url
            ? $urlOrLabel
            : Url::where('label', $urlOrLabel)->firstOrFail();

        if ($device === null) {
            if ($url->device === Device::Both) {
                $this->audit($url, Device::Mobile, $sync);
                return $this->audit($url, Device::Desktop, $sync);
            }
            $device = $url->device;
        }

        if (! in_array($device, [Device::Mobile, Device::Desktop], true)) {
            throw new InvalidArgumentException("Device must be 'mobile' or 'desktop'.");
        }

        $driverName = $this->driverOverride;

        $audit = Audit::create([
            'id'     => Str::uuid()->toString(),
            'url_id' => $url->id,
            'driver' => $driverName ?? (string) config('vitals.driver', 'auto'),
            'device' => $device,
            'status' => AuditStatus::Pending,
        ]);

        $this->driverOverride = null;

        $job = new RunAuditJob($audit->id);

        if ($sync) {
            $driver = $driverName !== null
                ? $this->drivers->driver($driverName)
                : app(LighthouseDriver::class);

            $job->handle(
                $driver,
                app(\LaravelVitals\Storage\ReportRepository::class),
                app(\LaravelVitals\Recommendations\RecommendationBuilder::class),
                app(\LaravelVitals\Notifications\Channels\VitalsNotifier::class),
                app(\LaravelVitals\Seo\SeoAuditor::class),
            );
        } else {
            dispatch($job);
        }

        return $audit;
    }

    /**
     * Audit every enabled URL via Bus::batch.
     */
    public function auditAll(?Device $device = null): Batch
    {
        $this->seeder->sync();

        $jobs = [];

        foreach (Url::query()->where('enabled', true)->get() as $url) {
            $devices = $device !== null ? [$device] : ($url->device === Device::Both ? [Device::Mobile, Device::Desktop] : [$url->device]);

            foreach ($devices as $effectiveDevice) {
                $audit = Audit::create([
                    'id'     => Str::uuid()->toString(),
                    'url_id' => $url->id,
                    'driver' => $this->driverOverride ?? (string) config('vitals.driver', 'auto'),
                    'device' => $effectiveDevice,
                    'status' => AuditStatus::Pending,
                ]);

                $jobs[] = new RunAuditJob($audit->id);
            }
        }

        $this->driverOverride = null;

        return Bus::batch($jobs)->name('vitals-full-audit')->allowFailures()->dispatch();
    }
}
