<?php

require_once('../app/database/connection.php');
require_once('../app/ldap/connection.php');

class AuthorizationModel
{
    private $db;
    private $ldap;
    public function __construct()
    {
        $this->db = new DBConnection();
        $this->ldap = new LDAPConnection();
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