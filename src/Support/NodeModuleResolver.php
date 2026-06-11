<?php

declare(strict_types=1);

namespace LaravelVitals\Support;

/**
 * Resolves npm packages the way Node does — by walking node_modules
 * directories upward from a starting path until one contains the package
 * (or the filesystem root is reached).
 *
 * Filesystem stats only: no process spawn, no Node version assumptions. Used
 * by the Playwright driver so the auto-detection chain never reports it as
 * available when its npm dependencies are not actually installed.
 */
final class NodeModuleResolver
{
    /**
     * @param array<int, string> $packages
     */
    public static function allInstalled(string $fromDir, array $packages): bool
    {
        foreach ($packages as $package) {
            if (! self::isInstalled($fromDir, $package)) {
                return false;
            }
        }

        return true;
    }

    public static function isInstalled(string $fromDir, string $package): bool
    {
        $dir = $fromDir;

        while (true) {
            if (is_dir($dir . '/node_modules/' . $package)) {
                return true;
            }

            $parent = dirname($dir);

            // dirname() of a filesystem root returns the root unchanged.
            if ($parent === $dir) {
                return false;
            }

            $dir = $parent;
        }
    }
}
