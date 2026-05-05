<?php

declare(strict_types=1);

use LaravelVitals\Support\CodeReference;
use LaravelVitals\Support\CodeReferenceCollection;

it('round-trips through array form', function (): void {
    $a = new CodeReference('a.blade.php', 1, 2, '<a>');
    $b = new CodeReference('b.blade.php', 3, 4, '<b>');

    $collection = new CodeReferenceCollection([$a, $b]);

    $array = $collection->toArray();
    expect($array)->toHaveCount(2)
        ->and(CodeReferenceCollection::fromArray($array)->all())->toEqual([$a, $b]);
});

it('rejects non-CodeReference items at construction', function (): void {
    expect(fn (): \LaravelVitals\Support\CodeReferenceCollection => new CodeReferenceCollection([new stdClass()]))
        ->toThrow(\InvalidArgumentException::class);
});
