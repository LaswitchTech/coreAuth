<?php
// Initiate Session
session_start();

// These must be at the top of your script, not inside a function
use LaswitchTech\coreAuth\Auth;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Initiate Auth
$Auth = new Auth();

// Dump Connection Status
echo json_encode($Auth->Authentication->isAuthenticated());
