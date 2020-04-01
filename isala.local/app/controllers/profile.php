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
    public function index($target = '')
    {
        // If no target is given, default to current user
        if (!$target) $target = $_SESSION['uid'];
        if (!$this->authenticate()) {
            return header("Location: /public/logout");
            die();
        }

        // Define model to be used for this page
        $this->model = $this->model('ProfileModel');

        logger::log($_SESSION['uid'], 'Viewing profilepage', $this->model);

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

        // Define categories allowed for each role 
        $allowed_categories = $this->getAllowedCategories($this->target->getUid(), $this->user->getGroup());

        // Only define medical_data if target is a Patiënt
        if ($this->target->getGroup() == 'patienten') $medical_data = $this->target->getMedicalData();
        else $medical_data = [];

        // Parse data to view (beware of order)
        $this->view('includes/head');
        $this->view('includes/navbar', ['name' => $this->user->getFullName()]);
        $this->view('profile/index', [
            'title' => $this->model->getTitle(),
            'firstname' => $this->target->getFirstName(),
            'lastname' => $this->target->getLastName(),
            'adress' => $this->target->getAdress(),
            'medical_data' => $medical_data,
            'allowed_categories' => $allowed_categories
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

    private function getAllowedCategories($target, $group)
    {
        // If user is the target, user has permission to view everything
        if ($_SESSION['uid'] == $target) {
            return ['Algemeen', 'Dieet', 'Fysiotherapie', 'Psychologie'];
        }
        else {
            switch ($group) {
                case 'dokters':
                    return ['Algemeen', 'Dieet', 'Fysiotherapie', 'Psychologie'];
                case 'dietisten':
                    return ['Algemeen', 'Dieet'];
                case 'fysiotherapeuten':
                    return ['Algemeen', 'Fysiotherapie'];
                case 'psychologen':
                    return ['Algemeen', 'Psychologie'];
                default:
                    return [];
            }
        }
    }
}
