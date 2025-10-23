<?php

declare(strict_types=1);

namespace Leaf\Sprout;

class Command
{
    protected $signature = '';
    protected $description = '';
    protected $handler = null;
    protected $arguments = [];

    protected $params = [];

    protected $help = [
        'arguments' => [],
        'params' => [],
    ];

    public function __construct()
    {
        // 
    }

    /**
     * Get the value of an argument passed into app
     * @param string $argument
     * @return mixed
     */
    public function argument(string $argument)
    {
        if ($this->help['arguments'][$argument]['type'] === 'array') {
            if (is_string($this->arguments[$argument])) {
                return explode(',', $this->arguments[$argument] ?? '');
            }
        }

        return $this->arguments[$argument] ?? $this->help['arguments'][$argument]['default'];
    }

    /**
     * Get the value of an param/flag passed into app
     * @param string $param
     * @return mixed
     */
    public function param(string $param)
    {
        return $this->params[$param] ?? null;
    }

    /**
     * Get the value of an param/flag passed into app
     * @param string $param
     * @return mixed
     */
    public function option(string $param)
    {
        return $this->param($param);
    }

    /**
     * Write output to console
     * @param string $data
     * @return void
     */
    public function write(string $data)
    {
        echo Style\Renderer::render($data);
    }

    /**
     * Write output to console with a new line
     * @param string $data
     * @return void
     */
    public function writeln(string $data)
    {
        $this->write($data . PHP_EOL);
    }

    /**
     * Output some data as a comment
     */
    public function comment($messages)
    {
        return $this->writeln("<comment>$messages</comment>");
    }

    /**
     * Output some data as a info
     */
    public function info($messages)
    {
        return $this->writeln("<info>$messages</info>");
    }

    /**
     * Output some data as a error
     */
    public function error($messages)
    {
        return $this->writeln("<error>$messages</error>");
    }

    /**
     * Output some data as a question
     */
    public function question($messages)
    {
        return $this->writeln("<question>$messages</question>");
    }

    /**
     * Create a new command from function
     * @param string $signature
     * @param callable $handler
     * @return Command
     */
    public function create(string $signature, callable $handler): Command
    {
        $this->signature = $signature;
        $this->handler = $handler;

        return $this;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * Get all command arguments
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get all command params/flags
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Add a description to your command
     * @param string $description The command description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * Add command help
     * @param array add help descriptions for params
     */
    public function setHelp(array $help)
    {
        $this->help = $help;
    }

    /**
     * Get command help
     * @return array
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * Get command name
     */
    public function getName()
    {
        return trim(explode(' ', $this->signature)[0]);
    }

    /**
     * Get command description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set all arguments passed into command
     * @param array $arguments
     * @return void
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = array_merge($this->arguments, $arguments);
    }

    /**
     * Set all params/flags passed into command
     * @param array $params
     * @return void
     */
    public function setParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
    }

    protected function validateCommandArguments(array $data, array &$dataPool)
    {
        foreach ($data as $key => $value) {
            if (!$value['optional'] && (!isset($dataPool[$value['long']]) && !isset($dataPool[$key]))) {
                throw new \Exception("Argument $key is required");
            }

            if ($value['optional'] && (!isset($dataPool[$value['long']]) && !isset($dataPool[$key]))) {
                $dataPool[$value['long']] = $value['default'];
            }
        }
    }

    public function call(Command $command): int
    {
        $this->validateCommandArguments($this->help['params'], $this->params);
        $this->validateCommandArguments($this->help['arguments'], $this->arguments);

        return $this->handler
            ? call_user_func($this->handler, $command)
            : $this->handle($command);
    }
}
