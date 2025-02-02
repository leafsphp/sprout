<?php

declare(strict_types=1);

namespace Leaf\Sprout;

use Exception;

/**
 * Lightweight process runner
 * ---
 * Run shell commands in PHP
 */
class Process
{
    protected $timeout = 60;
    protected $idleTimeout = 60;
    protected $env = [];

    /**
     * Exit codes translation table.
     *
     * User-defined errors must use exit codes in the 64-113 range.
     * 
     * @author Fabien Potencier <fabien@symfony.com>
     */
    public static array $exitCodes = [
        0 => 'OK',
        1 => 'General error',
        2 => 'Misuse of shell builtins',

        126 => 'Invoked command cannot execute',
        127 => 'Command not found',
        128 => 'Invalid exit argument',

        // signals
        129 => 'Hangup',
        130 => 'Interrupt',
        131 => 'Quit and dump core',
        132 => 'Illegal instruction',
        133 => 'Trace/breakpoint trap',
        134 => 'Process aborted',
        135 => 'Bus error: "access to undefined portion of memory object"',
        136 => 'Floating point exception: "erroneous arithmetic operation"',
        137 => 'Kill (terminate immediately)',
        138 => 'User-defined 1',
        139 => 'Segmentation violation',
        140 => 'User-defined 2',
        141 => 'Write to pipe with no one reading',
        142 => 'Signal raised by alarm',
        143 => 'Termination (request to terminate)',
        // 144 - not defined
        145 => 'Child process terminated, stopped (or continued*)',
        146 => 'Continue if stopped',
        147 => 'Stop executing temporarily',
        148 => 'Terminal stop signal',
        149 => 'Background process attempting to read from tty ("in")',
        150 => 'Background process attempting to write to tty ("out")',
        151 => 'Urgent data available on socket',
        152 => 'CPU time limit exceeded',
        153 => 'File size limit exceeded',
        154 => 'Signal raised by timer counting virtual time: "virtual timer expired"',
        155 => 'Profiling timer expired',
        // 156 - not defined
        157 => 'Pollable event',
        // 158 - not defined
        159 => 'Bad syscall',
    ];

    public function __construct(string $command)
    {
        if (!\function_exists('proc_open')) {
            throw new \Exception('The Process class relies on proc_open, which is not available on your PHP installation.');
        }
    }

    /**
     * Set an error action for current process
     */
    public function onError(callable $errorHandler): Process
    {
        return $this;
    }

    /**
     * Run the current process and get command output
     * @param callable|null $callback
     * @return int
     */
    public function run(?callable $callback = null): int
    {
        if ($this->isRunning()) {
            throw new \Exception('Process is already running.');
        }

        return 0;
    }

    /**
     * Check if a process is successful
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return true;
    }

    /**
     * Check if a process is running
     * @return bool
     */
    public function isRunning(): bool
    {
        return false;
    }

    /**
     * Get the exit code of the process
     */
    public function getExitCode(): int
    {
        return 0;
    }
}
