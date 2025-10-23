<?php

namespace Leaf\Sprout;

/**
 * Seedling
 * ----
 * Utils for sseedling framework
 */
class Seedling
{
    public static function commands(): array
    {
        return [
            Seedling\GenerateConsoleCommand::class,
            Seedling\DeleteConsoleCommand::class,
        ];
    }

    public static function moduleCommands(): array
    {
        $commands = [];

        if (class_exists('Leaf\Queue')) {
            $commands[] = \Leaf\Queue::commands();
        }

        if (class_exists('Leaf\Billing')) {
            $commands[] = \Leaf\Billing::commands();
        }

        if (class_exists('Leaf\Schema')) {
            $commands[] = \Leaf\Commands\DatabaseCommands::commands();
        }

        return $commands;
    }
}
