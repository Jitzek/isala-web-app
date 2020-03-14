<?php

use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertTrue;

/**
 * UnitTest
 * @group Unit
*/

// ClassName and FileName needs to end with "Test" (capitalizing shouldn't matter)
class UnitTest extends TestCase
{
    // FunctionName needs to start with "test" (capitalizing shouldn't matter)
    /** @test */
    public function test_function()
    {
        $this->assertTrue(true);
    }
}
