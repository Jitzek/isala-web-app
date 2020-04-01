<?php

require_once('../app/core/Controller.php');
require_once('../app/models/UserModel.php');
require_once('../app/interfaces/Authentication.php');
require_once("../app/logging/logger.php");

class Home extends Controller implements Authentication
{
    private $model;
    public function index()
    {
        if (!$this->authenticate()) {
            logger::log($_SESSION['uid'], 'User automatically logged out', $this->model('HomeModel'));
            return header("Location: /public/logout");
            die();
        }

        // Define Model to be used
        $this->model = $this->model('HomeModel');;
        $user = new UserModel($_SESSION['uid']);

        logger::log($_SESSION['uid'], 'Viewing homepage', $this->model);

        // Parse data to view (beware of order)
        $this->view('includes/head');
        $this->view('includes/navbar', ['name' => $user->getFullName()]);
        $this->view('home/index', ['title' => $this->model->getTitle(), 'name' => $user->getFullName(), 'group' => $user->getGroup()]);
        //$this->view('includes/cookie', ['accepted_cookie' => $user->getCookie()]); // UserModel has no getCookie()
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
