<?php

require_once('../app/ldap/connection.php');

class LoginModel
{
    private $title;
    private $ldap;
    public function __construct()
    {
        $this->title = 'Login';
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