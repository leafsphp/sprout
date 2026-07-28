<?php

declare(strict_types=1);

use Leaf\Sprout\Style\Renderer;

test('style tags render to ansi sequences', function () {
    expect(Renderer::render('<info>hello</info>'))->toBe("\033[1;34mhello\033[0m")
        ->and(Renderer::render('<error>bad</error>'))->toBe("\033[1;37;41mbad\033[0m")
        ->and(Renderer::render('<comment>note</comment>'))->toBe("\033[1;33mnote\033[0m");
});

test('bold, underline and italic tags are supported', function () {
    expect(Renderer::render('<b>hi</b>'))->toBe("\033[1mhi\033[0m")
        ->and(Renderer::render('<u>hi</u>'))->toBe("\033[4mhi\033[0m")
        ->and(Renderer::render('<i>hi</i>'))->toBe("\033[3mhi\033[0m");
});

test('plain text passes through untouched', function () {
    expect(Renderer::render('just words, no tags'))->toBe('just words, no tags');
});

test('custom tags can be registered', function () {
    Renderer::tags(['loud' => "\033[5m"]);

    expect(Renderer::render('<loud>hey</loud>'))->toBe("\033[5mhey\033[0m");
});
