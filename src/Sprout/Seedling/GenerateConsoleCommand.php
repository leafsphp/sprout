<?php

namespace Leaf\Sprout\Seedling;

use Leaf\Sprout\Command;

class GenerateConsoleCommand extends Command
{
    protected $signature = 'g:command {consoleCommand : The name of the console command}';
    protected $description = 'Create a new console command';
    protected $help = 'Create a custom aloe cli command';

    protected function handle()
    {
        list($commandName, $className) = $this->mapNames($this->argument('consoleCommand'));

        if (file_exists($commandFile = getcwd() . DIRECTORY_SEPARATOR . "app/console/$className.php")) {
            $this->error("$className already exists!");
            return 1;
        }

        \Leaf\FS\File::create($commandFile, function () use ($className, $commandName) {
            return str_replace(
                ['ClassName', 'CommandName'],
                [$className, $commandName],
                \file_get_contents(__DIR__ . '/stubs/console.stub')
            );
        }, ['recursive' => true]);

        $this->comment("$className generated successfully");

        return 0;
    }

    public function mapNames($command)
    {
        $className = $command;

        if (strpos($command, ':')) {
            $commandItems = explode(':', $command);
            $items = [];

            foreach ($commandItems as $item) {
                $items[] = $this->studly($item);
            }

            $className = implode('', $items);
        }

        if (!strpos($className, 'Command')) {
            $className .= 'Command';
        } else {
            $command = str_replace('Command', '', $command);
        }

        return [strtolower($command), $this->studly($className)];
    }

    public function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(
            str_replace(['-', '_'], ' ', $value)
        ));
    }
}
