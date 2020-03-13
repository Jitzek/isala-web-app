<?php

require_once('../app/ldap/connection.php');

class UserModel
{
    private $uid;
    private $ldap;

    public function __construct()
    {
        $this->uid = $_SESSION['uid'];
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
}