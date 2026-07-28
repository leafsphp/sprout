<?php

declare(strict_types=1);

// binary-level checks: a real php process, real exit() propagation

$appScript = <<<'PHP'
$app = sprout()->createApp(['name' => 'BinTest', 'version' => 'v1']);

$app->command('work', function () {
    $this->info('working');
    return 0;
});

$app->command('explode', function () {
    $this->error('boom');
    return 3;
});

exit((int) ($app->run(false) ?? 0));
PHP;

test('a real binary exits 0 on success', function () use ($appScript) {
    $result = execSprout($appScript, 'work');

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('working');
})->skipOnWindows();

test('a real binary propagates non-zero exit codes', function () use ($appScript) {
    $result = execSprout($appScript, 'explode');

    expect($result['code'])->toBe(3)
        ->and($result['output'])->toContain('boom');
})->skipOnWindows();

test('a real binary exits 1 for unknown commands', function () use ($appScript) {
    $result = execSprout($appScript, 'nope');

    expect($result['code'])->toBe(1);
})->skipOnWindows();

test('text prompts accept piped input', function () {
    $script = <<<'PHP'
$answers = sprout()->prompt([[
    'type' => 'text',
    'name' => 'name',
    'message' => 'Your name',
]]);

echo 'got:' . $answers['name'] . PHP_EOL;
exit(0);
PHP;

    $result = execSprout($script, '', "Mika\n");

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('got:Mika');
})->skipOnWindows();

test('confirm prompts accept piped input', function () {
    $script = <<<'PHP'
$confirmed = sprout()->confirm('Proceed?');

echo 'confirmed:' . ($confirmed ? 'yes' : 'no') . PHP_EOL;
exit(0);
PHP;

    $result = execSprout($script, '', "y\n");

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('confirmed:yes');
})->skipOnWindows();

test('prompts with a null type are skipped', function () {
    $script = <<<'PHP'
$answers = sprout()->prompt([[
    'type' => null,
    'name' => 'skipped',
    'message' => 'Should never render',
]]);

echo 'done:' . var_export($answers['skipped'] ?? null, true) . PHP_EOL;
exit(0);
PHP;

    $result = execSprout($script);

    expect($result['code'])->toBe(0)
        ->and($result['output'])->toContain('done:');
})->skipOnWindows();
