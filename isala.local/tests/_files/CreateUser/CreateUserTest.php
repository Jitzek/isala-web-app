<?php

use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertTrue;

/**
 * UnitTest
 * @group Unit
 */

// ClassName and FileName needs to end with "Test" (capitalizing shouldn't matter)
class CreateUserTest extends TestCase
{
    // FunctionName needs to start with "test" (capitalizing shouldn't matter)
    /** @test */
    public function attemptUserCreation_succesful1()
    {
        $uid = '111111111';
        $firstname = 'Peter';
        $lastname = 'Patient';
        $group = 'patienten';
        $password = 'peter';
        $this->assertTrue($this->attemptUserCreation($uid, $firstname, $lastname, $group, $password));
    }

    /** @test */
    public function attemptUserCreation_succesful2()
    {
        $uid = 'account1';
        $firstname = '';
        $lastname = '';
        $group = 'administrators';
        $password = 'account';
        $this->assertTrue($this->attemptUserCreation($uid, $firstname, $lastname, $group, $password));
    }

    /** @test */
    public function attemptUserCreation_account_exists1()
    {
        $uid = '000000000';
        $firstname = 'Peter';
        $lastname = 'Patient';
        $group = 'patienten';
        $password = 'peter';
        $this->assertFalse($this->attemptUserCreation($uid, $firstname, $lastname, $group, $password));
    }

    /** @test */
    public function attemptUserCreation_account_exists2()
    {
        $uid = 'account2';
        $firstname = '';
        $lastname = '';
        $group = 'administrators';
        $password = 'account';
        $this->assertFalse($this->attemptUserCreation($uid, $firstname, $lastname, $group, $password));
    }

    private function attemptUserCreation($uid, $firstname, $lastname, $group, $password)
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
                            }
                            return false;
                        case 'uidExists':
                            if ($args[1] == 'inetOrgPerson') {
                                if ($args[0] == '000000000') return true;
                            } else if ($args[1] == 'account') {
                                if ($args[0] == 'account2') return true;
                            }
                            $this->count++;
                            return false;
                        case 'getDnByUid':
                            switch ($args[0]) {
                                case 'patiënt1':
                                    return 'cn=Peter,ou=patienten,dc=isala,dc=local';
                                case 'account1':
                                    return 'uid=account1,ou=administrators,ou=ccc,ou=isala,dc=isala,dc=local';
                                default:
                                    return '';
                            }
                        case 'userInGroup':
                            switch ($args[0]) {
                                case 'cn=patienten,ou=developers,dc=isala,dc=local':
                                    if ($args[1] == 'cn=Peter,ou=developers,dc=isala,dc=local') return true;
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
                        case 'add_inetOrgPerson':
                            if ($args[0] == 'Peter' && $args[1] == 'Patient' && $args[2] == '111111111' && $args[3] == 'cn=patienten,ou=patienten,dc=isala,dc=local' && $args[4] == 'peter') return true;
                            return false;

                        case 'add_account':
                            if ($args[0] == 'account1' && $args[1] == 'cn=administrators,ou=administrators,ou=ccc,ou=isala,dc=isala,dc=local' && $args[2] == 'account') return true;
                            return false;
                    }
                })
            );

        $model = $this->getMockBuilder(CreateUserModel::class)
            ->getMock();

        $model->method('getLDAP')
            ->willReturn($ldap);

        /* ----- Done configuring Mocks ----- */

        if ($model->getLDAP()->getConnection()) {
            if (!$model->getLDAP()->query('bind', [NULL, NULL])) { //NULL, NULL = anonymous bind
                return false;
            }
            // Check if User already Exists
            if (
                $model->getLDAP()->query('uidExists', [$uid, "inetOrgPerson"])
                || $model->getLDAP()->query('uidExists', [$uid, "account"])
            ) return false; // TODO: display or return error message

            // Get Group DN
            $group_dn = $model->getLDAP()->query('getGroupDNByName', [$group]); // Location of the group in LDAP Directory

            // Attempt to add user
            if (strlen($firstname) < 1) {
                if (strlen($lastname) < 1) {
                    // Attempt to add user as account
                    if (!$model->getLDAP()->query('add_account', [$uid, $group_dn, $password])) return false;
                } else {
                    // insufficient data given
                    return false;
                }
            } else if (strlen($lastname) < 1) {
                // insufficient data given
                return false;
            } else {
                // Attempt to add user as inetOrgPerson
                if (!$model->getLDAP()->query('add_inetOrgPerson', [$firstname, $lastname, $uid, $group_dn, $password])) return false;
            }

            return true;
        } else {
            die('Connection to LDAP service failed');
        }
        return false;
    }
}

class CreateUserModel
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
