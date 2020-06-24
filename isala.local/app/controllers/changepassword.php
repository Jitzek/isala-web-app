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
        // Authenticate user
        if (!$this->authenticate()) {
            return header("Location: /public/logout");
            die();
        }

        // Define Model to be used
        $this->model = $this->model('ChangePasswordModel');

        // Define logging model
        $this->logModel = $this->model('LoggingModel');

        // If request for password change has been send
        if (isset($_POST['change_password'])) {
            // Validate user input
            if ($this->validateUserInput()) {
                // Attempt to change password, redirect user on successfull attempt
                if ($this->attemptPasswordChange($_SESSION['uid'], $_POST['prev_password'], $_POST['new_password'])) {
                    header("Location: /public/home");
                    exit();
                }
            }
        }

        // Parse data to view
        $this->view('changepassword/index', ['title' => $this->model->getTitle(), 'err_msg' => $this->err_msg]);
        $this->view('includes/footer');
    }

    public function authenticate()
    {
        // Require Session variables
        if (!$_SESSION['uid']) {
            return false;
        }
        return true;
    }

    protected function validateUserInput()
    {
        // If required fields werent filled in
        if (!$_POST['prev_password'] || !$_POST['new_password'] || !$_POST['new_password2']) {
            logger::log($_SESSION['uid'], 'Fields left empty when changing password', $this->logModel);
            $this->err_msg = 'Niet alle velden zijn ingevuld';
            //echo "<p style=\"color: #FC240F\">Please fill in all fields</p>";
            return false;
        }

        // Validate given password
        if (!$this->validatePassword($_POST['new_password'], $_POST['new_password2'])) {
            logger::log($_SESSION['uid'], $this->err_msg, $this->logModel);
            return false;
        }

        return true;
    }

    protected function attemptPasswordChange($uid, $prev_password, $new_password)
    {
        // Check for connection with LDAP and Database
        if (!$this->model->getLDAP()->getConnection()) {
            $this->err_msg = 'Verbinding mislukt';
            return false;
        }
        if (!$this->model->getDB()->getConnection()) {
            $this->err_msg = 'Verbinding mislukt';
            return false;
        }

        // Check if User Exists
        if (
            !$this->model->getLDAP()->query('uidExists', [$uid, "inetOrgPerson"])
            && !$this->model->getLDAP()->query('uidExists', [$uid, "account"])
        ) return false;

        // Get user DN
        $user_dn = $this->model->getLDAP()->query('getDnByUid', [$uid]);

        // Check if prev_password is correct
        if (!$this->model->getLDAP()->query('bind', [$user_dn, $prev_password])) {
            $this->err_msg = 'Gegeven wachtwoord is incorrect';
            return false;
        }
        // Check if passwords are the same 
        if ($prev_password == $new_password) {
            $this->err_msg = 'Het nieuwe wachtwoord kan niet gelijk zijn aan het oude wachtwoord';
            return false;
        }

        if (!$this->model->getLDAP()->query('bind', [NULL, NULL])) return false; //NULL, NULL = anonymous bind

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
            $this->err_msg = 'The twee nieuwe wachtwoorden komen niet overeen';
            return false;
        }
        // Length of password should be 8 characters or longer
        if (strlen($new_password) < 8) {
            $this->err_msg = 'Het nieuwe wachtwoord moet minstens 8 karakters lang zijn';
            return false;
        }

        // Length of password should be 999 characters or less
        if (strlen($new_password) > 999) {
            $this->err_msg = 'Het nieuwe wachtwoord mag maximaal 999 karakters lang zijn';
            return false;
        }

        // Password should contain atleast one capital letter
        if (!(bool) preg_match('/[A-Z]/', $new_password)) {
            $this->err_msg = 'Het nieuwe wachtwoord moet minstens één hoofdletter bevatten';
            return false;
        }

        // Password should contain atleast one special character
        if (!(bool) preg_match('/[\'^£$%&*()}{@#~?><>,!|=_+¬-]/', $new_password)) {
            $this->err_msg = 'Het nieuwe wachtwoord moet minstens één speciaal karakter bevatten';
            return false;
        }

        return true;
    }
}
