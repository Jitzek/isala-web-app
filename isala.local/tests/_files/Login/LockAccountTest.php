<?php

use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertTrue;

/**
 * UnitTest
 * @group Unit
 */

// ClassName and FileName needs to end with "Test" (capitalizing shouldn't matter)
class LockAccountTest extends TestCase
{
    // FunctionName needs to start with "test" (capitalizing shouldn't matter)
    /** @test */
    public function lockAccount_succesful1()
    {
        $uid = 'elzenknopje';
        $this->assertTrue($this->lockAccount($uid));
    }

    private function lockAccount($uid)
    {
        /**
         * Configuring Mocks
         */



        /* ----- Done configuring Mocks ----- */

        
        return true;
    }

    private function accountIsLocked() {
        // Database Query

    }
}

class LoginModel
{
    public function getDB()
    {
    }
}

class DBConnection
{
    public function getConnection()
    {
    }

    public function query()
    {
    }
}
