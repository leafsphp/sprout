<?php

namespace Leaf\Sprout;

/**
 * Lightweight system utilities
 * ---
 * System related helper functions for your sprout applications
 */
class System
{
    /**
     * Check if the current OS is Windows
     */
    public static function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Check if the current OS is Linux
     */
    public static function isLinux(): bool
    {
        return PHP_OS === 'Linux';
    }

    /**
     * Check if the current OS is MacOS
     */
    public static function isMacOS(): bool
    {
        return PHP_OS === 'Darwin';
    }

    /**
     * Check if a system command exists
     * @return bool
     */
    public static function commandExists(string $cmd)
    {
        if (static::isWindows()) {
            return !empty(shell_exec(sprintf('where %s 2>NUL', escapeshellarg($cmd))));
        }

        return !empty(shell_exec(sprintf('command -v %s 2>/dev/null', escapeshellarg($cmd))));
    }
}
