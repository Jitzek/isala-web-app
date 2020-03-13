<?php
/** 
 * Project initialization
*/ 

// Start Session
session_start();

// Call constructor of main App class
require_once 'core/App.php';
$app = new App();