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
        $user = $this->model('UserModel');

        // Parse data to view
        $this->view('home/index', ['title' => $model->getTitle(), 'name' => $user->getName(), 'group' => $user->getGroup()]);
    }
}