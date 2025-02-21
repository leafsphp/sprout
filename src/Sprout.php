<?php

declare(strict_types=1);

namespace Leaf;

use Leaf\Sprout\App;
use Leaf\Sprout\Process;
use Leaf\Sprout\Process\Composer;
use Leaf\Sprout\Process\Npm;
use Leaf\Sprout\Style;
use Leaf\Sprout\Prompt;

/**
 * Leaf Sprout
 * --------
 * Minimalist CLI framework
 *
 * @author Michael Darko <mickdd22@gmail.com>
 * @copyright 2025 Michael Darko
 * @link https://leafphp.dev/docs/mvc/commands.html
 * @license MIT
 */
class Sprout
{
    /**
     * Create a new Sprout App
     * @param array $config Application config
     * @return App
     */
    public function createApp($config = []): App
    {
        return (new App($config));
    }

    /**
     * Create a new process
     * @param string $command The command to run
     * @return Process
     */
    public function process(string $command): Process
    {
        return (new Process($command));
    }

    /**
     * Create a new Prompt
     * @param array $prompt
     * @return Prompt
     */
    public function prompt(array $prompt): array
    {
        return (new Prompt($prompt))->ask();
    }

    /**
     * Create a new Style
     * @return Style
     */
    public function style(): Style
    {
        return new Style();
    }

    /**
     * Run a composer process
     * @return Composer
     */
    public function composer(bool $global = false): Composer
    {
        return new Composer($global);
    }

    /**
     * Run an npm process
     * @param string $packageManager
     * @return Npm
     */
    public function npm(string $packageManager = 'npm'): Npm
    {
        return new Npm($packageManager);
    }

    /**
     * Create a new process and immediately run it
     * @param string $command The command to run
     * @return int
     */
    public function run(string $command, ?float $timeout = 60): int
    {
        return $this->process($command)->setTimeout($timeout)->run();
    }
}
