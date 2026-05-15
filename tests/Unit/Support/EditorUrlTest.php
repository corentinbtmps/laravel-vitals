<?php

declare(strict_types=1);

use LaravelVitals\Support\EditorUrl;

// ── Preset compilation ──────────────────────────────────────────────────────

it('builds a vscode URL correctly', function (): void {
    config(['vitals.ui.editor' => 'vscode', 'vitals.ui.editor_url_template' => null]);

    $url = EditorUrl::for('/var/www/app/app/Models/User.php', 42);

    expect($url)->toBe('vscode://file//var/www/app/app/Models/User.php:42');
});

it('builds a cursor URL correctly', function (): void {
    config(['vitals.ui.editor' => 'cursor', 'vitals.ui.editor_url_template' => null]);

    $url = EditorUrl::for('/var/www/app/routes/web.php', 10);

    expect($url)->toBe('cursor://file//var/www/app/routes/web.php:10');
});

it('builds a phpstorm URL correctly', function (): void {
    config(['vitals.ui.editor' => 'phpstorm', 'vitals.ui.editor_url_template' => null]);

    $url = EditorUrl::for('/var/www/app/app/Http/Controllers/HomeController.php', 25);

    expect($url)->toBe('phpstorm://open?file=/var/www/app/app/Http/Controllers/HomeController.php&line=25');
});

it('builds an idea URL correctly', function (): void {
    config(['vitals.ui.editor' => 'idea', 'vitals.ui.editor_url_template' => null]);

    $url = EditorUrl::for('/var/www/app/app/Services/AuditService.php', 5);

    expect($url)->toBe('idea://open?file=/var/www/app/app/Services/AuditService.php&line=5');
});

it('builds a sublime URL correctly', function (): void {
    config(['vitals.ui.editor' => 'sublime', 'vitals.ui.editor_url_template' => null]);

    $url = EditorUrl::for('/var/www/app/app/Models/Post.php', 1);

    expect($url)->toBe('subl://open?url=file:///var/www/app/app/Models/Post.php&line=1');
});

it('builds an atom URL correctly', function (): void {
    config(['vitals.ui.editor' => 'atom', 'vitals.ui.editor_url_template' => null]);

    $url = EditorUrl::for('/var/www/app/config/app.php', 30);

    expect($url)->toBe('atom://core/open/file?filename=/var/www/app/config/app.php&line=30');
});

it('builds a textmate URL correctly', function (): void {
    config(['vitals.ui.editor' => 'textmate', 'vitals.ui.editor_url_template' => null]);

    $url = EditorUrl::for('/var/www/app/app/Models/Audit.php', 15);

    expect($url)->toBe('txmt://open?url=file:///var/www/app/app/Models/Audit.php&line=15');
});

it('builds a macvim URL correctly', function (): void {
    config(['vitals.ui.editor' => 'macvim', 'vitals.ui.editor_url_template' => null]);

    $url = EditorUrl::for('/var/www/app/app/Models/Audit.php', 7);

    expect($url)->toBe('mvim://open/?url=file:///var/www/app/app/Models/Audit.php&line=7');
});

it('builds an emacs URL correctly', function (): void {
    config(['vitals.ui.editor' => 'emacs', 'vitals.ui.editor_url_template' => null]);

    $url = EditorUrl::for('/var/www/app/app/Models/Audit.php', 3);

    expect($url)->toBe('emacs://open?url=file:///var/www/app/app/Models/Audit.php&line=3');
});

it('builds a nova URL correctly', function (): void {
    config(['vitals.ui.editor' => 'nova', 'vitals.ui.editor_url_template' => null]);

    $url = EditorUrl::for('/var/www/app/app/Models/Audit.php', 9);

    expect($url)->toBe('nova://open?path=/var/www/app/app/Models/Audit.php&line=9');
});

it('builds a zed URL correctly', function (): void {
    config(['vitals.ui.editor' => 'zed', 'vitals.ui.editor_url_template' => null]);

    $url = EditorUrl::for('/var/www/app/app/Models/Audit.php', 20);

    expect($url)->toBe('zed://file//var/www/app/app/Models/Audit.php:20');
});

// ── Custom template ─────────────────────────────────────────────────────────

it('custom template overrides the preset', function (): void {
    config([
        'vitals.ui.editor'              => 'vscode',
        'vitals.ui.editor_url_template' => 'myeditor://{path}:{line}',
    ]);

    $url = EditorUrl::for('/var/www/app/app/Models/User.php', 99);

    expect($url)->toBe('myeditor:///var/www/app/app/Models/User.php:99');
});

it('uses custom template when no preset is set', function (): void {
    config([
        'vitals.ui.editor'              => null,
        'vitals.ui.editor_url_template' => 'custom://{path}:{line}',
    ]);

    $url = EditorUrl::for('/var/www/app/routes/api.php', 50);

    expect($url)->toBe('custom:///var/www/app/routes/api.php:50');
});

// ── Null / not configured ───────────────────────────────────────────────────

it('returns null when neither editor nor template is configured', function (): void {
    config(['vitals.ui.editor' => null, 'vitals.ui.editor_url_template' => null]);

    expect(EditorUrl::for('/var/www/app/app/Models/User.php', 1))->toBeNull();
});

it('returns null for unknown editor preset', function (): void {
    config(['vitals.ui.editor' => 'nonexistent-editor', 'vitals.ui.editor_url_template' => null]);

    expect(EditorUrl::for('/var/www/app/app/Models/User.php', 1))->toBeNull();
});

it('returns null when editor_url_template is empty string', function (): void {
    config(['vitals.ui.editor' => null, 'vitals.ui.editor_url_template' => '']);

    expect(EditorUrl::for('/var/www/app/app/Models/User.php', 1))->toBeNull();
});

// ── Absolute vs relative paths ──────────────────────────────────────────────

it('passes absolute paths through unchanged', function (): void {
    config(['vitals.ui.editor' => 'vscode', 'vitals.ui.editor_url_template' => null]);

    $url = EditorUrl::for('/absolute/path/to/file.php', 1);

    expect($url)->toContain('/absolute/path/to/file.php');
});

it('prefixes relative paths with base_path', function (): void {
    config(['vitals.ui.editor' => 'vscode', 'vitals.ui.editor_url_template' => null]);

    $url = EditorUrl::for('app/Models/User.php', 5);

    expect($url)->toContain(base_path('app/Models/User.php'));
});

// ── Defaults ────────────────────────────────────────────────────────────────

it('defaults line to 1 when null is passed', function (): void {
    config(['vitals.ui.editor' => 'vscode', 'vitals.ui.editor_url_template' => null]);

    $url = EditorUrl::for('/var/www/app/file.php');

    expect($url)->toEndWith(':1');
});

// ── Supported editors list ──────────────────────────────────────────────────

it('reports 11 supported editors', function (): void {
    expect(EditorUrl::supportedEditors())->toHaveCount(11)
        ->and(EditorUrl::supportedEditors())->toContain('vscode')
        ->and(EditorUrl::supportedEditors())->toContain('phpstorm')
        ->and(EditorUrl::supportedEditors())->toContain('zed');
});
