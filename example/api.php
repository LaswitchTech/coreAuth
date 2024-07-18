<?php
// Initiate Session
session_start();

// These must be at the top of your script, not inside a function
use LaswitchTech\phpAUTH\phpAUTH;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Initiate phpAUTH
$phpAUTH = new phpAUTH();

// Dump Connection Status
echo json_encode($phpAUTH->Authentication->isAuthenticated());
