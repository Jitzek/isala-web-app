<?php

use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertTrue;

/**
 * UnitTest
 * @group Unit
 */

// ClassName and FileName needs to end with "Test" (capitalizing shouldn't matter)
class ChangePasswordTest extends TestCase
{
    // FunctionName needs to start with "test" (capitalizing shouldn't matter)
    /** @test */
    public function attemptPasswordChange_succesful1()
    {
        $uid = '111111111';
        $prev_password = 'password';
        $new_password = 'password2';
        $this->assertTrue($this->attemptPasswordChange($uid, $prev_password, $new_password));
    }

    /** @test */
    public function attemptPasswordChange_user_does_not_exist()
    {
        $uid = '000000000';
        $prev_password = 'password';
        $new_password = 'password2';
        $this->assertFalse($this->attemptPasswordChange($uid, $prev_password, $new_password));
    }

    private function attemptPasswordChange($uid, $prev_password, $new_password)
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
                            else if ($args[0] == 'cn=Patient,ou=patienten,dc=isala,dc=local' && $args[1] == 'password') return true;
                            return false;
                        case 'uidExists':
                            if ($args[1] == 'inetOrgPerson') {
                                if ($args[0] == '111111111') return true;
                            } else if ($args[1] == 'account') {
                                if ($args[0] == 'account2') return true;
                            }
                            return false;
                        case 'getDnByUid':
                            switch ($args[0]) {
                                case '111111111':
                                    return 'cn=Patient,ou=patienten,dc=isala,dc=local';
                                case 'account1':
                                    return 'uid=account1,ou=administrators,ou=ccc,ou=isala,dc=isala,dc=local';
                                default:
                                    return '';
                            }
                        case 'userInGroup':
                            switch ($args[0]) {
                                case 'cn=patienten,ou=developers,dc=isala,dc=local':
                                    if ($args[1] == 'cn=Patient,ou=patienten,dc=isala,dc=local') return true;
                                    return false;
                                case 'cn=administrators,ou=administrators,ou=ccc,ou=isala,dc=isala,dc=local':
                                    if ($args[1] == 'uid=account1,ou=administrators,ou=ccc,ou=isala,dc=isala,dc=local') return true;
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
                        case 'changeUserPassword':
                            return true;
                        case 'getGroupOfUser':
                            return '';
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
                        case 'updateLastPasswordChange':
                            return;
                    }
                })
            );

        $model = $this->getMockBuilder(ChangePasswordModel::class)
            ->getMock();

        $model->method('getDB')
            ->willReturn($db);

        $model->method('getLDAP')
            ->willReturn($ldap);

        /* ----- Done configuring Mocks ----- */

        $user_dn = $model->getLDAP()->query('getDnByUid', [$uid]);
        // Check if prev_password is correct
        if (!$model->getLDAP()->query('bind', [$user_dn, $prev_password])) {
            // Display error message
            return false;
        }
        // Check if passwords are the same 
        if ($prev_password == $new_password) {
            // Display error message
            return false;
        }

        if (!$model->getLDAP()->getConnection()) {
            return false;
        }
        if (!$model->getDB()->getConnection()) {
            return false;
        }

        if (!$model->getLDAP()->query('bind', [NULL, NULL])) return false; //NULL, NULL = anonymous bind

        // Check if User Exists
        if (
            !$model->getLDAP()->query('uidExists', [$uid, "inetOrgPerson"])
            && !$model->getLDAP()->query('uidExists', [$uid, "account"])
        ) return false;

        // Get user DN
        $user_dn = $model->getLDAP()->query('getDnByUid', [$uid]);

        // Check if user is valid and/or given password is correct
        if (!$model->getLDAP()->query('bind', [$user_dn, $prev_password])) {
            // Display error message
            return false;
        }

        // Change password
        if (!$model->getLDAP()->query('changeUserPassword', [$user_dn, $new_password])) return false;

        // Edit Last Password Change column in Database
        $group = $model->getLDAP()->query('getGroupOfUser', [$uid]);
        $table = $this->convertGroupToTable($group);
        $model->getDB()->query('updateLastPasswordChange', [$uid, $table]);

        return true;
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

class ChangePasswordModel
{
    public function getDB()
    {
    }

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

class DBConnection
{
    public function getConnection()
    {
    }

    public function query()
    {
    }
}
