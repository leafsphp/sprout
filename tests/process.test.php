<?php

declare(strict_types=1);

test('sprout()->run() returns the process exit code', function () {
    $php = escapeshellarg(PHP_BINARY);

    ob_start();
    $ok = sprout()->run("$php -r \"exit(0);\"");
    $fail = sprout()->run("$php -r \"exit(2);\"");
    ob_end_clean();

    expect($ok)->toBe(0)->and($fail)->toBe(2);
});

test('process captures output', function () {
    $php = escapeshellarg(PHP_BINARY);
    $process = sprout()->process("$php -r \"echo 'hi from process';\"");
    $process->run();

    expect($process->isSuccessful())->toBeTrue()
        ->and($process->getOutput())->toContain('hi from process');
});

test('failed processes report exit code and are not successful', function () {
    $php = escapeshellarg(PHP_BINARY);
    $process = sprout()->process("$php -r \"fwrite(STDERR, 'boom'); exit(5);\"");
    $process->run();

    expect($process->isSuccessful())->toBeFalse()
        ->and($process->getExitCode())->toBe(5)
        ->and($process->getErrorOutput())->toContain('boom');
});
