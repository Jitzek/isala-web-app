<?php

require_once('../app/core/Controller.php');
require_once('../app/interfaces/Authentication.php');
require_once('../app/interfaces/Authorization.php');

class Patientlist extends Controller implements Authentication, Authorization
{
    private $model;

    public function index()
    {
        // Define Model to be used
        $this->model = $this->model('PatientListModel');

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

        $user = $this->model('UserModel', [$_SESSION['uid']]);
        $patienten = $this->model->getDB()->query('getPatientsOfGecontracteerd', [$user->getUid(), $this->model->getDB()->query('convertGroupToColumn', [$user->getGroup()])]);
        $patientList = [];
        foreach(isset($patienten) ? $patienten : [] as $patient){
            $patientList[] = $this->model('PatiëntModel', [$patient]);
        }
        $this->view('includes/head');
        $this->view('includes/navbar', ['name' => $user->getFullName()]);
        $this->view('patientlist/index', ['title' => $this->model->getTitle(), 'patientlist' => $patientList]);
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
        // User cannot be a patient
        if ($_SESSION['role'] == 'patienten') {
            return false;
        }
        return true;
    }
}