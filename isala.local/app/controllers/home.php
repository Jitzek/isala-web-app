<?php

require_once('../app/core/Controller.php');

class Home extends Controller
{
    private $model;
    public function index()
    {
        if (!$_SESSION['uid']) {
            return header("Location: /public/login");
        }
        $model = $this->model('HomeModel');

        $this->view('home/index', ['title' => $model->getTitle()]);
    }
}