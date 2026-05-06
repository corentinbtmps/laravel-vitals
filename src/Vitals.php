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
    public function audit(Url|string $urlOrLabel, ?string $device = null, bool $sync = true): Audit
    {
        $this->seeder->sync();

        $url = $urlOrLabel instanceof Url
            ? $urlOrLabel
            : Url::where('label', $urlOrLabel)->firstOrFail();

        $device ??= $url->device === 'both' ? 'mobile' : $url->device;

        if (! in_array($device, ['mobile', 'desktop'], true)) {
            throw new InvalidArgumentException("Device must be 'mobile' or 'desktop'.");
        }

        $audit = Audit::create([
            'id'     => Str::uuid()->toString(),
            'url_id' => $url->id,
            'driver' => $this->driverOverride ?? (string) config('vitals.driver', 'auto'),
            'device' => $device,
            'status' => 'pending',
        ]);

        $this->driverOverride = null;

        $job = new RunAuditJob($audit->id);

        if ($sync) {
            $job->handle(
                app(LighthouseDriver::class),
                app(ReportRepository::class),
            );
        } else {
            dispatch($job);
        }

        return $audit;
    }

    /**
     * Audit every enabled URL via Bus::batch.
     */
    public function auditAll(?string $device = null): Batch
    {
        $this->seeder->sync();

        $jobs = [];

        foreach (Url::query()->where('enabled', true)->get() as $url) {
            $effectiveDevice = $device ?? ($url->device === 'both' ? 'mobile' : $url->device);

            $audit = Audit::create([
                'id'     => Str::uuid()->toString(),
                'url_id' => $url->id,
                'driver' => $this->driverOverride ?? (string) config('vitals.driver', 'auto'),
                'device' => $effectiveDevice,
                'status' => 'pending',
            ]);

            $jobs[] = new RunAuditJob($audit->id);
        }

        $this->driverOverride = null;

        return Bus::batch($jobs)->name('vitals-full-audit')->allowFailures()->dispatch();
    }
}
