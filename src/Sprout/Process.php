<?php

declare(strict_types=1);

namespace Leaf\Sprout;

class Process
{
    public function __construct(string $command)
    {
        // 
    }

    /**
     * Set an error action for current process
     */
    public function onError(): Process
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
     * Get the exit code of the process
     */
    public function getExitCode(): int
    {
        return 0;
    }
}
