<?php

class LDAPConnection
{
    private $hostname = "isala.local"; // "localhost" also accepted

    public function __construct()
    {
        $this->conn = ldap_connect($this->hostname);
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function bind($ldaprdn, $ldappass)
    {
        ldap_set_option($this->conn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        return $ldapbind = ldap_bind($this->conn, $ldaprdn, $ldappass);
    }

    public function uidExists($ldapdn, $uid, $ldap_ObjClass)
    {
        $filter = sprintf("(&(objectClass=%s)(uid=%s))", $ldap_ObjClass, $uid);
        $entries = ldap_search($this->conn, $ldapdn, $filter);
        $results = ldap_get_entries($this->conn, $entries);

        if ($results["count"] < 1) return false;
        return true;
    }

    public function getDnByUid($ldapdn, $uid) {
        $filter = sprintf("(uid=%s)", $uid);
        $entries = ldap_search($this->conn, $ldapdn, $filter);
        $results = ldap_get_entries($this->conn, $entries);
        return $results[0]["dn"];
    }

    public function userInGroup($ldapdn, $ldap_user, $ldap_group_ObjClass, $ldap_user_ObjClass) {
        $filter = sprintf("(&(objectClass=%s)(%s=%s))", $ldap_group_ObjClass, $ldap_user_ObjClass, $ldap_user);
        $entries = ldap_search($this->conn, $ldapdn, $filter);
        $results = ldap_get_entries($this->conn, $entries);
    
        if ($results["count"] < 1) return false;
        return true;
    }
}