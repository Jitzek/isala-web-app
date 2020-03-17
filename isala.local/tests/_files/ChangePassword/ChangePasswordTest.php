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
                            $this->count++;
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
                    }
                })
            );

        $model = $this->getMockBuilder(ChangePasswordModel::class)
            ->getMock();

        $model->method('getLDAP')
            ->willReturn($ldap);

        /* ----- Done configuring Mocks ----- */

        if ($model->getLDAP()->getConnection()) {
            if (!$model->getLDAP()->query('bind', [NULL, NULL])) return false; //NULL, NULL = anonymous bind

             // Check if User Exists
             if (
                !$model->getLDAP()->query('uidExists', [$uid, "inetOrgPerson"])
                && !$model->getLDAP()->query('uidExists', [$uid, "account"])
            ) return false;

            // Get user DN
            $user_dn = $model->getLDAP()->query('getDnByUid', [$uid]);

            // Check if user is valid and/or given password is correct
            if (!$model->getLDAP()->query('bind', [$user_dn, $prev_password])) return false;

            // Change password
            if (!$model->getLDAP()->query('changeUserPassword', [$user_dn, $new_password])) return false;

            return true;
        } else {
            die('Connection to LDAP service failed');
        }
        return false;
    }
}

class ChangePasswordModel
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
