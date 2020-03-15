<?php

class LDAPQueries 
{
    private $conn;
    private $baseDN = "dc=isala,dc=local";
    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    public function bind($ldapdn, $ldappass)
    {
        ldap_set_option($this->conn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        return ldap_bind($this->conn, $ldapdn, $ldappass);
    }

    public function uidExists($uid, $ldap_ObjClass)
    {
        $filter = vsprintf("(&(objectClass=%s)(uid=%s))", $this->arrSanitizeFilter([$ldap_ObjClass, $uid]));
        $entries = ldap_search($this->conn, $this->baseDN, $filter);
        $results = ldap_get_entries($this->conn, $entries);

        if ($results["count"] < 1) return false;
        return true;
    }

    public function getGroupDNByName($group) {
        $filter = vsprintf("(&(objectClass=%s)(cn=%s))", $this->arrSanitizeFilter(["groupOfNames", $group]));
        $entries = ldap_search($this->conn, $this->baseDN, $filter);
        $results = ldap_get_entries($this->conn, $entries);
        return $results[0]["dn"];
    }

    public function getDnByUid($uid) 
    {
        $filter = vsprintf("(uid=%s)", $this->arrSanitizeFilter([$uid]));
        $entries = ldap_search($this->conn, $this->baseDN, $filter);
        $results = ldap_get_entries($this->conn, $entries);
        return $results[0]["dn"];
    }

    public function userInGroup($ldapdn, $ldap_user, $ldap_group_ObjClass, $ldap_user_ObjClass) 
    {
        $filter = vsprintf("(&(objectClass=%s)(%s=%s))", $this->arrSanitizeFilter([$ldap_group_ObjClass, $ldap_user_ObjClass, $ldap_user]));
        $entries = ldap_search($this->conn, $ldapdn, $filter);
        $results = ldap_get_entries($this->conn, $entries);
    
        if ($results["count"] < 1) return false;
        return true;
    }

    public function getGroupOfUid($ldap_user_uid)
    {
        $ldapdn = "dc=isala,dc=local";
        $ldap_user_dn = $this->getDnByUid($ldap_user_uid) | '';
        $ldap_group_ObjClass = "groupOfNames";
        $ldap_user_ObjClass = "member";
        $filter = vsprintf("(&(objectClass=%s)(%s=%s))", $this->arrSanitizeFilter([$ldap_group_ObjClass, $ldap_user_ObjClass, $ldap_user_dn]));
        $entries = ldap_search($this->conn, $ldapdn, $filter);
        $results = ldap_get_entries($this->conn, $entries);
        if ($results["count"] < 1) return 'group not found';
        return $results[0]['cn'][0];
    }

    /**
     * Sanitizes elements of array to be used in a LDAP Filter
    */
    private function arrSanitizeFilter($arr)
    {
        for ($i = 0; $i < count($arr); $i++) {
            $arr[$i] = ldap_escape($arr[$i], '', LDAP_ESCAPE_FILTER);
        }
        return $arr;
    }
}

    