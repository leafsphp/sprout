<?php

declare(strict_types=1);

if (!function_exists('sprout')) {
    /**
     * Return the Leaf instance
     *
     */
    function sprout(): Leaf\Sprout
    {
        if (!(\Leaf\Config::getStatic('sprout'))) {
            \Leaf\Config::singleton('sprout', function () {
                return new \Leaf\Sprout();
            });
        }

        return \Leaf\Config::get('sprout');
    }
}

if (!function_exists('_env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function _env($key, $default = null)
    {
        $env = array_merge(getenv() ?? [], $_ENV ?? []);

        return $env[$key] ??= $default;
    }
}
