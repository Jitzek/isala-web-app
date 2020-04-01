<?php

require_once('../app/core/Controller.php');
require_once("../app/logging/logger.php");

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
                    logger::log($uid, 'Login successful', $this->model);
                    header("Location: /public/login/twofactor/" . $token);
                    die();
                } else {
                    if ($this->err_msg == '') {
                        logger::log($uid, 'Attempt to login failed', $this->model);
                        echo "<p style=\"color: #FC240F\">UserID or Password was incorrect</p>";
                    }
                    else  {
                        logger::log($uid, $this->err_msg, $this->model);
                        echo "<p style=\"color: #FC240F\">" . htmlentities($this->err_msg) . "</p>";
                    }
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
                    die();
                }

                if ($this->attemptAuthenticate($_SESSION['uid'], $_POST['2fa_code'])) {
                    // Remove authorization token
                    unset($_SESSION['auth_token']);

                    $user_dn = $this->model->getLDAP()->query('getDnByUid', [$_SESSION['uid']]);
                    $group = $this->model->getLDAP()->query('getGroupOfUser', [$user_dn]);
                    $table = $this->model->getDB()->query('convertGroupToTable', [$group]);


                    // Finish logging in
                    if($table == "Gecontracteerd") {
                        header("Location: /public/home");
                    } else if($this->model->getDB()->query('isUpdateLastPasswordChangeEmpty', [$_SESSION['uid']]) !== NULL) {
                        header("Location: /public/home");
                    } else {
                        header("Location: /public/changepassword");
                    }

                    die();
                } else {
                    if (!$this->err_msg) {
                        echo "<p style=\"color: #FC240F\">Something went wrong</p>";
                    } else {
                        echo "<p style=\"color: #FC240F\">" . $this->err_msg . "</p>";
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
        if (!$this->model->getLDAP()->query('bind', [NULL, NULL])) {
            $this->err_msg = 'Ldap binding failed';
            return false;
        }

        // Check if User Exists
        if (
            !$this->model->getLDAP()->query('uidExists', [$uid, "inetOrgPerson"])
            && !$this->model->getLDAP()->query('uidExists', [$uid, "account"])
        ) {
            return false;
        }

        // Get User's DN
        $ldap_user_dn = $this->model->getLDAP()->query('getDnByUid', [$uid]);

        // Get Blocked IPs
        $blocked_ip_arr = $this->model->getDB()->query('blockedIPArray', [$uid]);
        if (count($blocked_ip_arr) > 0) {
            foreach ($blocked_ip_arr as $blocked_ip) {
                // If IP is blocked
                if ($blocked_ip == $this->getUserIP()) {
                    // Check if block has expired
                    if (!$this->model->getDB()->query('blockExpired', [$uid, $blocked_ip])) {
                        $this->err_msg = 'Account geblokkeerd voor het overschrijden van het aantal inlogpogingen, probeer het later weer opnieuw';
                        return false;
                    }
                }
            }
        }

        // Bind to LDAP with this user (check password)
        if (!$this->model->getLDAP()->query('bind', [$ldap_user_dn, $passwd])) {
            $this->model->getDB()->query('failedLoginAttempt', [$uid, $this->getUserIP()]);
            return false;
        }

        // Reset login attemps (but not blocked time penalty)
        $this->model->getDB()->query('succesfulLoginAttempt', [$uid, $this->getUserIP()]);

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

        // Get Blocked IPs
        $blocked_ip_arr = $this->model->getDB()->query('blockedIPArray', [$uid]);
        if (count($blocked_ip_arr) > 0) {
            foreach ($blocked_ip_arr as $blocked_ip) {
                // If IP is blocked
                if ($blocked_ip == $this->getUserIP()) {
                    // Check if block has expired
                    if (!$this->model->getDB()->query('blockExpired', [$uid, $blocked_ip])) {
                        $this->err_msg = 'Account geblokkeerd voor het overschrijden van het aantal inlogpogingen, probeer het later weer opnieuw';
                        return false;
                    }
                }
            }
        }

        $table = $this->model->getDB()->query('convertGroupToTable', [$this->model->getLDAP()->query('getGroupOfUid', [$uid])]);

        // Check if given 2FA is correct
        if ($this->model->getDB()->query('get2FA', [$uid, $table]) != $tfa) {
            $this->err_msg = 'Incorrect 2FA code';
            $this->model->getDB()->query('failedLoginAttempt', [$uid, $this->getUserIP()]);
            return false;
        }

        // Remove Block entry from database
        $this->model->getDB()->query('succesfulTwoFactor', [$uid, $this->getUserIP()]);

        return true;
    }

    private function getUserIP()
    {
        return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
    }

    // Below function is insecure due to the possibility of contacting untrusted proxies
    // Only use when trusted proxies have been implemented
    /*private function getUserIP(){
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip);
    
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false){
                        return $ip;
                    }
                }
            }
        }
    }*/
}
