<?php

require_once('../app/core/Controller.php');

class ChangePassword extends Controller
{
    private $model;
    private $err_msg;
    public function index() // TODO: don't transfer password over URL
    {
        /*if (!$_SESSION['uid']) {
            return header("Location: /public/login");
        }*/
        // Define Model to be used
        $this->model = $this->model('ChangePasswordModel');

        // Parse data to view
        $this->view('changepassword/index', ['title' => $this->model->getTitle()]);

        if ($_POST['change_password']) {
            if ($this->validatePassword($_POST['prev_password'], $_POST['new_password'])) {
                if (!$this->attemptPasswordChange($_SESSION['uid'], $_POST['prev_password'], $_POST['new_password'])) {
                    if (strlen($this->err_msg) < 1) echo "<p style=\"color: #FC240F\">Something went wrong</p>";
                    else echo "<p style=\"color: #FC240F\">" .  htmlentities($this->err_msg) . "</p>";
                }
            } else {
                echo "<p style=\"color: #FC240F\">" .  htmlentities($this->err_msg) . "</p>";
            }
        }
    }

    protected function attemptPasswordChange($uid, $prev_password, $new_password)
    {
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
        $group = $this->model->getLDAP()->query('getGroupOfUser', [$uid]);
        $table = $this->convertGroupToTable($group);
        $this->model->getDB()->query('updateLastPasswordChange', [$uid, $table]);

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

    private function validatePassword($prev_password, $new_password)
    {
        // Check if passwords are the same 
        if ($prev_password == $new_password) {
            $this->err_msg = 'New Password can\'t be the same as the old Password';
            return false;
        }

        // Check if new password complies to the given requirements

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
        return (bool) preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string);
    }
}
