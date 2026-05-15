<?php

declare(strict_types=1);

namespace LaravelVitals\Enums;

use Carbon\Carbon;

/**
 * Time-window period selector used across Overview, UrlDetail, RUM and Queries pages.
 */
enum Period: string
{
    case H24 = '24h';
    case D7  = '7d';
    case D30 = '30d';
    case D90 = '90d';
    case Y1  = '1y';
    case All = 'all';

    /**
     * Carbon cutoff date for the start of this period (null = no cutoff for All).
     */
    public function cutoff(): ?Carbon
    {
        return match ($this) {
            self::H24 => now()->subHours(24),
            self::D7  => now()->subDays(7),
            self::D30 => now()->subDays(30),
            self::D90 => now()->subDays(90),
            self::Y1  => now()->subYear(),
            self::All => null,
        };
    }

    /**
     * Human-readable label (translated).
     */
    public function label(): string
    {
        return __('vitals::vitals.period.' . $this->value);
    }

    /**
     * Short display string shown on the period toggle button.
     */
    public function buttonLabel(): string
    {
        return match ($this) {
            self::H24 => '24h',
            self::D7  => '7d',
            self::D30 => '30d',
            self::D90 => '90d',
            self::Y1  => '1y',
            self::All => __('vitals::vitals.period.all'),
        };
    }

    /**
     * Returns all periods in display order.
     *
     * @return list<self>
     */
    public static function ordered(): array
    {
        return [self::H24, self::D7, self::D30, self::D90, self::Y1, self::All];
    }

    /**
     * Max number of days this period spans (null for All, ~1 for H24).
     */
    public function days(): ?int
    {
        return match ($this) {
            self::H24 => 1,
            self::D7  => 7,
            self::D30 => 30,
            self::D90 => 90,
            self::Y1  => 365,
            self::All => null,
        };
    }

    /**
     * Filters Period::ordered() to those that fit within the host app's retention window.
     * A period is offered only when its span is <= retention. 'All' is offered when retention
     * is effectively unbounded (>= 1y) since otherwise it would mislead.
     *
     * @return list<self>
     */
    public static function availableFor(int $retentionDays): array
    {
        return array_values(array_filter(
            self::ordered(),
            fn (self $p): bool => $p === self::All
                ? $retentionDays >= 365
                : ($p->days() ?? 0) <= $retentionDays,
        ));
    }

    /**
     * Conservative default for the package: the smaller of audits + RUM retentions.
     */
    public static function effectiveRetentionDays(): int
    {
        return min(
            (int) config('vitals.retention.days', 90),
            (int) config('vitals.rum.retention_days', 90),
        );
    }
}
