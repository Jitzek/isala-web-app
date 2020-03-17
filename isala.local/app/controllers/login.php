<?php

require_once('../app/core/Controller.php');

class Login extends Controller
{
    private $model;
    private $err_msg = '';
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
                    if ($this->err_msg == '') echo "<p style=\"color: #FC240F\">UserID or Password was incorrect</p>";
                    else echo "<p style=\"color: #FC240F\">" . htmlentities($this->err_msg) . "</p>";
                }
            } else {
                echo "<p style=\"color: #FC240F\">Please Fill in all Fields</p>";
            }
        }
    }

    protected function attemptLogin($uid, $passwd, $group)
    {
        // Check for LDAP Connection
        if (!$this->model->getLDAP()->getConnection()) {
            $this->err_msg = 'Connection failed';
            return false;
        }
        if (!$this->model->getDB()->getConnection()) {
            $this->err_msg = 'Connection failed';
            return false;
        }

        if (!$this->model->getLDAP()->query('bind', [NULL, NULL])) return false;
        // Check if User Exists
        if (
            !$this->model->getLDAP()->query('uidExists', [$uid, "inetOrgPerson"])
            && !$this->model->getLDAP()->query('uidExists', [$uid, "account"])
        ) return false;

        // Get User's DN
        $ldap_user_dn = $this->model->getLDAP()->query('getDnByUid', [$uid]);

        // Check if User is in Group
        if ($group == 'anders') {
            $possible_groups = ["dietisten", "psychologen", "fysiotherapeuten", "administrators"];
            foreach ($possible_groups as $possible_group) {
                $ldap_group_dn = $this->model->getLDAP()->query('getGroupDNByName', [$possible_group]); // Location of the group in LDAP Directory
                if ($this->model->getLDAP()->query('userInGroup', [$ldap_group_dn, $ldap_user_dn, "groupOfNames", "member"])) {
                    // Check if account is locked
                    $table = $this->getTableNameForPostedGroup($possible_group);
                    if (strlen($table) < 1) {
                        $this->err_msg = 'Something went wrong';
                        return false;
                    }
                    if ($this->model->getDB()->query('userIsLocked', [$uid, $table])) {
                        $this->err_msg = 'Account is Locked';
                        return false;
                    }
                    break;
                }
                $ldap_group_dn = '';
            }
            if ($ldap_group_dn == '') return false;
        } else {
            if ($group != 'patienten' && $group != 'dokters') return false;
            $ldap_group_dn = $this->model->getLDAP()->query('getGroupDNByName', [$group]); // Location of the group in LDAP Directory
            if (!$this->model->getLDAP()->query('userInGroup', [$ldap_group_dn, $ldap_user_dn, "groupOfNames", "member"])) return false;

            $table = $this->getTableNameForPostedGroup($group);
            // Check if account is locked
            if (strlen($table) < 1) {
                $this->err_msg = 'Something went wrong';
                return false;
            }
            if ($this->model->getDB()->query('userIsLocked', [$uid, $table])) {
                $this->err_msg = 'Account is Locked';
                return false;
            }
        }

        // Bind to LDAP with this user (check password)
        if (!$this->model->getLDAP()->query('bind', [$ldap_user_dn, $passwd])) return false;

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
