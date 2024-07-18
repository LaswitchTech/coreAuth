<?php
// Initiate Session
session_start();

// These must be at the top of your script, not inside a function
use LaswitchTech\phpAUTH\phpAUTH;
use LaswitchTech\phpConfigurator\phpConfigurator;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Construct Hostnames
$Hostnames = ["localhost","::1","127.0.0.1"];
if(isset($_SERVER['SERVER_NAME']) && !in_array($_SERVER['SERVER_NAME'],$Hostnames)){
  $Hostnames[] = $_SERVER['SERVER_NAME'];
}
if(isset($_SERVER['HTTP_HOST']) && !in_array($_SERVER['HTTP_HOST'],$Hostnames)){
  $Hostnames[] = $_SERVER['HTTP_HOST'];
}

// Initiate phpAUTH
$phpAUTH = new phpAUTH();

// Configure phpAUTH
$phpAUTH->config("hostnames",$Hostnames)
        ->config("basic",false)
        ->config("bearer",false)
        ->config("request",true)
        ->config("cookie",true)
        ->config("session",true)
        ->config("2fa",true)
        ->config("maxAttempts",5)
        ->config("maxRequests",1000)
        ->config("lockoutDuration",1800) // 30 mins
        ->config("windowAttempts",100) // 100 seconds
        ->config("windowRequests",60) // 60 seconds
        ->config("window2FA",60) // 60 seconds
        ->config("windowVerification",2592000) // 30 Days
        ->init();

// Initiate phpConfigurator
$AccountsConfigurator = new phpConfigurator('accounts');

// Install phpAUTH
$Installer = $phpAUTH->install();

// Create a User
$User = $Installer->create("user",["username" => $AccountsConfigurator->get('accounts','username')]);

// Activate User
$User->activate();

// Create an API
$API = $Installer->create("api",["username" => $AccountsConfigurator->get('accounts','api')]);

// Activate API
$API->activate();

// Initiate phpConfigurator
$AccountConfigurator = new phpConfigurator('account');

// Save Account for Testing
$AccountConfigurator->set('account','url',"https://phpauth.local/api.php")
                    ->set('account','username',$User->get('username'))
                    ->set('account','password',$User->getPassword())
                    ->set('account','token',$API->get('username').":".$API->getToken());

//Render
?>
<!doctype html>
<html lang="en" class="h-100 w-100">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <title>Install</title>
    <script src="/vendor/components/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  </head>
  <body class="h-100 w-100">
    <div class="row h-100 w-100 m-0 p-0">
      <div class="col h-100 m-0 p-0">
        <div class="container h-100">
          <div class="d-flex h-100 row align-items-center justify-content-center">
            <div class="col">
              <h3>Installation <strong>Completed</strong></h3>
              <p class="mb-4">
                <span>
                  <strong>Token:</strong> <?= $API->get('username'); ?>:<?= $API->getToken(); ?>
                </span>
              </p>
              <p class="mb-4">
                <span class="me-4">
                  <strong>Username:</strong> <?= $User->get('username'); ?>
                </span>
                <span>
                  <strong>Password:</strong> <?= $User->getPassword(); ?>
                </span>
              </p>
              <div class="btn-group w-100 border shadow">
                <a href="install.php" class="btn btn-block btn-light">Re-Install</a>
                <a href="/" class="btn btn-block btn-primary">Log In</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
