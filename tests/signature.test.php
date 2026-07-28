<?php

declare(strict_types=1);

test('arguments are bound from argv in order', function () {
    $result = runSprout(sproutApp([GreetCommand::class]), ['greet', 'Mika']);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('Hello, Mika!');
});

test('option defaults from the signature are used when not passed', function () {
    $result = runSprout(sproutApp([GreetCommand::class]), ['greet', 'Mika']);

    expect($result['output'])->toContain('Hello, Mika!');
});

test('options can be passed as --option=value', function () {
    $result = runSprout(sproutApp([GreetCommand::class]), ['greet', 'Mika', '--greeting=Hi']);

    expect($result['output'])->toContain('Hi, Mika!');
});

test('options can be passed as a space separated value', function () {
    $result = runSprout(sproutApp([GreetCommand::class]), ['greet', 'Mika', '--greeting', 'Yo']);

    expect($result['output'])->toContain('Yo, Mika!');
});

test('optional arguments default to null without failing', function () {
    $result = runSprout(sproutApp([OptionalArgCommand::class]), ['shout']);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('shouting at: nobody');
});

test('array arguments collect the remaining argv', function () {
    $result = runSprout(sproutApp([ArrayArgCommand::class]), ['invite', 'ada', 'grace', 'linus']);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('invited: ada+grace+linus');
});

test('value options default to null when not passed', function () {
    $result = runSprout(sproutApp([RequiredOptionCommand::class]), ['deploy']);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('token:');
});

test('value options accept a value', function () {
    $result = runSprout(sproutApp([RequiredOptionCommand::class]), ['deploy', '--token=abc123']);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('token: abc123');
});

test('short option aliases map to their long option', function () {
    $result = runSprout(sproutApp([ShortOptionCommand::class]), ['clean', '-f']);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('forced');
});

test('boolean flags default to falsy when not passed', function () {
    $result = runSprout(sproutApp([ShortOptionCommand::class]), ['clean']);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('gentle');
});
