<?php

declare(strict_types=1);

namespace LaravelVitals\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelVitals\Models\RumEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles Real User Monitoring beacon ingestion.
 *
 * Privacy: No IP addresses are stored. Only metric values, URL paths,
 * device type, connection hint and user-agent string are persisted.
 * No cookies or fingerprinting beyond the UA string.
 */
final class RumController extends Controller
{
    public function ingest(Request $request): Response
    {
        if (! (bool) config('vitals.rum.enabled', true)) {
            return response()->noContent();
        }

        $data = $request->validate([
            'url'             => 'required|string|max:2048',
            'metric'          => 'required|in:LCP,INP,CLS,TTFB,FCP',
            'value'           => 'required|numeric',
            'rating'          => 'nullable|in:good,needs-improvement,poor',
            'navigation_type' => 'nullable|string|max:32',
            'attribution'     => 'nullable|array',
            'device'          => 'required|in:mobile,desktop',
            'user_agent'      => 'nullable|string|max:512',
            'connection'      => 'nullable|string|max:32',
            'timestamp'       => 'required|integer',
        ]);

        RumEvent::create([
            'url'             => $data['url'],
            'metric'          => $data['metric'],
            'value'           => $data['value'],
            'rating'          => $data['rating'] ?? null,
            'device'          => $data['device'],
            'navigation_type' => $data['navigation_type'] ?? null,
            'connection'      => $data['connection'] ?? null,
            'attribution'     => $data['attribution'] ?? null,
            'user_agent'      => $data['user_agent'] ?? null,
            'occurred_at'     => Carbon::createFromTimestampMs((int) $data['timestamp']),
        ]);

        return response()->noContent();
    }
}
