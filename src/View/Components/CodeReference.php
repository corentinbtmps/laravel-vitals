<?php

declare(strict_types=1);

namespace LaravelVitals\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class CodeReference extends Component
{
    /**
     * @param array{file: string, line_start: int, line_end: int, snippet: string, hint?: string|null} $ref
     */
    public function __construct(
        public array $ref,
    ) {
    }

    public function render(): View
    {
        return view('vitals::components.code-reference');
    }
}
