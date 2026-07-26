<?php

declare(strict_types=1);

use LaravelVitals\Database\SqlIndexParser;

beforeEach(function (): void {
    $this->parser = new SqlIndexParser();
});

it('extracts the table and a WHERE column', function (): void {
    $parsed = $this->parser->parse('select * from `users` where `email` = ?');

    expect($parsed)->not->toBeNull()
        ->and($parsed->table)->toBe('users')
        ->and($parsed->columns)->toBe(['email' => 'where']);
});

it('handles table aliases for WHERE and ORDER BY', function (): void {
    $parsed = $this->parser->parse('select * from users u where u.status = ? order by u.created_at desc');

    expect($parsed->table)->toBe('users')
        ->and($parsed->columns)->toBe(['status' => 'where', 'created_at' => 'order']);
});

it('captures IN predicates', function (): void {
    $parsed = $this->parser->parse('select id from orders where status in (?, ?, ?)');

    expect($parsed->columns)->toBe(['status' => 'where']);
});

it('only keeps join columns that belong to the primary table', function (): void {
    // Primary table posts (alias p); the users-side column must be ignored.
    $parsed = $this->parser->parse('select * from posts p join users u on u.id = p.user_id where u.role = ?');

    expect($parsed->table)->toBe('posts')
        ->and($parsed->columns)->toBe(['user_id' => 'join']);
});

it('prefers WHERE over ORDER BY when a column appears in both', function (): void {
    $parsed = $this->parser->parse('select * from events where kind = ? order by kind asc');

    expect($parsed->columns)->toBe(['kind' => 'where']);
});

it('ignores SQL keywords as columns', function (): void {
    $parsed = $this->parser->parse('select * from users where id = ? and name = ?');

    expect($parsed->columns)->toBe(['id' => 'where', 'name' => 'where']);
});

it('returns null for non-SELECT statements', function (): void {
    expect($this->parser->parse('update users set name = ? where id = ?'))->toBeNull();
});

it('returns null for a derived-table FROM', function (): void {
    expect($this->parser->parse('select * from (select * from users) t where t.id = ?'))->toBeNull();
});

it('returns a table with no candidates when there is nothing to index', function (): void {
    $parsed = $this->parser->parse('select * from settings');

    expect($parsed->table)->toBe('settings')
        ->and($parsed->hasCandidates())->toBeFalse();
});
