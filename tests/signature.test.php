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

test('flags do not swallow the argument after them', function () {
    $result = runSprout(sproutApp([ShortOptionCommand::class]), ['clean', '-f']);

    expect($result['output'])->toContain('forced');

    // a flag written before an argument leaves the argument alone
    $result = runSprout(sproutApp([FlagWithDefaultCommand::class]), ['add', '--dev', 'alchemy']);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('alchemy dev:true');
});

test('a flag declared with a default still reads as a boolean', function () {
    $app = sproutApp([FlagWithDefaultCommand::class]);

    expect(runSprout($app, ['add', 'alchemy'])['output'])->toContain('dev:false');
    expect(runSprout($app, ['add', 'alchemy', '--dev'])['output'])->toContain('dev:true');
    expect(runSprout($app, ['add', 'alchemy', '-d'])['output'])->toContain('dev:true');
    expect(runSprout($app, ['add', 'alchemy', '--dev=false'])['output'])->toContain('dev:false');
});

test('value options still take the token after them', function () {
    $result = runSprout(sproutApp([GreetCommand::class]), ['greet', '--greeting', 'Yo', 'Mika']);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('Yo, Mika!');
});

test('command names can contain dashes', function () {
    // https://github.com/leafsphp/leaf/issues/329
    $result = runSprout(sproutApp([DashedCommand::class]), ['auto-create-reports', 'weekly']);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('report: weekly');
});

test('dashed command names work with flag options', function () {
    $result = runSprout(sproutApp([DashedCommand::class]), ['auto-create-reports', '--dry-run']);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('report: default (dry)');
});

test('dashes and namespaces combine in command names', function () {
    $result = runSprout(sproutApp([NamespacedDashCommand::class]), ['reports:auto-create']);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('namespaced dash ok');
});
