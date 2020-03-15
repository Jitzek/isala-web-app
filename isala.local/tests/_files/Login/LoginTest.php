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
    public function attemptLogin_succesful()
    {
        $uid = 'elzenknopje';
        $passwd = 'idebian';
        $group = 'developer';
        $this->assertTrue($this->attemptLogin($uid, $passwd, $group));
    }

    /** @test */
    public function attemptLogin_wrongUser()
    {
        $uid = 'wronguser';
        $passwd = 'idebian';
        $group = 'developer';
        $this->assertFalse($this->attemptLogin($uid, $passwd, $group));
    }

    /** @test */
    public function attemptLogin_wrongPasword()
    {
        $uid = 'elzenknopje';
        $passwd = 'wrongpassword';
        $group = 'developer';
        $this->assertFalse($this->attemptLogin($uid, $passwd, $group));
    }

    /** @test */
    public function attemptLogin_wrongUserAndWrongPasword()
    {
        $uid = 'wronguser';
        $passwd = 'wrongpassword';
        $group = 'developer';
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
                            if ($args[0] == NULL && $args[1] == NULL) {
                                return true;
                            } else if ($args[0] == 'cn=Elzen Knop,ou=developers,dc=isala,dc=local' && $args[1] == 'idebian') {
                                return true;
                            } else {
                                return false;
                            }
                        case 'uidExists':
                            return true;
                        case 'getDnByUid':
                            switch ($args[0]) {
                                case 'elzenknopje':
                                    return 'cn=Elzen Knop,ou=developers,dc=isala,dc=local';
                                default:
                                    return '';
                            }
                            return 'cn=Elzen Knop,ou=developers,dc=isala,dc=local';
                        case 'userInGroup':
                            return true;
                        case 'getGroupDNByName':
                            switch ($args[1]) {
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
                                default:
                                    return '';
                            }
                    }
                })
            );

        $model = $this->getMockBuilder(LoginModel::class)
            ->getMock();

        $model->method('getLDAP')
            ->willReturn($ldap);

        /* ----- Done configuring Mocks ----- */

        if ($model->getLDAP()->getConnection()) {
            $ldapbind = $model->getLDAP()->query('bind', [NULL, NULL]); //NULL, NULL = anonymous bind
            if (!$ldapbind) {
                return false;
            }
            // Check if User Exists
            if (!$model->getLDAP()->query('uidExists', [$uid, "inetOrgPerson"])) return false;

            // Get User's DN
            $ldap_user_dn = $model->getLDAP()->query('getDnByUid', [$uid]);

            // Check if User is in Group
            $ldap_group_dn = $model->getLDAP()->query('getGroupDNByName', [$group]); // Location of the group in LDAP Directory
            if (!$model->getLDAP()->query('userInGroup', [$ldap_group_dn, $ldap_user_dn, "groupOfNames", "member"])) return false;

            // Bind to LDAP with this user
            $ldapbind = $model->getLDAP()->query('bind', [$ldap_user_dn, $passwd]);
            if (!$ldapbind) {
                return false;
            }

            $_SESSION['uid'] = $uid;
            return true;
        } else {
            die('Connection to LDAP service failed');
        }
        return false;
    }
}

class LoginModel
{
    public function getLDAP()
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
