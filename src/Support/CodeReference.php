<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

/**
 * A pointer to a region of code in the host application that motivates
 * a recommendation.
 */
final class CodeReference
{
    public function __construct(
        public readonly string $file,
        public readonly int $lineStart,
        public readonly int $lineEnd,
        public readonly string $snippet,
        public readonly ?string $hint = null,
    ) {
    }

    /**
     * @param array{file: string, line_start: int, line_end: int, snippet: string, hint?: string|null} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            file: $data['file'],
            lineStart: $data['line_start'],
            lineEnd: $data['line_end'],
            snippet: $data['snippet'],
            hint: $data['hint'] ?? null,
        );
    }

    /**
     * @return array{file: string, line_start: int, line_end: int, snippet: string, hint: string|null}
     */
    public function toArray(): array
    {
        return [
            'file'       => $this->file,
            'line_start' => $this->lineStart,
            'line_end'   => $this->lineEnd,
            'snippet'    => $this->snippet,
            'hint'       => $this->hint,
        ];
    }
}
