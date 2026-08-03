<?php

declare(strict_types=1);

if (!function_exists('sprout')) {
    /**
     * Return the Leaf instance
     * @return \Leaf\Sprout
     */
    function sprout(): Leaf\Sprout
    {
        return new \Leaf\Sprout();
    }
}

if (!function_exists('_env')) {
    /**
     * Gets the value of an environment variable.
     *
     * The environment is parsed once and cached for the rest of the
     * request. Runtime changes via putenv() won't show up here — use
     * _envUncached() for that.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function _env($key, $default = null)
    {
        static $env;

        if ($env === null) {
            $env = array_merge(getenv() ?: [], $_ENV ?? []);
        }

        if (!array_key_exists($key, $env)) {
            return $default;
        }

        return _envCast($env[$key], $default);
    }
}

if (!function_exists('_envUncached')) {
    /**
     * Read an environment variable live, skipping _env()'s per-request
     * cache. Every call hits the environment, so it is dramatically
     * slower than _env() — only reach for this when something mutates
     * the environment mid-request (eg. putenv()) and you need to see it.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function _envUncached($key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        return _envCast($value, $default);
    }
}

if (!function_exists('_envCast')) {
    /**
     * Normalize a raw environment string the way _env() does —
     * true/false/empty/null keywords and quoted values.
     *
     * @param  mixed  $value
     * @param  mixed  $default
     * @return mixed
     */
    function _envCast($value, $default = null)
    {
        if ($value === null) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return;
        }

        if (strpos($value, '"') === 0 && strpos($value, '"') === strlen($value) - 1) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
