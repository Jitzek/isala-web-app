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

        // Define Model to be used
        $model = $this->model('HomeModel');

        // Parse data to view
        $this->view('home/index', ['title' => $model->getTitle()]);
    }
}