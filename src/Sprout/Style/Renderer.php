<?php

declare(strict_types=1);

namespace Leaf\Sprout\Style;

class Renderer
{
    protected static array $styleTagMap = [
        'error' => "\033[1;37;41m",
        'info' => "\033[1;34m",
        'comment' => "\033[1;33m",
        'question' => "\033[30;46m",
        'b' => "\033[1m",
        'u' => "\033[4m",
        'i' => "\033[3m",
        'reset' => "\033[0m",
    ];

    /**
     * Render text with styles
     * @param string $text The text to render
     * @return string
     */
    public static function render(string $text)
    {
        return static::renderTags($text);
    }

    /**
     * Render text with parsed style tags
     * @param string $text The text to render
     * @return string
     */
    public static function renderTags(string $text): string
    {
        foreach (static::$styleTagMap as $tag => $ansi) {
            $text = preg_replace("/<$tag>/i", $ansi, $text);
            $text = preg_replace("/<\/$tag>/i", "\033[0m", $text); // reset on closing tag
        }

        return $text;
    }

    /**
     * Update tag map
     */
    public static function tags(array $newTags)
    {
        static::$styleTagMap = array_merge(
            static::$styleTagMap,
            $newTags
        );
    }
}
