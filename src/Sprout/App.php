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

    protected $eventListeners = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;

        if (isset($this->config['autoload'])) {
            $this->autoloadCommands($this->config['autoload']);
        }

        if (isset($this->config['autorun']) && $this->config['autorun'] === true) {
            $this->run();
        }
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

        if ($commandOptions === null) {
            throw new \Exception("Invalid command signature: " . json_encode(get_class($command)));
        }

        $command->setHelp($commandOptions['help']);

        $this->config['commands'][$commandOptions['name']] = [
            'signature' => $command->getSignature(),
            'params' => $commandOptions['params'],
            'arguments' => $commandOptions['arguments'],
            'parsedSignature' => $commandOptions['parsed'],
            'handler' => $command,
        ];

        return $command;
    }

    /**
     * Autoload commands from a directory
     * @param string $path The path to the commands directory
     * @return void
     */
    public function autoloadCommands(string $path): void
    {
        if (is_dir($path)) {
            $commandFiles = scandir($path);

            foreach ($commandFiles as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $className = pathinfo($file, PATHINFO_FILENAME);
                    $relativePath = str_replace([getcwd(), '/'], ['', '\\'], $path);
                    $relativePath = implode('\\', array_map(
                        'ucfirst',
                        explode('\\', trim($relativePath, '\\')))
                    );

                    $fullClassName = trim("$relativePath\\$className", '\\');

                    if (class_exists($fullClassName)) {
                        $this->register(new $fullClassName());
                    }
                }
            }
        }
    }

    /**
     * Register an event listener
     * @param string $event The event name
     * @param callable $listener The event listener callback
     * @return void
     */
    public function on(string $event, callable $listener): void
    {
        if (!isset($this->eventListeners[$event])) {
            $this->eventListeners[$event] = [];
        }

        $this->eventListeners[$event][] = $listener;
    }

    /**
     * Emit an event to all registered listeners
     * @param string $event The event name
     * @param array $data Event data
     * @return Event
     */
    public function emit(string $event, array $data = []): Event
    {
        $eventObject = new Event($event, $data);

        if (!isset($this->eventListeners[$event])) {
            return $eventObject;
        }

        foreach ($this->eventListeners[$event] as $listener) {
            if ($eventObject->isPropagationStopped()) {
                break;
            }

            call_user_func($listener, $eventObject);
        }

        return $eventObject;
    }

    /**
     * Remove event listeners for a specific event
     * @param string $event The event name
     * @return void
     */
    public function off(string $event): void
    {
        unset($this->eventListeners[$event]);
    }

    /**
     * Check if an event has any listeners
     * @param string $event The event name
     * @return bool
     */
    public function hasListeners(string $event): bool
    {
        return isset($this->eventListeners[$event]) && !empty($this->eventListeners[$event]);
    }

    protected function normalizeCommandInput(string $input): array
    {
        $tokens = preg_split('/\s+/', trim($input));
        $result = [
            'command' => null,
            'args' => [],
            'options' => []
        ];

        $i = 0;
        $len = count($tokens);

        if ($len > 0) {
            $result['command'] = $tokens[$i++];
        }

        while ($i < $len) {
            $token = $tokens[$i];

            // Long option: --option or --option=value
            if (strpos($token, '--') === 0) {
                $option = substr($token, 2);
                if (strpos($option, '=') !== false) {
                    [$name, $value] = explode('=', $option, 2);
                    $result['options'][$name] = strpos($value, ',') !== false ? explode(',', $value) : $value;
                } elseif (isset($tokens[$i + 1]) && strpos($tokens[$i + 1], '-') !== 0) {
                    // --option value
                    $value = [];
                    while (isset($tokens[$i + 1]) && strpos($tokens[$i + 1], '-') !== 0) {
                        $value[] = $tokens[++$i];
                    }
                    $result['options'][$option] = count($value) === 1 ? $value[0] : $value;
                } else {
                    // --option (boolean true)
                    $result['options'][$option] = true;
                }
            }

            // Short option: -a or -abc or -k value
            elseif (strpos($token, '-') === 0 && strlen($token) > 1) {
                $flags = substr($token, 1);

                // Handle -k value
                if (strlen($flags) === 1 && isset($tokens[$i + 1]) && strpos($tokens[$i + 1], '-') !== 0) {
                    $result['options'][$flags] = $tokens[++$i];
                } else {
                    // Handle -abc => a=true, b=true, c=true
                    foreach (str_split($flags) as $flag) {
                        $result['options'][$flag] = true;
                    }
                }
            }

            // Normal argument
            else {
                $result['args'][] = $token;
            }

            $i++;
        }

        return $result;
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
        $commandString = join(' ', array_slice($argv, 1));
        $commandData = $this->normalizeCommandInput($commandString);

        if ($commandName === '' || $commandName === 'list') {
            $this->renderListView();
            return;
        }

        if (!isset($this->config['commands'][$commandName])) {
            if ($this->hasListeners('command.notFound')) {
                $event = $this->emit('command.notFound', [
                    'commandName' => $commandName,
                    'commandData' => $commandData,
                    'argv' => $argv
                ]);

                if ($event->getExitCode() !== 0) {
                    exit($event->getExitCode());
                }

                return;
            }

            echo "Command not found\n";
            return;
        }

        $beforeEvent = $this->emit('command.before', [
            'commandName' => $commandName,
            'commandData' => $commandData,
            'argv' => $argv
        ]);

        if ($beforeEvent->isPropagationStopped()) {
            if ($beforeEvent->getExitCode() !== 0) {
                exit($beforeEvent->getExitCode());
            }

            return;
        }

        if ($this->hasListeners($commandName)) {
            $customEvent = $this->emit($commandName, [
                'commandName' => $commandName,
                'commandData' => $commandData,
                'argv' => $argv
            ]);

            if ($customEvent->isPropagationStopped()) {
                if ($customEvent->getExitCode() !== 0) {
                    exit($customEvent->getExitCode());
                }

                return;
            }
        }

        $command = $this->config['commands'][$commandName]['handler'];

        foreach (array_values($command->getHelp()['params']) as $paramData) {
            $params[$paramData['long']] = (
                isset($commandData['options'][$paramData['long']]) ||
                isset($commandData['options'][$paramData['short']])
            ) ?
                ($commandData['options'][$paramData['long']] ?? $commandData['options'][$paramData['short']] ?? null) :
                $paramData['default'] ?? null;

            if ($paramData['optional'] === false && $params[$paramData['long']] === null) {
                die("Argument --{$paramData['long']} is required\n");
            }
        }

        foreach ($this->config['commands'][$commandName]['arguments'] as $index => $arg) {
            if (($command->getHelp()['arguments'][$arg]['type'] ?? null) === 'array') {
                $arguments[$arg] = array_slice($commandData['args'], $index);
                break;
            }

            $arguments[$arg] = $commandData['args'][$index] ?? null;
        }

        $command->setParams($params);
        $command->setArguments($arguments);

        if (in_array('--help', $argv) || in_array('-h', $argv)) {
            $this->renderHelpView($command);

            return;
        }

        $result = $command->call($command);

        $this->emit('command.after', [
            'commandName' => $commandName,
            'commandData' => $commandData,
            'params' => $params,
            'arguments' => $arguments,
            'result' => $result,
            'argv' => $argv
        ]);

        return $result;
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
            'parsed' => ArgvParser::parseCommandSignature($signature),
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
        $groups = [];

        foreach ($this->config['commands'] as $commandName => $command) {
            $parts = explode(':', $commandName, 2);

            $namespace = (count($parts) === 2) ? $parts[0] : '_global';

            $groups[$namespace][$commandName] = $command['handler']->getDescription();
        }

        ksort($groups);

        foreach ($groups as $namespace => &$commands) {
            ksort($commands);
        }

        $output = <<<HELP
{$this->config['name']} {$this->config['version']}

Usage:
  command [options] [arguments]

Options:
  -h, --help        Display help for the given command
  -V, --version     Display this application version

Available commands:

HELP;

        if (isset($groups['_global'])) {
            foreach ($groups['_global'] as $name => $desc) {
                $output .= sprintf("  \033[1;32m%-18s\033[0m %s\n", $name, $desc);
            }

            unset($groups['_global']);
        }

        foreach ($groups as $namespace => $commands) {
            $output .= " $namespace\n";

            foreach ($commands as $name => $desc) {
                $output .= sprintf("  \033[1;32m%-18s\033[0m %s\n", $name, $desc);
            }
        }

        echo Style\Renderer::render($output);
    }

    protected function renderHelpView(Command $command)
    {
        $paramsHelp = '';
        $argumentsHelp = '';

        if (count($command->getHelp()['arguments']) === 0) {
            $argumentsHelp = "  This command has no arguments.\n";
        }

        foreach ($command->getHelp()['arguments'] as $helpKey => $helpValue) {
            $argumentsHelp .= "  $helpKey: {$helpValue['description']}\n";
        }

        foreach ($command->getHelp()['params'] as $helpKey => $helpValue) {
            $paramsHelp .= (($helpValue['short'] !== null) ? "  -{$helpValue['short']}," : ' ') .
                " --{$helpValue['long']}: {$helpValue['description']}\n";
        }

        $options = $command->getHelp();
        $arguments = $options['arguments'];
        $params = count($options['params']) > 0 ? '[--options...]' : '';

        $usageArguments = '';

        foreach ($arguments as $argKey => $argValue) {
            $usageArguments .= " [$argKey]";
        }

        echo <<<HELP
Description:
  {$command->getDescription()}

Usage:
  {$command->getName()}{$usageArguments} {$params}

Arguments:
$argumentsHelp
Options:
  -h, --help: Display help for the given command.
$paramsHelp

HELP;
    }
}
