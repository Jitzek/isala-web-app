<?php

require_once('../app/ldap/connection.php');
require_once('../app/database/connection.php');

class UserModel
{
    private $uid;
    private $db;
    private $ldap;

    public function __construct()
    {
        $this->uid = $_SESSION['uid'];
        $this->db = new DBConnection();
        $this->ldap = new LDAPConnection();
    }

    public function getName()
    {
        //SQL Query
        return $_SESSION['uid']; // placeholder
    }

    public function getGroup()
    {
        return $this->ldap->query('getGroupOfUid', [$this->uid]);
    }

    public function setCookie($state)
    {
        $group = $this->ldap->query('getGroupOfUid', [$this->uid]);
        $table = $this->db->query('convertGroupToTable', [$group]);
        $this->db->query('setCookie', [$this->uid, $table, $state]);
    }

    public function getCookie()
    {
        $group = $this->ldap->query('getGroupOfUid', [$this->uid]);
        $table = $this->db->query('convertGroupToTable', [$group]);
        return $this->db->query('getCookie', [$this->uid, $table]);
    }
}