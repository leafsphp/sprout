<?php

declare(strict_types=1);

test('bare invocation renders the command list with app name and version', function () {
    $result = runSprout(sproutApp([GreetCommand::class]), []);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('TestApp v0.0-test')
        ->and($result['output'])->toContain('greet')
        ->and($result['output'])->toContain('Greet someone properly');
});

test('every command group lists its own commands', function () {
    // regression: a by-reference foreach corrupted the last group, making it
    // repeat the previous group's commands
    $app = sproutApp();
    $app->command('scaffold:auth', function () {
        return 0;
    })->setDescription('Scaffold auth');
    $app->command('view:build', function () {
        return 0;
    })->setDescription('Build views');
    $app->command('view:dev', function () {
        return 0;
    })->setDescription('Dev views');

    $result = runSprout($app, []);

    expect($result['output'])->toContain('view:build')
        ->and($result['output'])->toContain('view:dev')
        ->and(substr_count($result['output'], 'scaffold:auth'))->toBe(1);
});

test('unknown commands exit with code 1', function () {
    $result = runSprout(sproutApp([GreetCommand::class]), ['definitely:not:real']);

    expect($result['code'])->toBe(1)
        ->and($result['output'])->toContain('Command not found');
});

test('run(false) returns the exit code instead of exiting', function () {
    $result = runSprout(sproutApp([FailingCommand::class]), ['fail:hard']);

    expect($result['code'])->toBe(3)
        ->and($result['output'])->toContain('it broke');
});

test('functional commands registered via command() run and return codes', function () {
    $app = sproutApp();
    $app->command('cache:clear', function () {
        echo 'cache cleared';

        return 0;
    });

    $result = runSprout($app, ['cache:clear']);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('cache cleared');
});

test('--help renders the command description and argument help', function () {
    $result = runSprout(sproutApp([GreetCommand::class]), ['greet', '--help']);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('Greet someone properly')
        ->and($result['output'])->toContain('The name of the person')
        ->and($result['output'])->toContain('The greeting to use');
});
