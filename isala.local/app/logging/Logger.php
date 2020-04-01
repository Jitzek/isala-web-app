<?php


class logger
{
    protected $model;
    public $err_msg = '';


    public static function getUserIP() {
        if( array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',')>0) {
                $addr = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
                return trim($addr[0]);
            } else {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        }
        else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }


    public static function log($uid, $message, $model)
    {
        $data[0] = $uid;
        if( ($data[1] = $_SERVER['REQUEST_URI']) == '') {
            $data[1] = "REQUEST_URI_UNKNOWN";
        }
        $data[2] = $message;
        $data[3] = logger::getUserIP();

        $result = $model->getDB()->query('insertAuditlog', [$data]);
        if(!$result) {
            die('Logging failed');
        }
        return true;
    }
}