<?php

use PHPUnit\Framework\TestCase;

class LoggingTest extends TestCase
{
    /** @test */
    public function log_Correctly1() {
        $uid = '123';
        $message = 'User was created';
        $model = $this->getMockBuilder(Model::class)
            ->getMock();

        $this->assertTrue($this->log($uid, $message, $model));
    }

    /** @test */
    public function log_Incorrectyly1() {
        $uid = NULL;
        $message = NULL;
        $model = $this->getMockBuilder(Model::class)
            ->getMock();

        $this->assertFalse($this->log($uid, $message, $model));
    }

    public function log($uid, $message, $model) {
        $db = $this->getMockBuilder(DBConnection::class)
            ->getMock();

        $model->method('getDB')
            ->willReturn($db);

        $db->method('getConnection')
            ->willReturn(true);

        $db->method('query')
            ->will(
                $this->returnCallback(function ($arg, $args) {
                    if($arg == 'insertAuditlog' && $args[0] != NULL && $args[1] != NULL) {
                        return true;
                    } else {
                        return false;
                    }
                }
            )
        );

        if (!$model->getDB()->getConnection()) {
            $err_msg = 'Connection Failed';
            return false;
        }

        $data1 = $uid;
        $data2 = $message;
        $data3 = $model;

        if($model->getDB()->query('insertAuditlog',[$data1, $data2, $data3])) return true;
        else return false;
    }
}

class Model
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