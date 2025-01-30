<?php

declare(strict_types=1);

namespace Leaf\Sprout\Process;

use Leaf\Sprout\Process;

class Composer
{
    public function __construct()
    {
        // 
    }

    /**
     * Check if an CWD has a composer package installed
     * @param string|array $package The package to check for
     */
    public function hasDependency($package): bool
    {
        return false;
    }

    /**
     * Install a composer package
     * @param string|array|null $package The package to install
     * @param callable|null $callback A callback to run after installation
     */
    public function install($package = null, $callback = null): Process
    {
        $process = new Process($package ? "composer require $package" : "composer install");
        $process->run($callback);

        return $process;
    }

    /**
     * Remove a composer package
     * @param string|array $package The package to remove
     * @param callable|null $callback A callback to run after removal
     */
    public function remove($package, $callback = null): Process
    {
        $process = new Process("composer remove $package");
        $process->run($callback);

        return $process;
    }

    /**
     * Run a composer script defined in composer.json
     * @param string $script The script to run
     * @param callable|null $callback A callback to run after script execution
     */
    public function runScript(string $script, $callback = null): Process
    {
        $process = new Process("composer run $script");
        $process->run($callback);

        return $process;
    }
}
