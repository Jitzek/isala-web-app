<?php

require_once('../app/core/Controller.php');

class ChangePassword extends Controller
{
    private $model;
    private $err_msg;
    public function index($uid) // TODO: don't transfer password over URL
    {
        // Define Model to be used
        $this->model = $this->model('ChangePasswordModel');

        if ($_POST['change_password']) {
            if ($this->validatePasswordTest($_POST['prev_password'], $_POST['new_password'])) {
                $this->attemptPasswordChange($uid, $_POST['prev_password'], $_POST['new_password']);
            } else {
                echo "<p style=\"color: #FC240F\">" .  htmlentities($this->err_msg) . "</p>";
            }
        }
    }

    protected function attemptPasswordChange($uid, $prev_password, $new_password)
    {
        if ($this->model->getLDAP()->getConnection()) {
            if (!$this->model->getLDAP()->query('bind', [NULL, NULL])) return false; //NULL, NULL = anonymous bind

            // Check if User Exists
            if (
                !$this->model->getLDAP()->query('uidExists', [$uid, "inetOrgPerson"])
                && !$this->model->getLDAP()->query('uidExists', [$uid, "account"])
            ) return false;

            // Get user DN
            $user_dn = $this->model->getLDAP()->query('getDnByUid', [$uid]);

            // Check if user is valid and/or given password is correct
            if (!$this->model->getLDAP()->query('bind', [$user_dn, $prev_password])) return false;

            // Change password
            if (!$this->model->getLDAP()->query('changeUserPassword', [$user_dn, $new_password])) return false;

            return true;
        } else {
            die('Connection to LDAP service failed');
        }
    }

    protected function validatePasswordTest($prev_password, $new_password)
    {
        // Check if passwords are the same 
        if ($prev_password == $new_password) {
            $this->err_msg = 'New Password can\'t be Previous Password';
            return false;
        }

        // Check if new password complies to the given requirements

        // Length of password should be 8 characters or longer
        if (strlen($new_password) < 8) {
            $this->err_msg = 'New Password needs a minimum length of 8 characters';
            return false;
        }

        // Password should contain atleast one capital letter
        if (!$this->isPartUppercase($new_password)) {
            $this->err_msg = 'New Password should contain atleast one capital letter';
            return false;
        }

        // Password should contain atleast one special character
        if (!$this->hasSpecialCharacter($new_password)) {
            $this->err_msg = 'New Password should contain atleast one special character';
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
