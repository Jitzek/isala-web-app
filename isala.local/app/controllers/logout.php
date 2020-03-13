<?php

require_once('../app/core/Controller.php');

class Logout extends Controller
{
    public function index()
    {
        // Handle Post Request (logout)
        if ($_POST['logout']) {
            $this->attemptLogout();
            if (session_status() == PHP_SESSION_ACTIVE) {
                echo "<p style=\"color: #FC240F\">Failed to logout</p>";
                return;
            }
            header("Location: /public/login");
        }
    }

    protected function attemptLogout()
    {
        // Abandon Session
        session_destroy();
    }
}
