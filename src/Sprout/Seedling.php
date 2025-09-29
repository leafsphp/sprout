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
}
