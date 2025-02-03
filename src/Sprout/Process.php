<?php

declare(strict_types=1);

namespace Leaf\Sprout;

/**
 * Lightweight process runner
 * ---
 * Run shell commands in PHP
 */
class Process
{
    protected $process;

    public function __construct(string $command)
    {
        if (!\function_exists('proc_open')) {
            throw new \Exception('The Process class relies on proc_open, which is not available on your PHP installation.');
        }

        $this->process = \Symfony\Component\Process\Process::fromShellCommandline($command);
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
        return $this->process->run(function ($type, $buffer) use ($callback) {
            if ($callback) {
                $callback($type, $buffer);
            } else {
                echo $buffer;
            }
        });
    }

    /**
     * Get the exit code of the process
     */
    public function getExitCode(): int
    {
        return $this->process->getExitCode();
    }

    /**
     * Get exit code error
     * @param int $code
     */
    public function getExitCodeText(int $code): string
    {
        return $this->process::$exitCodes[$code];
    }

    /**
     * Get the output of the process
     */
    public function getOutput(): string
    {
        return $this->process->getOutput();
    }

    /**
     * Get the error output of the process
     */
    public function getErrorOutput(): string
    {
        return $this->process->getErrorOutput();
    }

    /**
     * Set the timeout for the process
     * @param int $timeout
     */
    public function setTimeout(?float $timeout): Process
    {
        $this->process->setTimeout($timeout);

        return $this;
    }

    /**
     * Set the working directory for the process
     * @param string $cwd
     */
    public function setWorkingDirectory(string $cwd): Process
    {
        $this->process->setWorkingDirectory($cwd);

        return $this;
    }

    /**
     * Set the environment variables for the process
     * @param array $env
     */
    public function setEnv(array $env): Process
    {
        $this->process->setEnv($env);

        return $this;
    }

    public function __call($method, $args)
    {
        if (\method_exists($this->process, $method)) {
            return \call_user_func_array([$this->process, $method], $args);
        }

        throw new \Exception("Method $method does not exist on the Process class.");
    }
}
