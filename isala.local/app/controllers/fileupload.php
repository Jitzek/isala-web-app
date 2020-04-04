<?php

require_once('../app/core/Controller.php');
class Fileupload extends Controller{
    private $model;
    public function index($uid = "")
    {
        // Define Model to be used
        $this->model = $this->model('FileuploadModel');
        $user =$this->model('UserModel', [$_SESSION['uid']]);
        // Parse data to view
        $this->view('includes/head');
        $this->view('includes/navbar', ['name' => $user->getFullName()]);
        $this->view('fileupload/index', ['title' => $this->model->getTitle(), 'table' => $this->getDocuments($uid), 'uid' => $uid]);
        $this->view('includes/footer');
    }
    //get documents of patient, if role is dokter or patient, get all. else get only where owner of docs
    private function getDocuments($uid){
        if($_SESSION['role'] == "dokters" || $_SESSION['role'] == "patienten"){
            $table = $this->model->getDB()->query('getDocs', ["", $uid]);
        }
        else if($_SESSION['role'] == "dietisten" || $_SESSION['role'] == "fysiotherapeuten" || $_SESSION['role'] == "psychologen"){
            $table = $this->model->getDB()->query('getDocs', [$_SESSION['uid'], $uid]);
        }
        return $table;
    }
}