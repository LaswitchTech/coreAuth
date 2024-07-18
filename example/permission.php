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

// Initialize Links
$Links = [
  'user' => ['organization', 'group', 'role'],
  'organization' => ['user', 'organization', 'group', 'role'],
  'group' => ['user', 'organization', 'role'],
  'role' => ['user', 'organization', 'group'],
  'permission' => [],
];

// Initialize RecordTypes
$RecordTypes = [
  'users' => 'user',
  'organizations' => 'organization',
  'groups' => 'group',
  'roles' => 'role',
  'permissions' => 'permission',
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

// Validate Id
if(!isset($_GET['id'])){
  exit;
}

// Store Type
$Id = $_GET['id'];

// Create Sub-Manager
$RoleManager = $phpAUTH->manage("roles");

// Retrieve Users
$Roles = $RoleManager->read();

// Create Sub-Manager
$PermissionManager = $phpAUTH->manage("permissions");

// Retrieve Users
$Permissions = $PermissionManager->read();

// Create Manager
$Manager = $RoleManager;

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

// Header
$Header = ucfirst($Page);

// Handle Forms
if(isset($_POST) && !empty($_POST)){
  if($phpCSRF->validate()){

    // Handle Set Form
    if(isset($_POST['name'],$_POST['set'])){
      $Object->set($_POST['name']);
    }

    // Handle Increase Form
    if(isset($_POST['name'],$_POST['level'],$_POST['increase'])){
      $Object->set($_POST['name'], (intval($_POST['level']) + 1));
    }

    // Handle Decrease Form
    if(isset($_POST['name'],$_POST['level'],$_POST['decrease'])){
      $Object->set($_POST['name'], (intval($_POST['level']) - 1));
    }

    // Handle Unset Form
    if(isset($_POST['name'],$_POST['unset'])){
      $Object->unset($_POST['name']);
    }
  }
}

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
                <h3 class="mt-5 mb-3">Permissions of <strong><?= $Header ?></strong> <small>(<?= $Object->get($Identifiers[$Page]); ?>)</small></h3>
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
                  <p class="mb-3">
                    <div class="overflow-auto">
                      <form method="post">
                        <div class="row mb-2 mx-0">
                          <div class="col-12">
                            <div class="input-group">
                              <span class="input-group-text text-bg-primary" id="labelPermission">Permission</span>
                              <select class="form-select" id="selectType" name="name" aria-label="Permission" aria-describedby="labelPermission" required>
                                <option selected>Select a permission</option>
                                <?php foreach($Permissions as $Permission){ ?>
                                  <option value="<?= $Permission->get('name') ?>"><?= $Permission->get('name') ?></option>
                                <?php } ?>
                              </select>
                            </div>
                          </div>
                        </div>
                        <input type="hidden" class="d-none" name="csrf" value="<?= $phpCSRF->token() ?>">
                        <div class="btn-group w-100 border shadow mt-4 mb-4">
                          <button type="submit" name="set" class="btn btn-block btn-success">Set</button>
                        </div>
                      </form>
                    </div>
                  </p>
                  <p class="mb-3">
                    <div class="overflow-auto my-2">
                      <table class="table border table-striped table-hover">
                        <thead>
                          <tr class="text-bg-light">
                            <th class="border">Name</th>
                            <th class="border">Level</th>
                            <th class="border">Effective</th>
                            <th class="border position-sticky end-0 text-bg-light">Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach($Object->get('permissions') as $Name => $Level){ ?>
                            <tr>
                              <td class="border"><?= $Name ?></td>
                              <td class="border"><?= $Level ?></td>
                              <td class="border">
                                <?php foreach($Levels as $LevelValue => $LevelName){ ?>
                                  <?php if($LevelValue > 0 && $LevelValue <= $Level){ ?>
                                    <span class="badge rounded-pill mx-1 text-bg-<?= $Colors[$LevelName] ?>"><?= $LevelName ?></span>
                                  <?php } else { ?>
                                    <?php if($LevelValue <= 0){ ?>
                                      <span class="badge rounded-pill mx-1 text-bg-<?= $Colors[$LevelName] ?>"><?= $LevelName ?></span>
                                    <?php } ?>
                                  <?php } ?>
                                <?php } ?>
                              </td>
                              <td class="border position-sticky end-0 text-bg-light">
                                <form method="post">
                                  <input type="hidden" class="d-none" name="name" value="<?= $Name ?>">
                                  <input type="hidden" class="d-none" name="level" value="<?= $Level ?>">
                                  <input type="hidden" class="d-none" name="csrf" value="<?= $phpCSRF->token() ?>">
                                  <div class="btn-group border shadow">
                                    <button type="submit" name="increase" value="increase" class="btn btn-sm btn-success">Increase</button>
                                    <button type="submit" name="decrease" value="decrease" class="btn btn-sm btn-danger">Decrease</button>
                                    <button type="submit" name="unset" value="unset" class="btn btn-sm btn-light">Unset</button>
                                  </div>
                                </form>
                              </td>
                            </tr>
                          <?php } ?>
                        </tbody>
                      </table>
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
