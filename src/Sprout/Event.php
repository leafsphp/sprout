<?php

declare(strict_types=1);

namespace Leaf\Sprout;

class Event
{
    protected $name;
    protected $data;
    protected $propagationStopped = false;
    protected $exitCode = 0;

    public function __construct(string $name, array $data = [])
    {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * Get the event name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the command name from event data
     */
    public function getCommandName(): ?string
    {
        return $this->data['commandName'] ?? null;
    }

    /**
     * Get all event data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get a specific piece of event data
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Set event data
     */
    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Set the exit code
     */
    public function setExitCode(int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }

    /**
     * Get the exit code
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * Stop event propagation
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * Check if propagation is stopped
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Get command arguments
     */
    public function getArguments(): array
    {
        return $this->data['arguments'] ?? [];
    }

    /**
     * Get command parameters/options
     */
    public function getParams(): array
    {
        return $this->data['params'] ?? [];
    }

    /**
     * Get the raw command data
     */
    public function getCommandData(): array
    {
        return $this->data['commandData'] ?? [];
    }

    /**
     * Get command argv
     */
    public function getCommandArgv(): array
    {
        return $this->data['argv'] ?? [];
    }
}
