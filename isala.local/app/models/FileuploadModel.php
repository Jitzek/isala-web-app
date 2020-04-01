<?php

require_once('../app/ldap/connection.php');
require_once('../app/database/connection.php');

class FileuploadModel
{
    private $title;
    private $ldap;
    private $db;
    public function __construct()
    {
        $this->title = 'Fileupload';
        $this->ldap = new LDAPConnection();
        $this->db = new DBConnection();
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