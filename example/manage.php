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

// Initialize Levels
$Levels = ['none','read','create','edit','delete'];
$Colors = [
  'none' => 'secondary',
  'read' => 'primary',
  'create' => 'success',
  'edit' => 'warning',
  'delete' => 'danger',
];

// Validate Type
if(!isset($_GET['type']) || !in_array($_GET['type'],$Types)){
  exit;
}

// Store Type
$Page = $_GET['type'];

// Create Manager
$Manager = $phpAUTH->manage("{$Page}s");

// Retrieve Objects
$Objects = $Manager->read();

// Retrieve Columns
$Columns = $phpDB->getColumns("{$Page}s");

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
                <h3 class="mt-5 mb-3"><?= $Header ?> <strong>Management</strong> <small>(<?= count($Objects); ?>)</small></h3>
                <?php if($phpAUTH->Authentication->isAuthenticated()){ ?>
                  <div class="btn-group w-100 border shadow mb-4">
                    <a href="/" class="btn btn-block btn-light">Index</a>
                    <?php foreach($Types as $Type){ ?>
                      <?php if($Type === $Page){ ?>
                        <a href="manage.php?type=<?= $Type ?>" class="btn btn-block btn-primary"><?= ucfirst($Type) ?></a>
                      <?php } else { ?>
                        <a href="manage.php?type=<?= $Type ?>" class="btn btn-block btn-light"><?= ucfirst($Type) ?></a>
                      <?php } ?>
                    <?php } ?>
                  </div>
                  <p class="mb-5">
                    <div class="btn-group w-100 border shadow mb-3">
                      <a href="create.php?type=<?= $Page ?>" class="btn btn-block btn-success">Create</a>
                    </div>
                    <div class="overflow-auto">
                      <table class="table border table-striped table-hover">
                        <thead>
                          <tr class="text-bg-light">
                            <?php foreach($Columns as $Column => $DataType){ ?>
                              <th class="border"><?= ucfirst($Column) ?></th>
                            <?php } ?>
                            <?php if($Page === "permission"){ ?>
                              <th class="border">Effective</th>
                            <?php } ?>
                            <th class="border position-sticky end-0 text-bg-light">Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach($Objects as $Object){ ?>
                            <tr>
                              <?php foreach($Columns as $Column => $DataType){ ?>
                                <?php $Value = $Object->get($Column); ?>
                                <?php if(is_array($Value)){ $Value = json_encode($Value, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); } ?>
                                <td class="border"><?= $Value ?></td>
                              <?php } ?>
                              <?php if($Page === "permission"){ ?>
                                <td class="border">
                                  <?php foreach($Levels as $Level => $Name){ ?>
                                    <?php if($Level > 0 && $phpAUTH->Authorization->hasPermission($Object->get('name'), $Level)){ ?>
                                      <span class="badge rounded-pill mx-1 text-bg-<?= $Colors[$Name] ?>"><?= $Name ?></span>
                                    <?php } else { ?>
                                      <?php if($Level <= 0){ ?>
                                        <span class="badge rounded-pill mx-1 text-bg-<?= $Colors[$Name] ?>"><?= $Name ?></span>
                                      <?php } ?>
                                    <?php } ?>
                                  <?php } ?>
                                </td>
                              <?php } ?>
                              <td class="border position-sticky end-0 text-bg-light">
                                <div class="btn-group border shadow">
                                  <a href="detail.php?type=<?= $Page ?>&id=<?= $Object->get($Identifiers[$Page]); ?>" class="btn btn-sm btn-primary">Details</a>
                                  <?php if($Page !== "permission"){ ?>
                                    <a href="relationship.php?type=<?= $Page ?>&id=<?= $Object->get($Identifiers[$Page]); ?>" class="btn btn-sm btn-light">Relationships</a>
                                  <?php } ?>
                                  <?php if($Page === "role"){ ?>
                                    <a href="permission.php?type=<?= $Page ?>&id=<?= $Object->get($Identifiers[$Page]); ?>" class="btn btn-sm btn-info">Permissions</a>
                                  <?php } ?>
                                  <a href="edit.php?type=<?= $Page ?>&id=<?= $Object->get($Identifiers[$Page]); ?>" class="btn btn-sm btn-warning">Edit</a>
                                  <a href="delete.php?type=<?= $Page ?>&id=<?= $Object->get($Identifiers[$Page]); ?>" class="btn btn-sm btn-danger">Delete</a>
                                </div>
                              </td>
                            </tr>
                          <?php } ?>
                        </tbody>
                      </table>
                    </div>
                  </p>
                <?php } else { ?>
                  <div class="btn-group w-100 border shadow">
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
