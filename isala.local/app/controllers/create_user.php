<?php

require_once('../app/core/Controller.php');

class create_user extends Controller
{
    private $model;
    private $err_msg = '';
    public function index()
    {
        // Define Model to be used
        $this->model = $this->model('CreateUserModel');

        //$this->attemptUserCreation($uid, $firstname, $lastname, $password);


        if (!$_SESSION['uid']) {
            return header("Location: /public/login");
        }

        // Define Model to be used
        //$model = $this->model('CreateUserModel');
        $user = $this->model('UserModel');

        // Parse data to view (beware of order)
        $this->view('includes/head');
        $this->view('includes/navbar', ['name' => $user->getName()]);
        $this->view('createuser/index', ['title' => $this->model->getTitle(), 'name' => $user->getName(), 'group' => $user->getGroup()]);
        $this->view('includes/footer');

        if($_POST['uid']&&$_POST['voornaam']&&$_POST['sn']&&$_POST['passwd']) {
            if ($_POST["create_user"]) {
                $this->attemptUserCreation($_POST['uid'], $_POST['voornaam'], $_POST['sn'], $_POST['passwd']);
            }
        } else {
            if ($this->err_msg == '') {
                echo "<div id=\"accountinput\" >";
                echo "<p style=\"color: #0000ff\">Vul alle velden in om een gebruiker toe te voegen.</p>";
                echo "</div>";
            } else echo "<p style=\"color: #0000ff\">" . htmlentities($this->err_msg) . "</p>";
        }
    }

    protected function attemptUserCreation($uid, $firstname, $lastname, $password)
    {
        if($this->model->getLDAP()->getConnection()) {

            //$ldap_group_dn = $this->model->getLDAP()->query('getGroupDNByName', ['patienten']);
            $ds = $this->model->getLDAP()->getConnection();
            $r = $this->model->getLDAP()->query('bind', ["cn=admin,dc=isala,dc=local", "isaladebian"]); //NULL, NULL = anonymous bind
            if (!$r) {
                if ($this->err_msg == '') {
                    echo "<div id=\"accountinput\" >";
                    echo "<p style=\"color: #FC240F\">Er kan geen verbinding worden gemaakt.</p>";
                    echo "</div>";
                } else echo "<p style=\"color: #FC240F\">" . htmlentities($this->err_msg) . "</p>";

                return false;
            }

            $dn = 'cn='.$firstname." ".$lastname.',ou=patienten,dc=isala,dc=local';

            //check if user already exists
            if ($this->model->getLDAP()->query('uidExists', [$uid, "inetOrgPerson"])){
                if ($this->err_msg == '') {
                    echo "<div id=\"accountinput\" >";
                    echo "<p style=\"color: #FC240F\">Deze gebruiker bestaat al.</p>";
                    echo "</div>";
                } else echo "<p style=\"color: #FC240F\">" . htmlentities($this->err_msg) . "</p>";

                return false;
            }

            $info["cn"] = $firstname." ".$lastname;
            $info['objectclass'][0] = "inetOrgPerson";
            $info['objectclass'][1] = "organizationalPerson";
            $info['objectclass'][1] = "person";
            $info['objectclass'][2] = "top";
            $info["sn"] = ldap_escape($lastname, '', LDAP_ESCAPE_FILTER);
            $info["givenName"] = ldap_escape($uid, '', LDAP_ESCAPE_FILTER);
            $info["uid"] = ldap_escape($uid, '', LDAP_ESCAPE_FILTER);
            //$info["userPassword"] = $password;
            $salt = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+?><":}{|-=[];,./', 6)), 0, 6);
            $info["userPassword"] = '{SSHA}' . base64_encode(sha1($password.$salt, true) . $salt);


            //$dn = "cn=patienten,ou=patienten,dc=isala,dc=local";

            $r = ldap_add($ds, $dn, $info);

            //$r = ldap_mod_add($ds,$dn,$info);
            if ($r)
            {
                if ($this->err_msg == '') {
                    echo "<div id=\"accountinput\" >";
                    echo "<p style=\"color: #008000\">De gebruiker is succesvol aangemaakt.</p>";
                    echo "</div>";
                } else echo "<p style=\"color: #008000\">" . htmlentities($this->err_msg) . "</p>";

                return false;
            }
            else
            {
                if ($this->err_msg == '') {
                    echo "<div id=\"accountinput\" >";
                    echo "<p style=\"color: #FC240F\">Er kan geen verbinding gemaakt worden.</p>";
                    echo "</div>";
                } else echo "<p style=\"color: #FC240F\">" . htmlentities($this->err_msg) . "</p>";

                return false;
            }

            ldap_close($ds);
        } else {
            if ($this->err_msg == '') {
                echo "<div id=\"accountinput\" >";
                echo "<p style=\"color: #FC240F\">Connectie met LDAP service is mislukt.</p>";
                echo "</div>";
            } else echo "<p style=\"color: #FC240F\">" . htmlentities($this->err_msg) . "</p>";

            return false;
        }
    }
}
