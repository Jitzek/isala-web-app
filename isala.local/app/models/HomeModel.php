<?php

require_once('../app/ldap/connection.php');

class HomeModel
{
    private $title;
    private $ldap;

    public function __construct()
    {
        $this->title = 'Home';
        $this->ldap = new LDAPConnection();
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getLDAP()
    {
        return $this->ldap;
    }
}