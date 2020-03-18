<?php

use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertTrue;

/**
 * UnitTest
 * @group Unit
 */

// ClassName and FileName needs to end with "Test" (capitalizing shouldn't matter)
class LoginTest extends TestCase
{
    // FunctionName needs to start with "test" (capitalizing shouldn't matter)
    /** @test */
    public function attemptLogin_succesful1()
    {
        $uid = 'elzenknopje';
        $passwd = 'idebian';
        $group = 'developers';
        $this->assertTrue($this->attemptLogin($uid, $passwd, $group));
    }

    /** @test */
    public function attemptLogin_succesful2()
    {
        $uid = 'j.janssen';
        $passwd = 'jan';
        $group = 'dokters';
        $this->assertTrue($this->attemptLogin($uid, $passwd, $group));
    }

    /** @test */
    public function attemptLogin_succesful3()
    {
        $uid = 'D.i.Eet';
        $passwd = 'dieet';
        $group = 'anders';
        $this->assertTrue($this->attemptLogin($uid, $passwd, $group));
    }

    /** @test */
    public function attemptLogin_succesful4()
    {
        $uid = 'admin';
        $passwd = 'isaladebian';
        $group = 'anders';
        $this->assertTrue($this->attemptLogin($uid, $passwd, $group));
    }

    /** @test */
    public function attemptLogin_wrongUser()
    {
        $uid = 'wronguser';
        $passwd = 'idebian';
        $group = 'developers';
        $this->assertFalse($this->attemptLogin($uid, $passwd, $group));
    }

    /** @test */
    public function attemptLogin_wrongPasword()
    {
        $uid = 'elzenknopje';
        $passwd = 'wrongpassword';
        $group = 'developers';
        $this->assertFalse($this->attemptLogin($uid, $passwd, $group));
    }

    /** @test */
    public function attemptLogin_wrongUserAndWrongPasword()
    {
        $uid = 'wronguser';
        $passwd = 'wrongpassword';
        $group = 'developers';
        $this->assertFalse($this->attemptLogin($uid, $passwd, $group));
    }

    /** @test */
    public function attemptLogin_wrongGroup1()
    {
        $uid = 'elzenknopje';
        $passwd = 'idebian';
        $group = 'anders';
        $this->assertFalse($this->attemptLogin($uid, $passwd, $group));
    }

    /** @test */
    public function attemptLogin_wrongGroup2()
    {
        $uid = 'elzenknopje';
        $passwd = 'idebian';
        $group = 'dokters';
        $this->assertFalse($this->attemptLogin($uid, $passwd, $group));
    }

    /** @test */
    public function attemptLogin_wrongGroup3()
    {
        $uid = 'elzenknopje';
        $passwd = 'idebian';
        $group = 'anders';
        $this->assertFalse($this->attemptLogin($uid, $passwd, $group));
    }

    /** @test */
    public function attemptLogin_lockedAccount1()
    {
        $uid = 'locked';
        $passwd = 'lock';
        $group = 'patienten';
        $this->assertFalse($this->attemptLogin($uid, $passwd, $group));
    }

    public function attemptLogin($uid, $passwd, $group)
    {
        /**
         * Configuring Mocks
         */
        $ldap = $this->getMockBuilder(LDAPConnection::class)
            ->getMock();

        $ldap->method('getConnection')
            ->willReturn(true);

        $ldap->method('query')
            ->will(
                $this->returnCallback(function ($arg, $args) {
                    switch ($arg) {
                        case 'bind':
                            if ($args[0] == NULL && $args[1] == NULL) return true;
                            else if ($args[0] == 'cn=Elzen Knop,ou=developers,dc=isala,dc=local' && $args[1] == 'idebian') return true;
                            else if ($args[0] == 'cn=Jan,ou=dokters,ou=isala,dc=isala,dc=local' && $args[1] == 'jan') return true;
                            else if ($args[0] == 'cn=diederik,ou=dietisten,ou=isala,dc=isala,dc=local' && $args[1] == 'dieet') return true;
                            else if ($args[0] == 'uid=admin,ou=administrators,ou=ccc,ou=isala,dc=isala,dc=local' && $args[1] == 'isaladebian') return true;
                            else if ($args[0] == 'uid=locked,ou=patienten,dc=isala,dc=local' && $args[1] == 'lock') return true;
                            return false;
                        case 'uidExists':
                            if ($args[1] == 'inetOrgPerson') {
                                if ($args[0] != 'admin') return true;
                            } else if ($args[1] == 'account') {
                                if ($args[0] == 'admin') return true;
                            }
                            return false;
                        case 'getDnByUid':
                            switch ($args[0]) {
                                case 'elzenknopje':
                                    return 'cn=Elzen Knop,ou=developers,dc=isala,dc=local';
                                case 'j.janssen':
                                    return 'cn=Jan,ou=dokters,ou=isala,dc=isala,dc=local';
                                case 'D.i.Eet':
                                    return 'cn=diederik,ou=dietisten,ou=isala,dc=isala,dc=local';
                                case 'admin':
                                    return 'uid=admin,ou=administrators,ou=ccc,ou=isala,dc=isala,dc=local';
                                case 'locked':
                                    return 'uid=locked,ou=patienten,dc=isala,dc=local';
                                default:
                                    return '';
                            }
                        case 'userInGroup':
                            switch ($args[0]) {
                                case 'cn=developers,ou=developers,dc=isala,dc=local':
                                    if ($args[1] == 'cn=Elzen Knop,ou=developers,dc=isala,dc=local') return true;
                                    return false;
                                case 'cn=dokters,ou=dokters,ou=isala,dc=isala,dc=local':
                                    if ($args[1] == 'cn=Jan,ou=dokters,ou=isala,dc=isala,dc=local') return true;
                                    return false;
                                case 'cn=dietisten,ou=dietisten,ou=isala,dc=isala,dc=local':
                                    if ($args[1] == 'cn=diederik,ou=dietisten,ou=isala,dc=isala,dc=local') return true;
                                    return false;
                                case 'cn=administrators,ou=administrators,ou=ccc,ou=isala,dc=isala,dc=local':
                                    if ($args[1] == 'uid=admin,ou=administrators,ou=ccc,ou=isala,dc=isala,dc=local') return true;
                                case 'cn=patienten,ou=patienten,dc=isala,dc=local':
                                    if ($args[1] == 'uid=locked,ou=patienten,dc=isala,dc=local') return true;
                                    return false;
                                default:
                                    return false;
                            }
                            return true;
                        case 'getGroupDNByName':
                            switch ($args[0]) {
                                case 'developers':
                                    return 'cn=developers,ou=developers,dc=isala,dc=local';
                                case 'patienten':
                                    return 'cn=patienten,ou=patienten,dc=isala,dc=local';
                                case 'dokters':
                                    return 'cn=dokters,ou=dokters,ou=isala,dc=isala,dc=local';
                                case 'dietisten':
                                    return 'cn=dietisten,ou=dietisten,ou=isala,dc=isala,dc=local';
                                case 'fysiotherapeuten':
                                    return 'cn=fysiotherapeuten,ou=fysiotherapeuten,ou=isala,dc=isala,dc=local';
                                case 'psychologen':
                                    return 'cn=psychologen,ou=psychologen,ou=isala,dc=isala,dc=local';
                                case 'administrators':
                                    return 'cn=administrators,ou=administrators,ou=ccc,ou=isala,dc=isala,dc=local';
                                default:
                                    return '';
                            }
                    }
                })
            );

        $db = $this->getMockBuilder(DBConnection::class)
            ->getMock();

        $db->method('getConnection')
            ->willReturn(true);

        $db->method('query')
            ->will(
                $this->returnCallback(function ($arg, $args) {
                    switch ($arg) {
                        case 'userIsLocked':
                            if ($args[0] == 'elzenknopje') return false;
                            else if ($args[0] == 'j.janssen' && $args[1] == 'Dokter') return false;
                            else if ($args[0] == 'D.i.Eet' && $args[1] == 'Diëtist') return false;
                            else if ($args[0] == 'admin') return false;
                            else if ($args[0] == 'locked' && $args[1] == 'Patiënt') return true;
                            return true;
                    }
                })
            );

        $model = $this->getMockBuilder(LoginModel::class)
            ->getMock();

        $model->method('getLDAP')
            ->willReturn($ldap);

        $model->method('getDB')
            ->willReturn($db);

        /* ----- Done configuring Mocks ----- */

        // Check for LDAP Connection
        if (!$model->getLDAP()->getConnection()) {
            // Display error message
            return false;
        }
        if (!$model->getDB()->getConnection()) {
            // Display error message
            return false;
        }

        if (!$model->getLDAP()->query('bind', [NULL, NULL])) return false;
        // Check if User Exists
        if (
            !$model->getLDAP()->query('uidExists', [$uid, "inetOrgPerson"])
            && !$model->getLDAP()->query('uidExists', [$uid, "account"])
        ) return false;

        // Get User's DN
        $ldap_user_dn = $model->getLDAP()->query('getDnByUid', [$uid]);

        // Check if User is in Group
        if ($group == 'anders') {
            $possible_groups = ["dietisten", "psychologen", "fysiotherapeuten", "administrators"];
            foreach ($possible_groups as $possible_group) {
                $ldap_group_dn = $model->getLDAP()->query('getGroupDNByName', [$possible_group]); // Location of the group in LDAP Directory
                if ($model->getLDAP()->query('userInGroup', [$ldap_group_dn, $ldap_user_dn, "groupOfNames", "member"])) {
                    // Check if account is locked
                    $table = $this->getTableNameForPostedGroup($possible_group);
                    if ($model->getDB()->query('userIsLocked', [$uid, $table])) {
                        // Display error message
                        return false;
                    }
                    break;
                }
                $ldap_group_dn = '';
            }
            if ($ldap_group_dn == '') return false;
        } else {
            if ($group != 'developers' && $group != 'patienten' && $group != 'dokters') return false; //TODO: remove developers
            $ldap_group_dn = $model->getLDAP()->query('getGroupDNByName', [$group]); // Location of the group in LDAP Directory
            if (!$model->getLDAP()->query('userInGroup', [$ldap_group_dn, $ldap_user_dn, "groupOfNames", "member"])) return false;

            // Check if account is locked
            $table = $this->getTableNameForPostedGroup($group);
            if ($model->getDB()->query('userIsLocked', [$uid, $table])) {
                // Display error message
                return false;
            }
        }

        // Bind to LDAP with this user (check password)
        if (!$model->getLDAP()->query('bind', [$ldap_user_dn, $passwd])) return false;

        $_SESSION['uid'] = $uid;
        return true;
    }

    private function getTableNameForPostedGroup($group)
    {
        switch ($group) {
            case 'patienten':
                return 'Patiënt';
            case 'dokters':
                return 'Dokter';
            case 'dietisten':
                return 'Diëtist';
            case 'dokters':
                return 'Dokter';
            case 'psychologen':
                return 'Psycholoog';
            case 'fysiotherapeuten':
                return 'Fysiotherapeut';
            case 'administrators':
                return 'Admin';
            default:
                return '';
        }
    }
}

class LoginModel
{
    public function getLDAP()
    {
    }

    public function getDB()
    {
    }
}

class LDAPConnection
{
    public function getConnection()
    {
    }

    public function query()
    {
    }
}

class DBConnection
{
    public function getConnection()
    {
    }

    public function query()
    {
    }
}
