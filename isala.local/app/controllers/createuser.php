<?php

require_once('../app/core/Controller.php');
require_once("../app/logging/logger.php");
require_once('../app/interfaces/Authentication.php');
require_once('../app/interfaces/Authorization.php');


class createUser extends Controller implements Authentication, Authorization
{
    private $model;
    private $err_msg = '';
    private $succ_msg = '';
    private $logModel;

    public function index()
    {
        // Define logging model
        $this->logModel = $this->model('LoggingModel');

        // Authenticate User
        if (!$this->authenticate()) {
            return header("Location: /public/logout");
            die();
        }

        // Authorize User
        if (!$this->authorize([])) {
            return header("Location: /public/home");
            die();
        }

        // Define Model to be used
        $this->model = $this->model('CreateUserModel');

        // Define Model to be used
        $user = $this->model('UserModel', [$_SESSION['uid']]);

        // Remember previous entered data except for the password
        $prev_values = $_POST;
        unset($prev_values['passwd']);
        if (isset($_POST["create_user"])) {
            // Are fields left empty
            if ($_POST['uid'] && $_POST['cn'] && $_POST['sn'] && $_POST['passwd'] && $_POST['adres'] && $_POST['geboortedatum'] && $_POST['geslacht'] && $_POST['telefoonnummer']) {
                if ($this->validateInput($_POST['cn'], $_POST['sn'], $_POST['adres'], $_POST['geboortedatum'], $_POST['geslacht'], $_POST['telefoonnummer'], $_POST['uid'], $_POST['passwd'])) {
                    if ($this->attemptUserCreation($_POST['uid'], $_POST['cn'], $_POST['sn'], $_POST['passwd'])) {
                        if ($this->attemptUserDatabaseEntry($_POST['uid'], $_POST['adres'], $_POST['geboortedatum'], $_POST['geslacht'], $_POST['telefoonnummer'], $user->getUid())) {
                            unset($prev_values);
                        }
                    }
                } else {
                    logger::log($_SESSION['uid'], 'Input was invalid: ' . $this->err_msg, $this->logModel);
                }
            } else {
                logger::log($_SESSION['uid'], 'Fields left empty while creating user', $this->logModel);
                $this->err_msg = 'Vul alle velden in om een gebruiker toe te voegen.';
            }
        }

        // Parse data to view (beware of order)
        $this->view('includes/head');
        $this->view('includes/navbar', ['name' => $user->getFullName()]);
        $this->view('createuser/index', [
            'title' => $this->model->getTitle(), 'name' => $user->getFullName(),
            'group' => $user->getGroup(),
            'err_msg' => $this->err_msg,
            'succ_msg' => $this->succ_msg,
            'prev_values' => $prev_values
        ]);
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

    public function authorize($args)
    {
        // User has to be a Dokter
        if ($_SESSION['role'] != 'dokters') {
            return false;
        }
        return true;
    }

    private function validateInput($cn, $sn, $adres, $geboortedatum, $geslacht, $telefoonnummer, $bsn, $wachtwoord)
    {
        // Validate Voornaam
        /**
         *  String
         *  Max Length: 128
         */
        if (strlen($cn) > 128) {
            $this->err_msg = "Voornaam mag niet langer zijn dan 128 karakters";
            return false;
        }
        if ((bool) preg_match('/[^A-Za-zÄÖÜËÏäöüëïÿẞß ]/', $cn)) {
            $this->err_msg = "Voornaam mag alleen letters bevatten";
            return false;
        }

        // Validate Achternaam
        /**
         * String
         * Max Length: 128
         */
        if (strlen($sn) > 128) {
            $this->err_msg = "Achternaam mag niet langer zijn dan 128 karakters";
            return false;
        }
        if ((bool) preg_match('/[^A-Za-zÄÖÜËÏäöüëïÿẞß ]/', $sn)) {
            $this->err_msg = "Achternaam mag alleen letters bevatten";
            return false;
        }

        // Validate Adres
        /**
         * Varchar(128)
         * Max Length: 128
         */
        if (strlen($adres) > 128) {
            $this->err_msg = "Adres mag niet langer zijn dan 128 karakters";
            return false;
        }
        if ((bool) preg_match('/[^A-Za-z0-9ÄÖÜËÏäöüëïÿẞß,. ]/', $adres)) {
            $this->err_msg = "Adres bevat niet toegestane tekens";
            return false;
        }

        // Validate Geboortedatum
        /**
         *  Date: yyyy-mm-dd
         *  Max Length: 10
         *  Min Lenght: 10
         */
        $od = date('Y-m-d', strtotime($geboortedatum));
        if (strlen($geboortedatum) != 10 || $od != $geboortedatum) {
            $this->err_msg = "Gegeven GeboorteDatum is niet valide";
            return false;
        }
        $nd = date('Y-m-d', strtotime($od));
        //$nd = DateTime::createFromFormat('Y-m-d', strtotime($geboortedatum));
        if (date('Y-m-d') < $nd) {
            $this->err_msg = "GeboorteDatum mag niet in de toekomst zijn";
            return false;
        }

        // Validate Geslacht
        /**
         *  Varchar(32)
         *  Max Length: 32
         */
        if (strlen($geslacht) > 32) {
            $this->err_msg = "Geslacht mag niet groter zijn dan 32 karakters";
            return false;
        }
        if ((bool) preg_match('/[^A-Za-zÄÖÜËÏäöüëïÿẞß ]/', $geslacht)) {
            $this->err_msg = "Achternaam mag alleen letters bevatten";
            return false;
        }

        // Validate Telefoonnummer
        /**
         *  Varchar(32)
         *  Max Length: 32
         */
        if (strlen($telefoonnummer) > 32) {
            $this->err_msg = "Telefoonnumer mag niet groter zijn dan 32 karakters";
            return false;
        }
        if ((bool) preg_match('/[^0-9 \+]/', $telefoonnummer)) {
            $this->err_msg = "Telefoonnumer mag alleen nummers bevatten";
            return false;
        }

        // Validate BSN
        /**
         *  Varchar(128)
         *  Min Length: 9
         *  Max Length: 9
         */
        if (strlen($bsn) != 9) {
            $this->err_msg = "BSN moet 9 karakters lang zijn";
            return false;
        }
        if ((bool) preg_match('/[^0-9]/', $bsn)) {
            $this->err_msg = "BSN mag alleen nummers bevatten";
            return false;
        }

        // Validate Wachtwoord
        /**
         * Min Length: 8
         * Max length: 999
         * Needs to contain at least 1 capital letter
         * Needs to contain at least 1 special character
         */
        return $this->validatePassword($wachtwoord);
    }

    private function validatePassword($password)
    {
        // Length of password should be 8 characters or longer
        if (strlen($password) < 8) {
            $this->err_msg = 'Het wachtwoord moet minstens 8 karakters lang zijn';
            return false;
        }

        // Length of password should be no longer than 999 characters
        if (strlen($password) > 999) {
            $this->err_msg = 'Het wachtwoord mag maximaal 999 karakters lang zijn';
            return false;
        }

        // Password should contain atleast one capital letter
        if (!(bool) preg_match('/[A-Z]/', $password)) {
            $this->err_msg = 'Het wachtwoord moet minstens één hoofdletter bevatten';
            return false;
        }

        // Password should contain atleast one special character
        if (!(bool) preg_match('/[\'^£$%&*()}{@#~?><>,!|=_+¬-]/', $password)) {
            $this->err_msg = 'Het wachtwoord moet minstens één speciaal karakter bevatten';
            return false;
        }

        return true;
    }

    protected function attemptUserCreation($uid, $firstname, $lastname, $password)
    {
        // Establish if Ldap connection is possible
        $ds = $this->model->getLDAP()->getConnection();
        if ($ds) {
            $admin = apache_getenv("LDAP_ADMIN");
            $passwd = apache_getenv("LDAP_PASSWD");
            $r = $this->model->getLDAP()->query('bind', [$admin, $passwd]); //NULL, NULL = anonymous bind

            // Is Ldap query successful
            if (!$r) {
                $this->err_msg = 'Er kan geen verbinding worden gemaakt.';
                logger::log($_POST['uid'], 'LDAP - connection failed', $this->logModel);
                return false;
            }

            // Get distinguished name to get path
            $dn = 'cn=' . ldap_escape($firstname, LDAP_ESCAPE_DN) . ',ou=patienten,dc=isala,dc=local';

            //check if user already exists
            if ($this->model->getLDAP()->query('uidExists', [$uid, "inetOrgPerson"])) {
                $this->err_msg = 'Deze gebruiker bestaat al.';
                logger::log($_POST['uid'], 'LDAP - user already exists', $this->logModel);
                return false;
            }

            // Get information from form
            $info['objectclass'][0] = "inetOrgPerson";
            $info['objectclass'][1] = "organizationalPerson";
            $info['objectclass'][2] = "person";
            $info['objectclass'][3] = "top";
            $info["cn"] = ldap_escape($firstname, '', LDAP_ESCAPE_DN);
            $info["sn"] = ldap_escape($lastname, '', LDAP_ESCAPE_DN);
            $info["uid"] = ldap_escape($uid, '', LDAP_ESCAPE_DN);

            // Hash & encrypt the password
            $salt = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+?><":}{|-=[];,./', 6)), 0, 6);
            $info["userPassword"] = '{SSHA}' . base64_encode(sha1($password . $salt, true) . $salt);

            //Attempt to add this new user
            $r = ldap_add($ds, $dn, $info);

            $entry['member'] = $dn;
            $dnOfGroup = 'cn=patienten,ou=patienten,dc=isala,dc=local';
            $r = ldap_mod_add($ds, $dnOfGroup, $entry);

            //Check if user successfully added
            if (!$r) {
                logger::log($_POST['uid'], 'LDAP - connection failed', $this->logModel);
                $this->err_msg = 'Er is iets misgegaan.';
                return false;
            }
            logger::log($_POST['uid'], 'LDAP - user created successfully', $this->logModel);
            $this->succ_msg = 'De gebruiker is succesvol aangemaakt.';
            return true;

            ldap_close($ds);
        } else {
            // Ldap connection could not be established
            logger::log($_POST['uid'], 'LDAP - connection failed', $this->logModel);
            $this->err_msg = 'Er kan geen verbinding gemaakt worden.';
            return false;
        }
        return true;
    }

    protected function attemptUserDatabaseEntry($uid, $adres, $geboortedatum, $geslacht, $telefoonnummer, $dokter)
    {
        //Check if database connection can be established
        if (!$this->model->getDB()->getConnection()) {
            logger::log($_POST['uid'], 'Database - user creation failed', $this->logModel);
            $this->err_msg = 'Er kan geen verbinding gemaakt worden.';
            return false;
        }

        //check if user already exists
        if ($this->model->getDB()->query('patiëntExists', [$uid])) {
            logger::log($_POST['uid'], 'Database - user already exists', $this->logModel);
            $this->err_msg = 'Deze gebruiker bestaat al';
            return false;
        }

        logger::log($_POST['uid'], 'Database - user created successfully', $this->logModel);
        $this->model->getDB()->query('createUser', [$uid, $adres, $geboortedatum, $geslacht, $telefoonnummer, $dokter]);
    }
}
