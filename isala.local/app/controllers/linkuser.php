<?php

require_once('../app/core/Controller.php');
require_once('../app/models/UserModel.php');
require_once('../app/interfaces/Authentication.php');
require_once("../app/logging/logger.php");

class linkuser extends Controller implements Authentication
{
    private $model;
    private $logModel;
    private $err_msg = '';
    private $data;

    public function index()
    {
        if (!$this->authenticate()) {
            return header("Location: /public/logout");
            die();
        }


        $this->model = $this->model('LinkUserModel');
        $user = new UserModel($_SESSION['uid']);

        // Define logging model
        $this->logModel = $this->model('LoggingModel');

        $patienten = $this->getPatients();

        $gecontracteerden = $this->getContracted();

        if (!$this->authorize()) {
            return header("Location: /public/logout");
            die();
        }

        //user is selected
        if (isset($_POST["submitUser"])) {
            if ($_POST["patient"]) {
                $this->data[0] = $this->getDietist($_POST["patient"][0]);
                $this->data[1] = $this->getFysiotherapeut($_POST["patient"][0]);
                $this->data[2] = $this->getPsycholoog($_POST["patient"][0]);
            }
        }

        // Parse data to view (beware of order)
        $this->view('includes/head');
        $this->view('includes/navbar', ['name' => $user->getFullName()]);
        $this->view('linkuser/index', ['title' => $this->model->getTitle(), 'name' => $user->getFullName(), 'group' => $user->getGroup(),
            'showpatienten' => $patienten, 'showcontracted' => $gecontracteerden, 'dietist' => $this->data[0],
            'fysio' => $this->data[1], 'psych' => $this->data[2]]);
        //$this->view('includes/cookie', ['accepted_cookie' => $user->getCookie()]); // UserModel has no getCookie()
        $this->view('includes/footer');


        echo "<div id=\"userlist\">";

        // Check if form is submitted successfully
        if (isset($_POST["submit"])) {
            // Check if any option is selected
            if ($_POST["contracted"]) {
                // Retrieving each selected option
                foreach ($_POST['patient'] as $subject)
                    echo "You selected $subject";

                echo "<br>";

                foreach ($_POST['contracted'] as $subject)
                    echo "You selected $subject";

                foreach ($_POST['patient'] as $patient) {
                    foreach ($_POST['contracted'] as $contracted) {
                        echo "<br>";
                        if ($this->linkUserToGecontracteerden($patient, $contracted)) {
                            echo "Gebruikers zijn met success gelinkt.";
                        } else {
                            if ($this->err_msg == '') {
                                logger::log($_SESSION['uid'], 'Linking users went wrong', $this->logModel);
                                echo "<p style=\"color: #FC240F\">Linking users went wrong</p>";
                            } else {
                                logger::log($_SESSION['uid'], $this->err_msg, $this->logModel);
                                echo "<p style=\"color: #FC240F\">" . htmlentities($this->err_msg) . "</p>";
                            }
                        }
                    }
                }
            } else
                echo "U moet zowel een patiënt als een gecontracteerde medewerker selecteren.";

        }

        echo "</div>";
        echo "<br>";

    }

    public function authenticate()
    {
        // Require Session variables
        if (!$_SESSION['uid']) {
            return false;
        }
        return true;
    }

    // Can this user view this page
    public function authorize()
    {
        $group = $this->model->getLDAP()->query('getGroupOfUid', [$_SESSION['uid']]);

        // Check if user is not a Patiënt
        if ($group == 'patienten') return false;

        return true;
    }

    // uid -> patient, guid -> gecontracteerd
    public function linkUserToGecontracteerden($uid, $guid)
    {
        // Check for LDAP Connection
        if (!$this->model->getLDAP()->getConnection()) {
            $this->err_msg = 'Connection Failed';
            return false;
        }
        // Check for DB Connection
        if (!$this->model->getDB()->getConnection()) {
            $this->err_msg = 'Connection Failed';
            return false;
        }

        // Get group of selected gecontracteerden.
        $group = $this->model->getLDAP()->query('getGroupOfUid', [$guid]);

        // Convert LDAP group to Patient column
        $group = $this->model->getDB()->query('convertGroupToColumn', [$group]);

        if ($this->model->getDB()->query('linkGecontracteerdenToUsers', [$uid, $group, $guid])) {
            return true;
        } else {
            return false;
        }
    }

    public function getPatients()
    {
        // Check for LDAP Connection
        if (!$this->model->getLDAP()->getConnection()) {
            $this->err_msg = 'Connection Failed';
            return false;
        }
        // Check for DB Connection
        if (!$this->model->getDB()->getConnection()) {
            $this->err_msg = 'Connection Failed';
            return false;
        }

        if (!$group = $this->model->getLDAP()->query('getGroupOfUid', [$_SESSION['uid']])) {
            $this->err_msg = 'Could not find group';
            return false;
        }

        // Check if user is not a Patiënt
        if ($group == 'patienten') return false;

        $patienten = $this->model->getDB()->query('getPatientsOfGecontracteerd', [$_SESSION['uid'], $this->model->getDB()->query('convertGroupToColumn', [$group])]);

        return $patienten;
    }

    public function getContracted()
    {
        // Check for LDAP Connection
        if (!$this->model->getLDAP()->getConnection()) {
            $this->err_msg = 'Connection Failed';
            return false;
        }
        // Check for DB Connection
        if (!$this->model->getDB()->getConnection()) {
            $this->err_msg = 'Connection Failed';
            return false;
        }

        // Check if user has access to target
        if (!$gecontracteerden = $this->model->getDB()->query('getGecontracteerdWithoutCurrent', [$_SESSION['uid']])) {
            $this->err_msg = 'Could not find gecontracteerden';
            return false;
        }

        $contracted = NULL;
        foreach ($gecontracteerden as $uid) {
            $group = $this->model->getLDAP()->query('getGroupOfUid', [$uid]);
            if ($group != 'dokters') {
                $contracted[] = $uid;
            }
        }

        return $contracted;
    }

    public function getDietist($uid)
    {
        // Check for DB Connection
        if (!$this->model->getDB()->getConnection()) {
            $this->err_msg = 'Connection Failed';
            return false;
        }

        $dietist = $this->model->getDB()->query('getDietistFromPatient', [$uid]);

        return $dietist;
    }

    public function getFysiotherapeut($uid)
    {
        // Check for DB Connection
        if (!$this->model->getDB()->getConnection()) {
            $this->err_msg = 'Connection Failed';
            return false;
        }

        $fysiotherapeut = $this->model->getDB()->query('getFysiotherapeutFromPatient', [$uid]);

        return $fysiotherapeut;
    }

    public function getPsycholoog($uid)
    {
        // Check for DB Connection
        if (!$this->model->getDB()->getConnection()) {
            $this->err_msg = 'Connection Failed';
            return false;
        }

        $psycholoog = $this->model->getDB()->query('getPsycholoogFromPatient', [$uid]);


        return $psycholoog;
    }
}