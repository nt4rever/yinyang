<?php

if (! function_exists('calculate_cache_ttl')) {
    /**
     * Randomly cache ttl to avoid cache stampede
     *
     * @param  int  $ttl  default 5 minutes
     */
    function calculate_cache_ttl($ttl = 5 * 60): int
    {
        return random_int($ttl - 30, $ttl + 30);
    }
}
