<?php

require_once('../app/core/Controller.php');

class Login extends Controller
{
    private $model;
    public function index()
    {
        // Define Model to be used
        $this->model = $this->model('LoginModel');

        // Parse data to view
        $this->view('login/index', ['title' => $this->model->getTitle()]);

        // Handle Post Request (login)
        if ($_POST["login"]) {
            if ($_POST['uid'] && $_POST['passwd'] && $_POST['group']) {
                if ($this->attemptLogin($_POST['uid'], $_POST['passwd'], $_POST['group'])) {
                    header("Location: /public/home");
                } else {
                    echo "<p style=\"color: #FC240F\">UserID or Password was incorrect</p>";
                }
            } else {
                echo "<p style=\"color: #FC240F\">Please Fill in all Fields</p>";
            }
        }
    }

    protected function attemptLogin($uid, $passwd, $group)
    {
        if ($this->model->getLDAP()->getConnection()) {
            $ldapbind = $this->model->getLDAP()->query('bind', [NULL, NULL]); //NULL, NULL = anonymous bind
            if (!$ldapbind) {
                return false;
            }
            // Check if User Exists
            if (!$this->model->getLDAP()->query('uidExists', [$uid, "inetOrgPerson"])
                && !$this->model->getLDAP()->query('uidExists', [$uid, "account"])) return false;
            
            // Get User's DN
            $ldap_user_dn = $this->model->getLDAP()->query('getDnByUid', [$uid]);

            // Check if User is in Group
            if ($group == 'anders') {
                $possible_groups = ["dietisten", "psychologen", "fysiotherapeuten", "administrators"];
                foreach ($possible_groups as $possible_group) {
                    $ldap_group_dn = $this->model->getLDAP()->query('getGroupDNByName', [$possible_group]); // Location of the group in LDAP Directory
                    if ($this->model->getLDAP()->query('userInGroup', [$ldap_group_dn, $ldap_user_dn, "groupOfNames", "member"])) break;
                    $ldap_group_dn = '';
                }
                if ($ldap_group_dn == '') return false;
            } else {
                $ldap_group_dn = $this->model->getLDAP()->query('getGroupDNByName', [$group]); // Location of the group in LDAP Directory
                if (!$this->model->getLDAP()->query('userInGroup', [$ldap_group_dn, $ldap_user_dn, "groupOfNames", "member"])) return false;
            }

            // Bind to LDAP with this user
            $ldapbind = $this->model->getLDAP()->query('bind', [$ldap_user_dn, $passwd]);
            if (!$ldapbind) {
                return false;
            }

            $_SESSION['uid'] = $uid;
            return true;
        } else {
            die('Connection to LDAP service failed');
        }
    }
}
