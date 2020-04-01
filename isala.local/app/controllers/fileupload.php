<?php

require_once('../app/core/Controller.php');
require_once('../app/models/UserModel.php');
class Fileupload extends Controller{
    private $model;
    public function index()
    {
        // Define Model to be used
        $this->model = $this->model('FileuploadModel');
        $this->user = new UserModel($_SESSION['uid']);
        // Parse data to view
        $this->view('includes/head');
        $this->view('includes/navbar', ['name' => $this->user->getFullName()]);
        $this->view('fileupload/index', ['title' => $this->model->getTitle(), 'table' => $this->getDocuments()]);
        $this->view('includes/footer');
        echo $_GET['state'];
    }
    //get documents of patient, if role is dokter or patient, get all. else get only where owner of docs
    public function getDocuments(){
        if($_SESSION['role'] == "dokters" || $_SESSION['role'] == "patienten"){
            //hardcoded Boeke, change with post
            $table = $this->model->getDB()->query('getDocs', ["", "Boeke"]);
        }
        else if($_SESSION['role'] == "dietisten" || $_SESSION['role'] == "fysiotherapeuten" || $_SESSION['role'] == "psychologen"){
            $table = $this->model->getDB()->query('getDocs', [$_SESSION['uid'], "Boeke"]);
        }
        return $table;
    }
}