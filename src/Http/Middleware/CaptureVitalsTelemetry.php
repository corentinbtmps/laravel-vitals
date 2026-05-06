<?php

declare(strict_types=1);

namespace LaravelVitals\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LaravelVitals\Jobs\PersistTelemetryJob;
use LaravelVitals\Telemetry\SamplingDecider;
use LaravelVitals\Telemetry\SignedHeader;
use LaravelVitals\Telemetry\TelemetryRecorder;
use Symfony\Component\HttpFoundation\Response;

/**
 * Captures backend telemetry for a request, gated on either:
 *   1. A valid X-Vitals-Audit-Id signed header (audit-driven), or
 *   2. The always_capture opt-in (sampled traffic).
 *
 * On the fast path (no header, no opt-in) this middleware does a constant
 * amount of work (one header lookup + a few conditionals) and returns
 * immediately. Production overhead is negligible.
 */
final class CaptureVitalsTelemetry
{
    public function handle(Request $request, Closure $next): Response
    {
        $rawHeader = $request->header('X-Vitals-Audit-Id');

        $auditId  = SignedHeader::verify(is_string($rawHeader) ? $rawHeader : null);
        $sampled  = $auditId === null && SamplingDecider::shouldCapture();

        if ($auditId === null && ! $sampled) {
            return $next($request);
        }

        $recorder = new TelemetryRecorder();
        $recorder->start($auditId, sampled: $sampled);
        app()->instance('vitals.active-recorder', $recorder);

        $response = null;

        try {
            $response = $next($request);
        } finally {
            $routeName = $request->route()?->getName();
            $status = $response instanceof Response
                ? $response->getStatusCode()
                : 500;

            $snapshot = $recorder->snapshot($status, is_string($routeName) ? $routeName : null);

            app()->forgetInstance('vitals.active-recorder');

            if (app()->runningUnitTests()) {
                (new PersistTelemetryJob($snapshot))->handle();
            } else {
                dispatch(new PersistTelemetryJob($snapshot))->afterResponse();
            }
        }

        return $response ?? throw new \RuntimeException('Response was null after middleware execution');
    }
}
