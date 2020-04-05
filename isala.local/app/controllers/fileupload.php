<?php

require_once('../app/core/Controller.php');
require_once('../app/interfaces/Authentication.php');
require_once('../app/interfaces/Authorization.php');

class Fileupload extends Controller implements Authentication, Authorization
{
    private $model;
    public function index($uid = NULL)
    {
        // default to current user
        if (!isset($uid)) $uid = $_SESSION['uid'];

        // Authenticate User
        if (!$this->authenticate()) {
            return header("Location: /public/logout");
            die();
        }

        // Authorize User
        if (!$this->authorize(['uid' => $uid])) {
            return header("Location: /public/home");
            exit();
        }

        // Define Model to be used
        $this->model = $this->model('FileuploadModel');
        $user = $this->model('UserModel', [$_SESSION['uid']]);
        // Parse data to view
        $this->view('includes/head');
        $this->view('includes/navbar', ['name' => $user->getFullName()]);
        $this->view('fileupload/index', ['title' => $this->model->getTitle(), 'table' => $this->getDocuments($uid), 'uid' => $uid]);
        $this->view('includes/footer');
    }
    //get documents of patient, if role is dokter or patient, get all. else get only where owner of docs
    private function getDocuments($uid)
    {
        if ($_SESSION['role'] == "dokters" || $_SESSION['role'] == "patienten") {
            $table = $this->model->getDB()->query('getDocs', ["", $uid]);
        } else if ($_SESSION['role'] == "dietisten" || $_SESSION['role'] == "fysiotherapeuten" || $_SESSION['role'] == "psychologen") {
            $table = $this->model->getDB()->query('getDocs', [$_SESSION['uid'], $uid]);
        }
        return $table;
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
        // Patiënt can see own page
        if ($_SESSION['role'] == 'patienten' && $args['uid'] == $_SESSION['uid']) return true;

        $model = $this->model('AuthorizationModel');
        $role = $model->getDB()->query('convertGroupToColumn', [$_SESSION['role']]);
        if (!in_array($args['uid'], $model->getDB()->query('getPatientsOfGecontracteerd', [$_SESSION['uid'], $role]))) {
            return false;
        }
        return true;
    }
}
