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
        //$user->getCookie();

        // Parse data to view (beware of order)
        $this->view('includes/head');
        $this->view('includes/navbar', ['name' => $user->getName()]);
        $this->view('home/index', ['title' => $this->model->getTitle(), 'name' => $user->getName(), 'group' => $user->getGroup()]);
        $this->view('includes/cookie', ['accepted_cookie' => $user->getCookie()]);
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

    public function cookie()
    {
        //Set accepted cookie to true
        $user = $user = $this->model('UserModel');
        $user->setCookie(1);

        header("Location: /public/home");
    }
}
