<?php

declare(strict_types=1);

namespace LaravelVitals\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dashboard authorization gate. Resolves through the `viewVitals` ability
 * registered in the service provider, which itself defers to the closure
 * configured via Vitals::authorize().
 */
final class Authorize
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Gate::denies('viewVitals', [$request->user()])) {
            abort(403);
        }

        return $next($request);
    }
}
