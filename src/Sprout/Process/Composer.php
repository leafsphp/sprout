<?php

declare(strict_types=1);

namespace Leaf\Sprout\Process;

use Leaf\Sprout\Process;

class Composer
{
    protected $global = false;

    public function __construct(bool $global = false)
    {
        $this->global = $global;
    }

    /**
     * Return the composer.json file in cwd
     * @return array
     */
    public function json(): array
    {
        $composerJson = getcwd() . '/composer.json';

        if (!file_exists($composerJson)) {
            return [];
        }

        return json_decode(
            file_get_contents($composerJson),
            true
        );
    }

    /**
     * Return the composer.lock file in cwd
     * @return array
     */
    public function lock(): array
    {
        $composerLock = getcwd() . '/composer.lock';

        if (!file_exists($composerLock)) {
            return [];
        }

        return json_decode(
            file_get_contents($composerLock),
            true
        );
    }

    /**
     * Check if dependencies have been installed
     * @return bool
     */
    public function hasDependencies(): bool
    {
        return file_exists(getcwd() . '/vendor') && file_exists(getcwd() . '/composer.lock');
    }

    /**
     * Check if an CWD has a composer package installed
     * @param string|array $package The package to check for
     */
    public function hasDependency($package): bool
    {
        return $this->hasDependencies() && (strpos(json_encode($this->lock()), "\"name\": \"$package\"") !== false);
    }

    /**
     * Install a composer package
     * @param string|array|null $package The package to install
     * @param callable|null $callback A callback to run after installation
     */
    public function install($package = null, $callback = null): Process
    {
        $installCommand = $this->global ? 'global require' : 'require';
        $process = new Process($package ? "composer $installCommand $package --ansi" : "composer install --ansi");
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
        $removeCommand = $this->global ? 'global remove' : 'remove';
        $process = new Process("composer $removeCommand $package");
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
