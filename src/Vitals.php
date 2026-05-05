<?php

declare(strict_types=1);

namespace LaravelVitals;

use Closure;

/**
 * Public service object. Bound as a singleton in VitalsServiceProvider and
 * exposed through the LaravelVitals\Facades\Vitals facade.
 *
 * Concrete audit / driver / budget methods are added in plans 2–5; this class
 * provides only the cross-cutting hooks (authorization) at the foundation
 * stage so downstream layers have a stable extension point.
 */
final class Vitals
{
    private ?Closure $authorizeCallback = null;

    /**
     * Register the closure used by the dashboard authorize middleware.
     */
    public function authorize(Closure $callback): self
    {
        $this->authorizeCallback = $callback;

        return $this;
    }

    /**
     * The closure registered by authorize() if any, otherwise null.
     */
    public function authorizeCallback(): ?Closure
    {
        return $this->authorizeCallback;
    }
}
