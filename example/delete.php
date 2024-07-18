<?php
// Initiate Session
session_start();

// Import Auth class into the global namespace
// These must be at the top of your script, not inside a function
use LaswitchTech\phpAUTH\phpAUTH;
use LaswitchTech\phpCSRF\phpCSRF;
use LaswitchTech\phpDB\Database;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Initiate phpAUTH
$phpAUTH = new phpAUTH();

// Initiate phpDB
$phpDB = new Database();

// Initiate phpCSRF
$phpCSRF = new phpCSRF();

// Initialize Types
$Types = ['user','organization','group','role','permission'];

// Initialize Identifiers
$Identifiers = [
  'user' => 'username',
  'organization' => 'id',
  'group' => 'name',
  'role' => 'name',
  'permission' => 'name',
];

// Validate Type
if(!isset($_GET['type']) || !in_array($_GET['type'],$Types)){
  exit;
}

// Store Type
$Page = $_GET['type'];

// Validate Id
if(!isset($_GET['id'])){
  exit;
}

// Store Type
$Id = $_GET['id'];

// Create Manager
$Manager = $phpAUTH->manage("{$Page}s");

// Retrieve Objects
$Object = $Manager->read($Id);

// Retrieve Columns
$Columns = $phpDB->getColumns("{$Page}s");

// Retrieve Required Columns
$Required = $phpDB->getRequired("{$Page}s");

// Retrieve Defaults
$Defaults = $phpDB->getDefaults("{$Page}s");

// Retrieve OnUpdate
$OnUpdate = $phpDB->getOnUpdate("{$Page}s");

// Retrieve Primary
$Primary = $phpDB->getPrimary("{$Page}s");

if(isset($_POST) && !empty($_POST)){
  if($phpCSRF->validate()){
    // if($Object->delete()){
    //   header('Location: manage.php?type=' . $Page);
    //   exit();
    // }
    if($Manager->delete($Id)){
      header('Location: manage.php?type=' . $Page);
      exit();
    }
  }
}

// Header
$Header = ucfirst($Page);

//Render
?>
<!doctype html>
<html lang="en" class="h-100 w-100">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <title><?= $Header ?> Management</title>
    <script src="/vendor/components/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  </head>
  <body class="h-100 w-100">
    <div class="row h-100 w-100 m-0 p-0">
      <?php if($phpAUTH->Authorization->isAuthorized()){ ?>
        <div class="col h-100 m-0 p-0">
          <div class="container h-100">
            <div class="d-flex h-100 row align-items-center justify-content-center">
              <div class="col">
                <h3 class="mt-5 mb-3">Delete this <strong><?= $Header ?></strong> <small>(<?= $Object->get($Identifiers[$Page]); ?>)</small></h3>
                <?php if($phpAUTH->Authentication->isAuthenticated()){ ?>
                  <div class="btn-group w-100 border shadow mb-4">
                    <a href="manage.php?type=<?= $Page ?>" class="btn btn-block btn-light">Return</a>
                    <a href="create.php?type=<?= $Page ?>" class="btn btn-block btn-success">Create</a>
                    <a href="detail.php?type=<?= $Page ?>&id=<?= $Id ?>" class="btn btn-block btn-primary">Details</a>
                    <a href="relationship.php?type=<?= $Page ?>&id=<?= $Id ?>" class="btn btn-block btn-light">Relationships</a>
                    <?php if($Page === "role"){ ?>
                      <a href="permission.php?type=<?= $Page ?>&id=<?= $Id ?>" class="btn btn-block btn-info">Permissions</a>
                    <?php } ?>
                    <a href="edit.php?type=<?= $Page ?>&id=<?= $Id ?>" class="btn btn-block btn-warning">Edit</a>
                    <a href="delete.php?type=<?= $Page ?>&id=<?= $Id ?>" class="btn btn-block btn-danger">Delete</a>
                  </div>
                  <p class="mb-5">
                    <div class="overflow-auto">
                      <form method="post">
                        <div class="row mb-2 mx-0">
                          <div class="col-12 text-center mb-4">
                            Are you sure you want to delete this <?= $Header ?> (<strong><?= $Id ?></strong>)?
                          </div>
                        </div>
                        <input type="hidden" class="d-none" name="csrf" value="<?= $phpCSRF->token() ?>">
                        <div class="btn-group w-100 border shadow mb-4">
                          <button type="submit" class="btn btn-block btn-danger">Yes</button>
                        </div>
                      </form>
                    </div>
                  </p>
                <?php } else { ?>
                  <div class="btn-group w-100 border shadow">
                    <a href="install.php" class="btn btn-block btn-light">Install</a>
                    <a href="/" class="btn btn-block btn-primary">Log In</a>
                  </div>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      <?php } else { ?>
        <div class="col h-100 m-0 p-0">
          <div class="container h-100">
            <div class="d-flex h-100 row align-items-center justify-content-center">
              <div class="col">
                <h3 class="mt-5 mb-3">Unauthorized Host: <strong><?= $_SERVER['SERVER_NAME'] ?></strong></h3>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
    <?= $phpAUTH->Compliance->form() ?>
  </body>
</html>
