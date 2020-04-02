<?php


class logger
{
    public $model;

    private function getUserIP() {
        return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
    }


    public static function log($uid, $message, $model)
    {
        $data[0] = $uid;
        if( ($data[1] = $_SERVER['REQUEST_URI']) == '') {
            $data[1] = "REQUEST_URI_UNKNOWN";
        }
        $data[2] = $message;
        $data[3] = self::getUserIP();

        $result = $model->getDB()->query('insertAuditlog', [$data]);
        if(!$result) {
            die('Logging failed');
        }
        return true;
    }
}