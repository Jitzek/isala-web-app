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
    public function attemptLogin_succesful() {
        $uid = 'elzenknopje';
        $passwd = 'idebian';
        $this->assertTrue($this->attemptLogin($uid, $passwd));
    }

    /** @test */
    public function attemptLogin_wrongUser() {
        $uid = 'wronguser';
        $passwd = 'idebian';
        $this->assertFalse($this->attemptLogin($uid, $passwd));
    }

    /** @test */
    public function attemptLogin_wrongPasword_test() {
        $uid = 'elzenknopje';
        $passwd = 'wrongpassword';
        $this->assertFalse($this->attemptLogin($uid, $passwd));
    }

    /** @test */
    public function attemptLogin_wrongUserAndWrongPasword_test() {
        $uid = 'wronguser';
        $passwd = 'wrongpassword';
        $this->assertFalse($this->attemptLogin($uid, $passwd));
    }
    
    public function attemptLogin($uid, $passwd)
    {
        // Create a stub for the Controller class.
        $ldap = $this->getMockBuilder(LDAPConnection::class)
            ->getMock();
        
        // Configure the stub.
        $ldap->method('getConnection')
        ->willReturn(true);

        // Configure the stub.
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
                        if ($args[0] == 'ou=developers,dc=isala,dc=local' && $args[1] == 'elzenknopje') {
                            return true;
                        } else {
                            return false;
                        }
                        return 'cn=Elzen Knop,ou=developers,dc=isala,dc=local';
                    case 'userInGroup':
                        return true;
                }
            })
        );

        // Create a stub for the Controller class.
        $model = $this->getMockBuilder(LoginModel::class)
            ->getMock();
        
        // Configure the stub.
        $model->method('getLDAP')
        ->willReturn($ldap);

        

        if ($model->getLDAP()->getConnection()) {
            /**
             * Example login
             */
            $ldapbind = $model->getLDAP()->query('bind', [NULL, NULL]); //NULL, NULL = anonymous bind
            if (!$ldapbind) {
                return false;
            }

            $ldap_dn_users = "ou=developers,dc=isala,dc=local"; // Location of the user in LDAP Directory

            // Check if User Exists
            if (!$model->getLDAP()->query('uidExists', [$ldap_dn_users, $uid, "inetOrgPerson"])) return false;

            // Get User's DN
            $ldap_user_dn = $model->getLDAP()->query('getDnByUid', [$ldap_dn_users, $uid]);

            // Check if User is in Group
            $ldap_group_dn = "cn=developers,ou=developers,dc=isala,dc=local"; // Location of the group in LDAP Directory
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

class LoginModel {
    public function getLDAP() {

    }
}

class LDAPConnection {
    public function getConnection() {

    }

    public function query() {

    }
}

