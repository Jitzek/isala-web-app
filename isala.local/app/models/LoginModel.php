<?php

require_once('../app/ldap/connection.php');
require_once('../app/database/connection.php');

class LoginModel
{
    private $title;
    private $db;
    private $ldap;
    public function __construct()
    {
        $this->title = 'Login';
        $this->db = new DBConnection();
        $this->ldap = new LDAPConnection();
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDB()
    {
        return $this->db;
    }

    public function getLDAP()
    {
        return $this->ldap;
    }
}