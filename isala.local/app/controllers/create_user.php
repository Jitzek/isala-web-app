<?php

require_once('../app/core/Controller.php');

class create_user extends Controller
{
    private $model;
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

        if ($_POST["create_user"]) {
            $this->attemptUserCreation($_POST['uid'],$_POST['voornaam'],$_POST['sn'], $_POST['passwd']);
        }
    }

    protected function attemptUserCreation($uid, $firstname, $lastname, $password)
    {
        if($this->model->getLDAP()->getConnection()) {

            //$ldap_group_dn = $this->model->getLDAP()->query('getGroupDNByName', ['patienten']);
            $ds = $this->model->getLDAP()->getConnection();
            $r = $this->model->getLDAP()->query('bind', ["cn=admin,dc=isala,dc=local", "isaladebian"]); //NULL, NULL = anonymous bind
            if (!$r) {
                echo "<div id=\"accountinput\">";
                die('Kan geen verbinding maken');
                echo "</div>";
                return false;
            }

            $dn = 'cn='.$firstname." ".$lastname.',ou=patienten,dc=isala,dc=local';

            //check if user already exists
            if ($this->model->getLDAP()->query('uidExists', [$uid, "inetOrgPerson"])){
                echo "<div id=\"accountinput\">";
                die('Deze gebruikersnaam bestaat al.');
                echo "</div>";
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
                echo "<div id=\"accountinput\">";
                die('De gebruiker is succesvol aangemaakt.');
                echo "</div>";
                //echo 'Success';
            }
            else
            {
                echo "<div id=\"accountinput\">";
                die('Er is iets fout gegaan.');
                echo "</div>";
            }

            ldap_close($ds);
        } else {
            echo "<div id=\"accountinput\">";
            die('Connection to LDAP service failed.');
            echo "</div>";
        }
    }
}
