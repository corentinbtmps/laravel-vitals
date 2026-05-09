<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Notification;
use LaravelVitals\Budgets\BudgetViolations;
use LaravelVitals\Models\Audit;
use LaravelVitals\Models\Url;
use LaravelVitals\Notifications\BudgetViolated;
use LaravelVitals\Notifications\RegressionDetected;

beforeEach(function (): void {
    config()->set('vitals.notifications', [
        'enabled'  => true,
        'channels' => ['slack'],
        'slack'    => ['webhook_url' => 'https://hooks.slack.com/test'],
        'triggers' => [
            'audit_completed'    => true,
            'budget_violation'   => true,
            'regression_detected' => true,
        ],
    ]);
});

// ── helper ────────────────────────────────────────────────────────────────────

function slackMakeUrl(): Url
{
    return Url::create(['label' => 'Home', 'path' => '/', 'device' => 'mobile', 'enabled' => true]);
}

function slackMakeAudit(Url $url, array $attrs = []): Audit
{
    return Audit::create(array_merge([
        'url_id'            => $url->id,
        'driver'            => 'stub',
        'device'            => 'mobile',
        'status'            => 'completed',
        'score_performance' => 88,
        'score_accessibility' => 92,
        'lcp_ms'            => 2200.0,
        'inp_ms'            => 150.0,
        'cls'               => 0.04,
        'ttfb_ms'           => 300.0,
        'completed_at'      => now(),
    ], $attrs));
}

// ── BudgetViolated threading ──────────────────────────────────────────────────

it('BudgetViolated toSlack includes thread_ts when audit has slack_message_ts', function (): void {
    $url   = slackMakeUrl();
    $audit = slackMakeAudit($url, ['slack_message_ts' => '1234567890.123456']);

    $violations = new BudgetViolations([
        ['metric' => 'lcp_ms', 'actual' => 5000, 'threshold' => 2500, 'severity' => 'critical'],
    ]);

    $notification = new BudgetViolated($audit, $violations);
    $notifiable   = new \stdClass();

    $slackMessage = $notification->toSlack($notifiable);

    // toArray() on SlackMessage converts to the payload sent to Slack.
    $payload = $slackMessage->toArray();

    expect($payload['thread_ts'])->toBe('1234567890.123456');
});

it('BudgetViolated toSlack omits thread_ts when no prior Slack message', function (): void {
    $url   = slackMakeUrl();
    $audit = slackMakeAudit($url);  // no slack_message_ts

    $violations = new BudgetViolations([
        ['metric' => 'lcp_ms', 'actual' => 5000, 'threshold' => 2500, 'severity' => 'warning'],
    ]);

    $notification = new BudgetViolated($audit, $violations);
    $payload      = $notification->toSlack(new \stdClass())->toArray();

    expect(array_key_exists('thread_ts', $payload))->toBeFalse();
});

// ── RegressionDetected threading ──────────────────────────────────────────────

it('RegressionDetected toSlack includes thread_ts when URL has a prior Slack audit', function (): void {
    $url = slackMakeUrl();
    slackMakeAudit($url, ['slack_message_ts' => '9876543210.654321']);

    $notification = new RegressionDetected(
        url: $url,
        baselineScore: 90,
        currentScore: 78,
        dropPercent: 13.3,
    );

    $payload = $notification->toSlack(new \stdClass())->toArray();

    expect($payload['thread_ts'])->toBe('9876543210.654321');
});

it('RegressionDetected toSlack omits thread_ts when no Slack audit exists', function (): void {
    $url = slackMakeUrl();
    slackMakeAudit($url);  // no slack_message_ts

    $notification = new RegressionDetected(
        url: $url,
        baselineScore: 90,
        currentScore: 78,
        dropPercent: 13.3,
    );

    $payload = $notification->toSlack(new \stdClass())->toArray();

    expect(array_key_exists('thread_ts', $payload))->toBeFalse();
});

// ── Latest ts wins ────────────────────────────────────────────────────────────

it('RegressionDetected picks the most-recent slack_message_ts', function (): void {
    $url = slackMakeUrl();

    slackMakeAudit($url, [
        'slack_message_ts' => '1000000000.000001',
        'completed_at'     => now()->subHours(2),
    ]);
    slackMakeAudit($url, [
        'slack_message_ts' => '2000000000.000002',
        'completed_at'     => now()->subHour(),
    ]);

    $notification = new RegressionDetected($url, 90, 78, 13.3);
    $payload      = $notification->toSlack(new \stdClass())->toArray();

    expect($payload['thread_ts'])->toBe('2000000000.000002');
});
