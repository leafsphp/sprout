<?php

declare(strict_types=1);

namespace Leaf\Sprout;

class App
{
    protected $config = [
        'name' => 'Leaf Sprout',
        'version' => '1.0.0',
        'commands' => [],
    ];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Create a new command via function
     * @param string $signature The command signature
     * @param callable $callback The command callback
     */
    public function command(string $signature, callable $callback): Command
    {
        return $this->register((new Command())->create($signature, $callback));
    }

    /**
     * Register a new command class
     * @param \Leaf\Sprout\Command|string|array $command The command(s) to register
     * @return \Leaf\Sprout\Command|App
     */
    public function register($command)
    {
        if (is_array($command)) {
            foreach ($command as $cmd) {
                $this->register($cmd);
            }

            return $this;
        }

        if (is_string($command)) {
            $command = new $command();
        }

        $commandOptions = $this->parseCommandSignature($command->getSignature());

        $command->setHelp($commandOptions['help']);

        $this->config['commands'][$commandOptions['name']] = [
            'signature' => $command->getSignature(),
            'params' => $commandOptions['params'],
            'arguments' => $commandOptions['arguments'],
            'handler' => $command,
        ];

        return $command;
    }

    /**
     * Run your sprout app
     * @return void
     */
    public function run()
    {
        $params = [];
        $arguments = [];

        $argv = (array) $_SERVER['argv'];
        $commandName = $argv[1] ?? '';

        if ($commandName === '' || $commandName === 'list') {
            $this->renderListView();
            return;
        }

        if (!isset($this->config['commands'][$commandName])) {
            echo "Command not found\n";
            return;
        }

        $command = $this->config['commands'][$commandName]['handler'];
        $argumentNames = $this->config['commands'][$commandName]['arguments'];

        foreach (array_slice($argv, 2) as $arg) {
            $parts = explode('=', $arg);

            if (strpos($parts[0], '-') === 0 && strpos($parts[0], '--') === false) {
                $paramName = array_filter($command->getHelp()['params'], function ($param) use ($parts) {
                    return $param['short'] === ltrim($parts[0], '-');
                });

                if (!empty($paramName)) {
                    $parts[0] = '--' . (array_values($paramName)[0]['long']);
                }
            }

            if (count($parts) >= 2) {
                $params[substr($parts[0], 2)] = join('=', array_slice($parts, 1));
                continue;
            }

            if (strpos($parts[0], '--') === 0) {
                $params[substr($parts[0], 2)] = true;
                continue;
            }

            while (null !== ($part = array_shift($argumentNames))) {
                $part = str_replace(['{', '}'], '', $part);

                $arguments[$part] = $arg;

                break;
            }
        }

        $command->setParams($params);
        $command->setArguments($arguments);

        if (in_array('--help', $argv)) {
            $this->renderHelpView($command);

            return;
        }

        return $command->call($command);
    }

    protected function parseCommandSignature(string $signature)
    {
        if (!preg_match('/^([\w:]+)(.*?)((?:\{\s*--.*\})+)?$/s', $signature, $matches)) {
            return null;
        }

        $name = trim($matches[1]);
        preg_match_all('/\{[^{}]+\}/', $matches[2] ?? '', $arguments);
        preg_match_all('/\{--[^{}]+\}/', $matches[3] ?? '', $params);

        $params = $this->parseSignatureTokens($params[0]);
        $arguments = $this->parseSignatureTokens($arguments[0]);

        return [
            'name' => $name,
            'params' => array_keys($params),
            'arguments' => array_keys($arguments),
            'help' => [
                'params' => $params,
                'arguments' => $arguments,
            ],
        ];
    }

    protected function parseSignatureTokens(array $input)
    {
        $return = [];

        foreach ($input as $token) {
            $parsed = [
                'long' => null,
                'short' => null,
                'default' => null,
                'type' => 'string',
                'optional' => false,
                'description' => null,
                'requiresValue' => false,
            ];

            $data = explode(':', str_replace(['{', '}'], '', $token));

            $tokenName = trim($data[0]);
            $parsed['description'] = trim($data[1] ?? '');

            if (strpos($tokenName, '=') !== false) {
                $parts = explode('=', $tokenName);

                if (!isset($parts[1])) {
                    $parsed['requiresValue'] = true;
                } else {
                    $parsed['default'] = trim($parts[1]);
                }

                $parsed['optional'] = true;
                $tokenName = trim($parts[0]);
            }

            if (substr($tokenName, -1) === '*') {
                $parsed['type'] = 'array';
                $tokenName = rtrim($tokenName, '*');
            }

            if (substr($tokenName, -1) === '?') {
                $parsed['optional'] = true;
                $tokenName = rtrim($tokenName, '?');
            }

            if (strpos($tokenName, '--') === 0) {
                $tokenName = trim($tokenName, '--');

                $parts = explode('|', $tokenName);

                if (count($parts) === 2) {
                    $parsed['long'] = $parts[1];
                    $parsed['short'] = $parts[0];
                } else {
                    $parsed['long'] = $parts[0];
                }
            }

            $return[$tokenName] = $parsed;
        }

        return $return;
    }

    protected function renderListView()
    {
        $commandList = '';

        foreach ($this->config['commands'] as $commandName => $command) {
            $commandList .= "  \033[1;32m$commandName\033[0m — {$command['handler']->getDescription()}\n";
        }

        echo <<<HELP
{$this->config['name']} {$this->config['version']}

Usage:
  command [options] [arguments]

Options:
  -h, --help  -  Display help for the given command.
  -V, --version  -  Display this application version

Available commands:
  \033[1;32mlist\033[0m — List commands
$commandList

HELP;
    }

    protected function renderHelpView(Command $command)
    {
        $paramsHelp = '';
        $argumentsHelp = '';

        foreach ($command->getHelp()['arguments'] as $helpKey => $helpValue) {
            $argumentsHelp .= "  $helpKey: {$helpValue['description']}\n";
        }

        foreach ($command->getHelp()['params'] as $helpKey => $helpValue) {
            $paramsHelp .= "  $helpKey: {$helpValue['description']}\n";
        }

        echo <<<HELP
Description:
  {$command->getDescription()}

Usage:
  {$command->getName()} [options] [--] [<packages>...]

Arguments:
$argumentsHelp
Options:
  -h, --help: Display help for the given command.
$paramsHelp

HELP;
    }
}
