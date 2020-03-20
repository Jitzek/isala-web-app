<?php

require_once('../app/ldap/connection.php');

class CreateUserModel
{
    private $title;
    private $ldap;
    public function __construct()
    {
        $this->title = 'create user';
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