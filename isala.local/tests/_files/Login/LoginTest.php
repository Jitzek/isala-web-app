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
        $this->assertTrue($this->attemptLogin($uid, $passwd));
    }

    /** @test */
    public function attemptLogin_succesful2()
    {
        $uid = 'j.janssen';
        $passwd = 'jan';
        $this->assertTrue($this->attemptLogin($uid, $passwd));
    }

    /** @test */
    public function attemptLogin_succesful3()
    {
        $uid = 'D.i.Eet';
        $passwd = 'dieet';
        $this->assertTrue($this->attemptLogin($uid, $passwd));
    }

    /** @test */
    public function attemptLogin_succesful4()
    {
        $uid = 'admin';
        $passwd = 'isaladebian';
        $this->assertTrue($this->attemptLogin($uid, $passwd));
    }

    /** @test */
    public function attemptLogin_wrongUser()
    {
        $uid = 'wronguser';
        $passwd = 'idebian';
        $this->assertFalse($this->attemptLogin($uid, $passwd));
    }

    /** @test */
    public function attemptLogin_wrongPasword()
    {
        $uid = 'elzenknopje';
        $passwd = 'wrongpassword';
        $this->assertFalse($this->attemptLogin($uid, $passwd));
    }

    /** @test */
    public function attemptLogin_wrongUserAndWrongPasword()
    {
        $uid = 'wronguser';
        $passwd = 'wrongpassword';
        $this->assertFalse($this->attemptLogin($uid, $passwd));
    }

    /** @test */
    public function attemptLogin_lockedAccount1()
    {
        $uid = 'locked';
        $passwd = 'lock';
        $this->assertFalse($this->attemptLogin($uid, $passwd));
    }

    /** @test */
    public function attemptLogin_lockedExpired()
    {
        $uid = 'lockedexpired';
        $passwd = 'lock';
        $this->assertTrue($this->attemptLogin($uid, $passwd));
    }

    public function attemptLogin($uid, $passwd)
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
                            else if ($args[0] == 'uid=lockedexpired,ou=patienten,dc=isala,dc=local' && $args[1] == 'lock') return true;
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
                                case 'lockedexpired':
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
                        case 'getGroupOfUid':
                            switch ($args[0]) {
                                case 'elzenknopje':
                                    return 'developers';
                                case 'j.janssen':
                                    return 'dokters';
                                case 'D.i.Eet':
                                    return 'dietisten';
                                case 'admin':
                                    return 'administrators';
                                case 'locked':
                                    return 'patienten';
                                case 'lockedexpired':
                                    return 'patienten';
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
                        case 'lockedIPArray':
                            if ($args[0] == 'locked') return ['0.0.0.0', '1.1.1.1'];
                            else if ($args[0] == 'lockedexpired') return ['1.1.1.1', '0.0.0.0'];
                            return [];
                        case 'lockExpired':
                            if ($args[0] == 'locked' && $args[1] == '0.0.0.0') return true;
                            else if ($args[0] == 'locked' && $args[1] == '1.1.1.1') return false;
                            else if ($args[0] == 'lockedexpired' && $args[1] == '1.1.1.1') return true;
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
            $err_msg = 'Connection Failed';
            return false;
        }
        if (!$model->getDB()->getConnection()) {
            $err_msg = 'Connection Failed';
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

        // Check if ip is blocked
        $locked_ip_arr = $model->getDB()->query('lockedIPArray', [$uid]);
        if (count($locked_ip_arr) > 0) {
            foreach($locked_ip_arr as $locked_ip) {
                // If ip is locked
                if ($locked_ip == $this->getUserIP()) {
                    // Check if block has expired
                    if (!$model->getDB()->query('lockExpired', [$uid, $locked_ip])) {
                        $err_msg = 'Account Locked for exceeding login attempts, please wait before trying again';
                        return false;
                    }
                }
            }
        }


        // Bind to LDAP with this user (check password)
        if (!$model->getLDAP()->query('bind', [$ldap_user_dn, $passwd])) return false;

        $_SESSION['uid'] = $uid;

        return true;
    }

    private function getUserIP()
    {
        return '1.1.1.1';
    }

    private function convertGroupToTable($group)
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
