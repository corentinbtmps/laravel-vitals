<?php

declare(strict_types=1);

namespace LaravelVitals\Enums;

/**
 * Device target for a Vitals audit.
 */
enum Device: string
{
    case Mobile  = 'mobile';
    case Desktop = 'desktop';
    case Both    = 'both';

    /**
     * Heroicons icon name for this device.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Mobile  => 'device-phone-mobile',
            self::Desktop => 'computer-desktop',
            self::Both    => 'device-phone-mobile',
        };
    }

    /**
     * Translated human label.
     */
    public function label(): string
    {
        return __('vitals::vitals.device.' . $this->value);
    }

    /**
     * Returns the concrete devices to audit when device = Both.
     *
     * @return list<self>
     */
    public function expand(): array
    {
        return match ($this) {
            self::Both    => [self::Mobile, self::Desktop],
            default       => [$this],
        };
    }
}
