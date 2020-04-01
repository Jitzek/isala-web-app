<?php

require_once('../app/core/Controller.php');
require_once('../app/interfaces/Authentication.php');
require_once('../app/interfaces/Authorization.php');
require_once('../app/models/UserModel.php');
require_once('../app/models/PatiëntModel.php');
require_once('../app/models/GecontracteerdModel.php');
require_once("../app/logging/logger.php");

class Profile extends Controller implements Authentication, Authorization
{
    private $model;
    private $user;
    private $target;
    private $logModel;

    public function index($target = '')
    {
        // If no target is given, default to current user
        if (!$target) $target = $_SESSION['uid'];
        if (!$this->authenticate()) {
            logger::log($_SESSION['uid'], 'User automatically logged out', $this->logModel);
            return header("Location: /public/logout");
            die();
        }

        // Define model to be used for this page
        $this->model = $this->model('ProfileModel');

        // Define logging model
        $this->logModel = $this->model('LoggingModel');

        logger::log($_SESSION['uid'], 'Viewing profilepage', $this->logModel);

        // Check if user is authorized to view this page
        if ($target != $_SESSION['uid']) {
            $args['target'] = $target;
            if (!$this->authorize($args)) {
                return header("Location: /public/profile");
                die();
            }
        }

        // Define Target Model 
        if ($this->model->getLDAP()->query('getGroupOfUid', [$target]) == 'patienten') $this->target = new PatiëntModel($target);
        else $this->target = new GecontracteerdModel($target);

        // Define User Model
        $this->user = new UserModel($_SESSION['uid']);
        $algemeen['Naam'] = $this->target->getFullName();
        $algemeen['Adres'] = $this->target->getAdres();
        $algemeen['Telefoonnummer'] = $this->target->getTelefoonnummer();
        // Only define medical_data if target is a Patiënt
        // Decide which role can see what medical data
        if ($this->target->getGroup() == 'patienten') {
            $algemeen['Leeftijd'] = $this->target->getLeeftijd();
            $algemeen['Geslacht'] = $this->target->getGeslacht();
            switch ($this->user->getGroup()) {
                case 'patienten':
                    $medical_data['Dieet'] = $this->target->getMeasurements('Dieet', TRUE);
                    $medical_data['Fysiotherapie'] = $this->target->getMeasurements('Fysiotherapie', TRUE);
                    $medical_data['Psychologie'] = $this->target->getMeasurements('Psychologie', TRUE);
                    break;
                case 'dokters':
                    $medical_data['Dieet'] = $this->target->getMeasurements('Dieet', TRUE);
                    $medical_data['Fysiotherapie'] = $this->target->getMeasurements('Fysiotherapie', TRUE);
                    $medical_data['Psychologie'] = $this->target->getMeasurements('Psychologie', TRUE);
                    break;
                case 'dietisten':
                    $medical_data['Dieet'] = $this->target->getMeasurements('Dieet', TRUE);
                    break;
                case 'fysiotherapeuten':
                    $medical_data['Fysiotherapie'] = $this->target->getMeasurements('Fysiotherapie', TRUE);
                    break;
                case 'psychologen':
                    $medical_data['Psychologie'] = $this->target->getMeasurements('Psychologie', TRUE);
                    break;
            }
        } else $medical_data = [];

        // Parse data to view (beware of order)
        $this->view('includes/head');
        $this->view('includes/navbar', ['name' => $this->user->getFullName()]);
        $this->view('profile/index', [
            'title' => $this->model->getTitle(),
            'firstname' => $this->target->getFirstName(),
            'lastname' => $this->target->getLastName(),
            'algemeen' => $algemeen,
            'medical_data' => $medical_data
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
        $group = $this->model->getLDAP()->query('getGroupOfUid', [$_SESSION['uid']]);

        // Check if user is not a Patiënt
        if ($group == 'patienten') return false;

        // Check if user has access to target
        $patienten = $this->model->getDB()->query('getPatientsOfGecontracteerd', [$_SESSION['uid'], $this->model->getDB()->query('convertGroupToColumn', [$group])]);
        if (!in_array($args['target'], $patienten)) {
            return false;
        }
        return true;
    }
}
