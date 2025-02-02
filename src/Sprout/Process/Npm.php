<?php

declare(strict_types=1);

namespace Leaf\Sprout\Process;

use Leaf\Sprout\Process;

class Npm
{
    /**
     * The package manager to use
     * @var string
     */
    protected $packageManager;

    public function __construct($packageManager = 'npm')
    {
        $this->packageManager = $packageManager;
    }

    /**
     * Return the composer.json file in cwd
     * @return array
     */
    public function json(): array
    {
        return [];
    }

    /**
     * Check if dependencies have been installed
     * @return bool
     */
    public function hasDependencies(): bool
    {
        return false;
    }

    /**
     * Check if an CWD has an npm package installed
     * @param string|array $package The package to check for
     */
    public function hasDependency($package): bool
    {
        return false;
    }

    /**
     * Install an npm package
     * @param string|array|null $package The package to install
     * @param callable|null $callback A callback to run after installation
     */
    public function install($package = null, $callback = null): Process
    {
        $process = new Process("{$this->packageManager} install $package");
        $process->run($callback);

        return $process;
    }

    /**
     * Remove an npm package
     * @param string|array $package The package to remove
     * @param callable|null $callback A callback to run after removal
     */
    public function remove($package, $callback = null): Process
    {
        $process = new Process("{$this->packageManager} uninstall $package");
        $process->run($callback);

        return $process;
    }

    /**
     * Run an npm script defined in composer.json
     * @param string $script The script to run
     * @param callable|null $callback A callback to run after script execution
     */
    public function runScript(string $script, $callback = null): Process
    {
        $process = new Process("{$this->packageManager} run $script");
        $process->run($callback);

        return $process;
    }
}
