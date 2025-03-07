<?php

namespace Leaf\Sprout;

/**
 * Argv Parser
 * ---
 * Parse symfony console style argv
 * to syntax for sprout
 */
class ArgvParser
{
    public static function parse($argv)
    {
        $application = array_shift($argv); // First argument is the application
        $command = array_shift($argv); // Second argument is the command

        $parsed = [
            'application' => $application,
            'command' => $command,
        ];

        return $parsed;
    }

    public static function parseCommandSignature(string $signature)
    {
        $parts = preg_split('/\s+/', trim($signature));
        $parsed = [
            'application' => null,
            'command' => array_shift($parts),
            'options' => [],
            'arguments' => [],
        ];

        foreach ($parts as $part) {
            if (preg_match('/^\{(.*)\}$/', $part, $matches)) {
                $param = explode(':', $matches[1]);
                $key = trim($param[0]);
                $isOption = str_starts_with($key, '--');

                if ($isOption) {
                    $key = ltrim($key, '-');
                    $default = isset($param[1]) ? trim($param[1]) : 'false';
                    $parsed['options'][$key] = $default === 'true' ? true : ($default === 'false' ? false : $default);
                } else {
                    $parsed['arguments'][$key] = null;
                }
            }
        }

        return $parsed;
    }

    public static function setCommandArgs(array $commandData, array $argv)
    {
        $command = $commandData['handler'];
        $argumentNames = $commandData['arguments'];

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
    }
}
