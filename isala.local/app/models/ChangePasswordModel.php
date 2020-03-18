<?php

require_once('../app/database/connection.php');
require_once('../app/ldap/connection.php');

class ChangePasswordModel
{
    private $title;
    private $db;
    private $ldap;
    public function __construct()
    {
        $this->title = 'Change password';
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