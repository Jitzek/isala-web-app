<?php

require_once('../app/core/Controller.php');
require_once('../app/interfaces/Authentication.php');
require_once("../app/logging/logger.php");

class ChangePassword extends Controller implements Authentication
{
    private $model;
    private $err_msg;
    private $logModel;

    public function index() // TODO: don't transfer password over URL
    {
        if (!$this->authenticate()) {
            return header("Location: /public/logout");
            die();
        }
        // Define Model to be used
        $this->model = $this->model('ChangePasswordModel');

        // Define logging model
        $this->logModel = $this->model('LoggingModel');


        // Parse data to view
        $this->view('changepassword/index', ['title' => $this->model->getTitle()]);

        if ($_POST['change_password']) {
            if (!$_POST['prev_password'] || !$_POST['new_password'] || !$_POST['new_password2']) {
                logger::log($_SESSION['uid'], 'Fields left empty when changing password', $this->logModel);
                echo "<p style=\"color: #FC240F\">Please fill in all fields</p>";
                return;
            }
            if ($this->validatePassword($_POST['new_password'], $_POST['new_password2'])) {
                if ($this->attemptPasswordChange($_SESSION['uid'], $_POST['prev_password'], $_POST['new_password'])) {
                    header("Location: /public/home");
                    exit();
                }
                if (strlen($this->err_msg) < 1) {
                    logger::log($_SESSION['uid'], 'Password change failed', $this->logModel);
                    echo "<p style=\"color: #FC240F\">Something went wrong</p>";
                }
                else {
                    logger::log($_SESSION['uid'], $this->err_msg, $this->logModel);
                    echo "<p style=\"color: #FC240F\">" .  htmlentities($this->err_msg) . "</p>";
                }
            } else {
                logger::log($_SESSION['uid'], $this->err_msg, $this->logModel);
                echo "<p style=\"color: #FC240F\">" .  htmlentities($this->err_msg) . "</p>";
            }
        }
    }

    public function authenticate()
    {
        // Require Session variables
        if (!$_SESSION['uid']) {
            return false;
        }
        return true;
    }

    protected function attemptPasswordChange($uid, $prev_password, $new_password)
    {
        $user_dn = $this->model->getLDAP()->query('getDnByUid', [$uid]);
        // Check if prev_password is correct
        if (!$this->model->getLDAP()->query('bind', [$user_dn, $prev_password])) {
            $this->err_msg = 'Password is incorrect';
            return false;
        }
        // Check if passwords are the same 
        if ($prev_password == $new_password) {
            $this->err_msg = 'New Password can\'t be the same as the old Password';
            return false;
        }

        if (!$this->model->getLDAP()->getConnection()) {
            $this->err_msg = 'Connection Failed';
            return false;
        }
        if (!$this->model->getDB()->getConnection()) {
            $this->err_msg = 'Connection Failed';
            return false;
        }

        if (!$this->model->getLDAP()->query('bind', [NULL, NULL])) return false; //NULL, NULL = anonymous bind

        // Check if User Exists
        if (
            !$this->model->getLDAP()->query('uidExists', [$uid, "inetOrgPerson"])
            && !$this->model->getLDAP()->query('uidExists', [$uid, "account"])
        ) return false;

        // Get user DN
        $user_dn = $this->model->getLDAP()->query('getDnByUid', [$uid]);

        // Check if user is valid and/or given password is correct
        if (!$this->model->getLDAP()->query('bind', [$user_dn, $prev_password])) {
            $this->err_msg = 'Incorrect Password';
            return false;
        }

        // Change password
        if (!$this->model->getLDAP()->query('changeUserPassword', [$user_dn, $new_password])) return false;

        // Edit Last Password Change column in Database
        $group = $this->model->getLDAP()->query('getGroupOfUser', [$user_dn]);
        $table = $this->model->getDB()->query('convertGroupToTable', [$group]);
        $this->model->getDB()->query('updateLastPasswordChange', [$uid, $table]);

        logger::log($_SESSION['uid'], 'Password changed', $this->logModel);

        return true;
    }

    private function validatePassword($new_password, $new_password_validation)
    {
        if ($new_password != $new_password_validation) {
            $this->err_msg = 'The two new passwords don\'t match';
            return false;
        }
        // Length of password should be 8 characters or longer
        if (strlen($new_password) < 8) {
            $this->err_msg = 'New Password needs to be at least 8 characters long';
            return false;
        }

        // Password should contain atleast one capital letter
        if (!$this->isPartUppercase($new_password)) {
            $this->err_msg = 'New Password needs to contain atleast one capital letter';
            return false;
        }

        // Password should contain atleast one special character
        if (!$this->hasSpecialCharacter($new_password)) {
            $this->err_msg = 'New Password needs to contain atleast one special character';
            return false;
        }

        return true;
    }

    private function isPartUppercase($string)
    {
        return (bool) preg_match('/[A-Z]/', $string);
    }

    private function hasSpecialCharacter($string)
    {
        return (bool) preg_match('/[\'^£$%&*()}{@#~?><>,!|=_+¬-]/', $string);
    }
}
