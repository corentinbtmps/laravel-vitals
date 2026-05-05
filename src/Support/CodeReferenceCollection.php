<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * Type-safe collection of CodeReference objects.
 *
 * @implements IteratorAggregate<int, CodeReference>
 */
final readonly class CodeReferenceCollection implements Countable, IteratorAggregate
{
    /** @var array<int, CodeReference> */
    private array $items;

    /**
     * @param array<int, CodeReference> $items
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            if (! $item instanceof CodeReference) {
                throw new InvalidArgumentException(
                    'CodeReferenceCollection accepts only CodeReference instances.'
                );
            }
        }

        $this->items = array_values($items);
    }

    /**
     * @param array<int, array{file: string, line_start: int, line_end: int, snippet: string, hint?: string|null}> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(array_map(
            CodeReference::fromArray(...),
            $data,
        ));
    }

    /**
     * @return array<int, array{file: string, line_start: int, line_end: int, snippet: string, hint: string|null}>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (CodeReference $ref): array => $ref->toArray(),
            $this->items,
        );
    }

    /**
     * @return array<int, CodeReference>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->items);
    }
}
