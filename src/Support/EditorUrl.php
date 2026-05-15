<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

final class EditorUrl
{
    /**
     * Built-in URL templates for common editors.
     * Same set as Symfony VarDumper / Spatie Ignition.
     *
     * @var array<string, string>
     */
    private const PRESETS = [
        'vscode'    => 'vscode://file/{path}:{line}',
        'cursor'    => 'cursor://file/{path}:{line}',
        'phpstorm'  => 'phpstorm://open?file={path}&line={line}',
        'idea'      => 'idea://open?file={path}&line={line}',
        'sublime'   => 'subl://open?url=file://{path}&line={line}',
        'atom'      => 'atom://core/open/file?filename={path}&line={line}',
        'textmate'  => 'txmt://open?url=file://{path}&line={line}',
        'macvim'    => 'mvim://open/?url=file://{path}&line={line}',
        'emacs'     => 'emacs://open?url=file://{path}&line={line}',
        'nova'      => 'nova://open?path={path}&line={line}',
        'zed'       => 'zed://file/{path}:{line}',
    ];

    /**
     * Resolves the URL template from config — custom template wins over preset.
     */
    public static function template(): ?string
    {
        $custom = config('vitals.ui.editor_url_template');
        if (is_string($custom) && $custom !== '') {
            return $custom;
        }

        $preset = config('vitals.ui.editor');
        if (is_string($preset) && isset(self::PRESETS[$preset])) {
            return self::PRESETS[$preset];
        }

        return null;
    }

    /**
     * Builds an editor URL for the given file path (relative to the host app base_path)
     * and line number. Returns null when no editor is configured.
     */
    public static function for(string $file, ?int $line = null): ?string
    {
        $template = self::template();
        if ($template === null) {
            return null;
        }

        $absolute = str_starts_with($file, '/') ? $file : base_path($file);

        return str_replace(['{path}', '{line}'], [$absolute, (string) ($line ?? 1)], $template);
    }

    /**
     * Returns the list of supported preset names.
     *
     * @return list<string>
     */
    public static function supportedEditors(): array
    {
        return array_keys(self::PRESETS);
    }
}
