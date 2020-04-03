<?php

require_once('../app/database/connection.php');
require_once('../app/ldap/connection.php');

class CreateUserModel
{
    private $title;
    private $db;
    private $ldap;
    public function __construct()
    {
        $this->title = 'Create User';
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