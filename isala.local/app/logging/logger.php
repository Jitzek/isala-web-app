<?php

class logger
{
    private function getUserIP() {
        return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
    }


    public static function log($uid, $message, $model)
    {
        $data['uid'] = $uid;
        $data['group'] = $model->getLDAP()->query('getGroupOfUid', [$uid]);
        
        if( ($data['url'] = $_SERVER['REQUEST_URI']) == '') {
            $data['url'] = "REQUEST_URI_UNKNOWN";
        }
        $data['msg'] = $message;
        $data['ip'] = self::getUserIP();

        $result = $model->getDB()->query('insertAuditlog', [$data]);
        if(!$result) {
            die('Logging failed');
        }
        return true;
    }
}