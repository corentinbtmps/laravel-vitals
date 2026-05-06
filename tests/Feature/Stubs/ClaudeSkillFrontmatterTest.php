<?php

declare(strict_types=1);

it('the Claude skill stub has valid YAML frontmatter with required keys', function (): void {
    $path = __DIR__ . '/../../../stubs/claude-skills/SKILL.md';
    expect(file_exists($path))->toBeTrue();

    $contents = file_get_contents($path);

    expect($contents)->toStartWith('---');

    if (! preg_match('/^---\s*\n(.+?)\n---/s', $contents, $matches)) {
        $this->fail('Frontmatter not found');
    }

    expect($matches[1])
        ->toContain('name: laravel-vitals')
        ->and($matches[1])->toContain('description:');
});

it('the Claude skill body documents core operations', function (): void {
    $contents = file_get_contents(__DIR__ . '/../../../stubs/claude-skills/SKILL.md');

    expect($contents)
        ->toContain('When to invoke')
        ->and($contents)->toContain('How to investigate')
        ->and($contents)->toContain('code_references');
});
