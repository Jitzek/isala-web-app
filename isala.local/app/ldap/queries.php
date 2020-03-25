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

    public function getGroupDNByName($group)
    {
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

    public function getGroupOfUser($user_dn)
    {
        $filter = vsprintf("(&(objectClass=groupOfNames)(member=%s))", $this->arrSanitizeFilter([$user_dn]));
        $entries = ldap_search($this->conn, $this->baseDN, $filter);
        $results = ldap_get_entries($this->conn, $entries);
        return $results[0]['cn'][0];
    }

    public function getGroupOfUid($ldap_user_uid)
    {
        $ldap_user_dn = $this->getDnByUid($ldap_user_uid) | '';
        $ldap_group_ObjClass = "groupOfNames";
        $ldap_user_ObjClass = "member";
        $filter = vsprintf("(&(objectClass=%s)(%s=%s))", $this->arrSanitizeFilter([$ldap_group_ObjClass, $ldap_user_ObjClass, $ldap_user_dn]));
        $entries = ldap_search($this->conn, $this->baseDN, $filter);
        $results = ldap_get_entries($this->conn, $entries);
        if ($results["count"] < 1) return 'group not found';
        return $results[0]['cn'][0];
    }

    public function add_inetOrgPerson($cn, $sn, $uid, $group_dn, $passwd)
    {
        /*if (!$this->bind('cn=admin,dc=isala,dc=local', 'isaladebian')) return false; //TODO: Make new LDAP account with limited permissions
        $prepare['cn'] = ldap_escape($cn, '', LDAP_ESCAPE_DN);
        $prepare['sn'] = ldap_escape($sn, '', LDAP_ESCAPE_DN);
        $prepare['uid'] = ldap_escape($uid, '', LDAP_ESCAPE_DN);
        $salt = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+?><":}{|-=[];,./', 6)), 0, 6);
        $prepare["userpassword"] = "{SSHA}" . base64_encode(sha1($passwd . $salt, true) . $salt);
        $prepare['objectclass'] = 'inetOrgPerson';
        if (ldap_add($this->conn, "cn=". ldap_escape($cn, '', LDAP_ESCAPE_DN) . ",ou=patienten,dc=isala,dc=local", $prepare)) {
            echo 'yes';
            return;
        }
        echo 'no';*/
    }

    public function changeUserPassword($user_dn, $password)
    {
        if (!$this->bind('cn=admin,dc=isala,dc=local', 'isaladebian')) return false; //TODO: Make new LDAP account with limited permissions

        // Encrypt password
        $salt = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+?><":}{|-=[];,./', 6)), 0, 6);
        $entry["userpassword"] = "{SSHA}" . base64_encode(sha1($password . $salt, true) . $salt);

        if (!ldap_mod_replace($this->conn, $user_dn, $entry)) return false;
        return true;
    }

    public function getFirstNameByUid($uid)
    {
        $filter = vsprintf("(uid=%s)", $this->arrSanitizeFilter([$uid]));
        $entries = ldap_search($this->conn, $this->baseDN, $filter);
        $results = ldap_get_entries($this->conn, $entries);
        return $results[0]["cn"][0];
    }

    public function getLastNameByUid($uid)
    {
        $filter = vsprintf("(uid=%s)", $this->arrSanitizeFilter([$uid]));
        $entries = ldap_search($this->conn, $this->baseDN, $filter);
        $results = ldap_get_entries($this->conn, $entries);
        return $results[0]["sn"][0];
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
