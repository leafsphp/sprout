<?php

declare(strict_types=1);

test('quoted arguments containing spaces stay one argument', function () {
    // regression: argv used to be joined into a string and re-split on
    // whitespace, breaking "John Doe" into two arguments
    $result = runSprout(sproutApp([GreetCommand::class]), ['greet', 'John Doe']);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('Hello, John Doe!');
});

test('option values containing spaces survive too', function () {
    $result = runSprout(sproutApp([GreetCommand::class]), ['greet', 'Mika', '--greeting=Good morning']);

    expect($result['output'])->toContain('Good morning, Mika!');
});

test('grouped short flags each register as true', function () {
    $app = sproutApp();
    $app->command('flags {--a|apple} {--b|berry}', function () {
        echo ($this->option('apple') ? 'A' : '-') . ($this->option('berry') ? 'B' : '-');

        return 0;
    });

    $result = runSprout($app, ['flags', '-ab']);

    expect($result['output'])->toContain('AB');
});

test('comma separated option values become arrays', function () {
    $app = sproutApp();
    $app->command('pick {--fruits= : Fruits to pick}', function () {
        $fruits = $this->option('fruits');
        echo 'picked:' . (is_array($fruits) ? join('|', $fruits) : $fruits);

        return 0;
    });

    $result = runSprout($app, ['pick', '--fruits=apple,berry']);

    expect($result['output'])->toContain('picked:apple|berry');
});
