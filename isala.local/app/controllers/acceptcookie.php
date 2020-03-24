<?php
require_once('../app/core/Controller.php');

class AcceptCookie extends Controller
{
    public function index()
    {
        //Set accepted cookie to true
        $user = $user = $this->model('UserModel');
        $user->setCookie(1);

        header("Location: /public/home");
    }
}
?>