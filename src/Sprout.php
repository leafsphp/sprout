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
     * @return App
     */
    public function createApp(): App
    {
        return (new App());
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
    public function prompt(array $prompt): Prompt
    {
        return (new Prompt($prompt));
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
    public function composer(): Composer
    {
        return new Composer();
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
}
