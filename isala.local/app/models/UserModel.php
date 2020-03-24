<?php

require_once('../app/database/connection.php');
require_once('../app/ldap/connection.php');

/**
 * Model containing minimal user info
*/
class UserModel
{
    private $uid;
    private $group;
    private $cn;
    private $sn;
    protected $db;
    protected $ldap;

    public function __construct($uid)
    {
        $this->uid = $uid;
        $this->db = new DBConnection();
        $this->ldap = new LDAPConnection();
        $this->group = $this->ldap->query('getGroupOfUid', [$this->uid]);
        $this->cn = $this->ldap->query('getFirstNameByUid', [$this->uid]);
        $this->sn = $this->ldap->query('getLastNameByUid', [$this->uid]);
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function getFirstName()
    {
        return $this->cn;
    }

    public function getLastName()
    {
        return $this->sn;
    }

    public function getFullName()
    {
        return $this->cn . ' ' . $this->sn;
    }

    public function getGroup()
    {
        return $this->group;
    }
}