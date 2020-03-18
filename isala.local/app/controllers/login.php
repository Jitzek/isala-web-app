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
        $this->view('login/index', ['title' => $this->model->getTitle(), '2fa' => false]);

        // Handle Post Request (login)
        if ($_POST["login"]) {
            if ($_POST['uid'] && $_POST['passwd']) {
                $uid = $_POST['uid'];
                if ($this->attemptLogin($_POST['uid'], $_POST['passwd'])) {
                    // Generate authentication token
                    $token = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 12)), 0, 12);
                    $_SESSION['auth_token'] = $token;
                    
                    $table = $this->model->getDB()->query('convertGroupToTable', [$this->model->getLDAP()->query('getGroupOfUid', [$uid])]);

                    // Generate 2FA Code
                    $this->model->getDB()->query('set2FA', [$uid, $table]);

                    // Redirect user to two factor authentication
                    header("Location: /public/login/twofactor/" . $token);
                } else {
                    if ($this->err_msg == '') echo "<p style=\"color: #FC240F\">UserID or Password was incorrect</p>";
                    else echo "<p style=\"color: #FC240F\">" . htmlentities($this->err_msg) . "</p>";
                }
            } else {
                echo "<p style=\"color: #FC240F\">Please Fill in all Fields</p>";
            }
        }
    }

    public function twofactor($auth_token)
    {
        // Check if user is authorized to be here
        if (!$_SESSION['auth_token'] || $_SESSION['auth_token'] != $auth_token) {
            header("Location: /public/home");
        }
        
        // Define Model to be used
        $this->model = $this->model('LoginModel');

        // Parse data to view
        $this->view('login/index', ['title' => $this->model->getTitle(), '2fa' => true]);

        // Check for Post
        if ($_POST['2fa_submit']) {
            if (strlen($_POST['2fa_code']) == 6) {
                // Check if user is authorized to be here
                if (!$_SESSION['auth_token'] || $_SESSION['auth_token'] != $auth_token) {
                    header("Location: /public/home");
                }

                $uid = $_SESSION['uid'];
                if ($this->attemptAuthenticate($uid, $_POST['2fa_code'])) {
                    // Remove authorization token
                    unset($_SESSION['auth_token']);

                    // Create session token
                    $token = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 12)), 0, 12);
                    $_SESSION['token'] = $token;
                    $table = $this->model->getDB()->query('convertGroupToTable', [$this->model->getLDAP()->query('getGroupOfUid', [$uid])]);
                    $this->model->getDB()->query('setToken', [$_SESSION['uid'], $table, $token]);

                    // Finish logging in
                    header("Location: /public/home");
                } else {
                    if (!$this->err_msg) {
                        echo "<p style=\"color: #FC240F\">Something went wrong</p>";
                    } else {
                        echo "<p style=\"color: #FC240F\">". $this->err_msg . "</p>";
                    }
                }
            } else {
                echo "<p style=\"color: #FC240F\">Incorrect 2FA code</p>";
            }
        }
    }

    protected function attemptLogin($uid, $passwd)
    {
        // Check for LDAP Connection
        if (!$this->model->getLDAP()->getConnection()) {
            $this->err_msg = 'Connection Failed';
            return false;
        }
        if (!$this->model->getDB()->getConnection()) {
            $this->err_msg = 'Connection Failed';
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

        // Check if account is locked
        $table = $this->model->getDB()->query('convertGroupToTable', [$this->model->getLDAP()->query('getGroupOfUid', [$uid])]);
        if ($this->model->getDB()->query('userIsLocked', [$uid, $table])) {
            $this->err_msg = 'Account Locked';
            return false;
        }

        // Bind to LDAP with this user (check password)
        if (!$this->model->getLDAP()->query('bind', [$ldap_user_dn, $passwd])) return false;

        $_SESSION['uid'] = $uid;

        return true;
    }

    protected function attemptAuthenticate($uid, $tfa)
    {
        // Check for LDAP Connection
        if (!$this->model->getLDAP()->getConnection()) {
            $this->err_msg = 'Connection Failed';
            return false;
        }
        if (!$this->model->getDB()->getConnection()) {
            $this->err_msg = 'Connection Failed';
            return false;
        }
        if (!$this->model->getLDAP()->query('bind', [NULL, NULL])) return false;

        // Check if User Exists
        if (
            !$this->model->getLDAP()->query('uidExists', [$uid, "inetOrgPerson"])
            && !$this->model->getLDAP()->query('uidExists', [$uid, "account"])
        ) return false;

        $table = $this->model->getDB()->query('convertGroupToTable', [$this->model->getLDAP()->query('getGroupOfUid', [$uid])]);

        if ($this->model->getDB()->query('get2FA', [$uid, $table]) != $tfa) {
            $this->err_msg = 'Incorrect 2FA code';
            return false;
        }

        return true;
    }
}
