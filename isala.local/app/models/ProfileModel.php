<?php

require_once('../app/ldap/connection.php');
require_once('../app/database/connection.php');

class ProfileModel
{
    private $title;
    private $db;
    private $ldap;
    public function __construct()
    {
        $this->title = 'Profiel';
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