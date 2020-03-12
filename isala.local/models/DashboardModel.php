<?php
class DashboardModel
{
    private $title;
    private $user;
    private $passwd;

    public function __construct()
    {
        $this->title = "Dashboard";
        $this->user = $_SERVER["AUTHENTICATE_UID"];
        $this->passwd = $_SERVER["PHP_AUTH_PW"];
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getPasswd()
    {
        return $this->passwd;
    }
}
