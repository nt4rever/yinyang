<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function test_calculate_cache_ttl_with_default_value()
    {
        $ttl = calculate_cache_ttl();
        $this->assertIsInt($ttl);
        $this->assertGreaterThanOrEqual(5 * 60 - 30, $ttl);
        $this->assertLessThanOrEqual(5 * 60 + 30, $ttl);
    }

    public function test_calculate_cache_ttl_with_custom_value()
    {
        $customTtl = 10 * 60;
        $ttl = calculate_cache_ttl($customTtl);
        $this->assertIsInt($ttl);
        $this->assertGreaterThanOrEqual($customTtl - 30, $ttl);
        $this->assertLessThanOrEqual($customTtl + 30, $ttl);
    }
}
