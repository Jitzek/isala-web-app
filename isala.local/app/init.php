<?php

/** 
 * Project initialization
*/

// Don't store session in permanent storage
ini_set('session.cookie_lifetime', 0);

// Use cookies for session (preferred HTTP method)
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);

// Prevent user of uninitialized session ID
ini_set('session.use_strict_mode', 1);

// Refuse access to the session cookie from JavaScript (prevents js injection cookie snatching)
ini_set('session.cookie_httponly', 1);

create_session();
regenerate_session();

// Call constructor of main App class
require_once 'core/App.php';
$app = new App();
die();


/**
 * Handle session creation 
*/
function create_session()
{
    // Start Session
    session_start();

    // Check if user has been inactive before resetting timer
    user_inactive();

    // Reset timer for inactivity
    $_SESSION['inactivity'] = time();

    // Initial session lifetime timer start
    if (!isset($_SESSION['lifetime'])) {
        $_SESSION['lifetime'] = time();
    }
}

/**
 * Handle session regeneration 
*/
function regenerate_session()
{
    if (isset($_SESSION['uid'])) {
        // Regenerate Session ID each 5 minutes
        if (isset($_SESSION['lifetime']) && $_SESSION['lifetime'] < time() - (5 * 60)) {
            // Regenerate Session ID
            session_regenerate_id(TRUE);

            // Start with new session ID
            session_start();

            // Reset session lifetime Timer
            $_SESSION['lifetime'] = time();
        }
    }
}

/**
 * Handle user inactivity
*/
function user_inactive()
{
    // Logout user if the user has been inactive for >= 15 minutes
    if (isset($_SESSION['inactivity']) && $_SESSION['inactivity'] < time() - (15 * 60)) {
        unset($_SESSION['inactivity']);
        return header("Location: /public/logout");
        die();
    }
}