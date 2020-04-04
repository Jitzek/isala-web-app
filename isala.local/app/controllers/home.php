<?php

require_once('../app/core/Controller.php');
require_once('../app/interfaces/Authentication.php');
require_once("../app/logging/logger.php");

class Home extends Controller implements Authentication
{
    private $model;
    private $logModel;

    public function index()
    {
        // Authenticate User
        if (!$this->authenticate()) {
            return header("Location: /public/logout");
            die();
        }
        
        // Define logging model
        $this->logModel = $this->model('LoggingModel');

        // Define Model to be used
        $this->model = $this->model('HomeModel');
        //$user = new UserModel($_SESSION['uid']);
        $user = $this->model('UserModel', [$_SESSION['uid']]);

        logger::log($_SESSION['uid'], 'Viewing homepage', $this->logModel);

        // Parse data to view (beware of order)
        $this->view('includes/head');
        $this->view('includes/navbar', ['name' => $user->getFullName()]);
        $this->view('home/index', ['title' => $this->model->getTitle(), 'name' => $user->getFullName(), 'group' => $user->getGroup(),
            'auth' => $user->getGroup() == 'dokters' ? true : false]);
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
}
