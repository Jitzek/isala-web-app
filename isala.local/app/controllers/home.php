<?php

require_once('../app/core/Controller.php');
require_once('../app/interfaces/Authentication.php');

class Home extends Controller implements Authentication
{
    private $model;
    public function index()
    {
        if (!$this->authenticate()) {
            return header("Location: /public/logout");
            die();
        }
        
        // Define Model to be used
        $this->model = $this->model('HomeModel');
        $user = $this->model('UserModel');

        // Parse data to view (beware of order)
        $this->view('includes/head');
        $this->view('includes/navbar', ['name' => $user->getName()]);
        $this->view('home/index', ['title' => $this->model->getTitle(), 'name' => $user->getName(), 'group' => $user->getGroup()]);
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
}
