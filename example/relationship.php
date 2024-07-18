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

// Create Sub-Manager
$UserManager = $phpAUTH->manage("users");

// Retrieve Users
$Users = $UserManager->read();

// Create Sub-Manager
$OrganizationManager = $phpAUTH->manage("organizations");

// Retrieve Users
$Organizations = $OrganizationManager->read();

// Create Sub-Manager
$GroupManager = $phpAUTH->manage("groups");

// Retrieve Users
$Groups = $GroupManager->read();

// Create Sub-Manager
$RoleManager = $phpAUTH->manage("roles");

// Retrieve Users
$Roles = $RoleManager->read();

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

// Header
$Header = ucfirst($Page);

// Handle Forms
if(isset($_POST) && !empty($_POST)){
  if($phpCSRF->validate()){

    // Handle Link Form
    if(isset($_POST['type'],$_POST[$_POST['type']])){
      $Data = [
        "Table" => $_POST['type'] . 's',
        "Id" => $_POST[$_POST['type']],
      ];
      $Object->link($Data['Table'], $Data['Id']);
    }

    // Handle Unlink Form
    if(isset($_POST['unlink'])){
      $Data = explode(':',$_POST['unlink']);
      $Data = [
        "Table" => $Data[0],
        "Id" => $Data[1],
      ];
      $Object->unlink($Data['Table'], $Data['Id']);
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
                <h3 class="mt-5 mb-3">Relationships of <strong><?= $Header ?></strong> <small>(<?= $Object->get($Identifiers[$Page]); ?>)</small></h3>
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
                              <span class="input-group-text text-bg-primary" id="labelType">Type</span>
                              <select class="form-select" id="selectType" name="type" aria-label="Type" aria-describedby="labelType" required>
                                <option selected>Select a type of object</option>
                                <?php foreach($Links[$Page] as $Type){ ?>
                                  <option value="<?= $Type; ?>"><?= ucfirst($Type); ?></option>
                                <?php } ?>
                              </select>
                            </div>
                          </div>
                        </div>
                        <div class="row mb-2 mx-0">
                          <div class="col-12 d-none" id="user">
                            <div class="input-group">
                              <span class="input-group-text" id="labelUser">User</span>
                              <select class="form-select" id="selectUser" name="user" aria-label="User" aria-describedby="labelUser" required>
                                <option selected>Select a User to be linked</option>
                                <?php foreach($Users as $User){ ?>
                                  <option value="<?= $User->get('id'); ?>"><?= ucfirst($User->get('username')); ?></option>
                                <?php } ?>
                              </select>
                            </div>
                          </div>
                          <div class="col-12 d-none" id="organization">
                            <div class="input-group">
                              <span class="input-group-text" id="labelOrganization">Organization</span>
                              <select class="form-select" id="selectOrganization" name="organization" aria-label="Organization" aria-describedby="labelOrganization" required>
                                <option selected>Select an Organization to be linked</option>
                                <?php foreach($Organizations as $Organization){ ?>
                                  <option value="<?= $Organization->get('id'); ?>"><?= ucfirst($Organization->get('name')); ?></option>
                                <?php } ?>
                              </select>
                            </div>
                          </div>
                          <div class="col-12 d-none" id="group">
                            <div class="input-group">
                              <span class="input-group-text" id="labelGroup">Group</span>
                              <select class="form-select" id="selectGroup" name="group" aria-label="Group" aria-describedby="labelGroup" required>
                                <option selected>Select a Group to be linked</option>
                                <?php foreach($Groups as $Group){ ?>
                                  <option value="<?= $Group->get('id'); ?>"><?= ucfirst($Group->get('name')); ?></option>
                                <?php } ?>
                              </select>
                            </div>
                          </div>
                          <div class="col-12 d-none" id="role">
                            <div class="input-group">
                              <span class="input-group-text" id="labelRole">Role</span>
                              <select class="form-select" id="selectRole" name="role" aria-label="Role" aria-describedby="labelRole" required>
                                <option selected>Select a Role to be linked</option>
                                <?php foreach($Roles as $Role){ ?>
                                  <option value="<?= $Role->get('id'); ?>"><?= ucfirst($Role->get('name')); ?></option>
                                <?php } ?>
                              </select>
                            </div>
                          </div>
                        </div>
                        <input type="hidden" class="d-none" name="csrf" value="<?= $phpCSRF->token() ?>">
                        <div class="btn-group w-100 border shadow mt-4 mb-4">
                          <button type="submit" class="btn btn-block btn-primary">Link</button>
                        </div>
                      </form>
                    </div>
                  </p>
                  <p class="mb-3">
                    <?php foreach($Object->get('relationships') as $Table => $Records){ ?>
                      <?php $Columns = $phpDB->getColumns($Table); ?>
                      <div class="overflow-auto my-2">
                        <form method="post">
                          <table class="table border table-striped table-hover">
                            <thead>
                              <tr class="text-bg-light">
                                <th class="border" colspan="<?= count($Columns) ?>"><?= ucfirst($Table) ?></th>
                              </tr>
                              <tr class="text-bg-light">
                                <?php foreach($Columns as $Column => $DataType){ ?>
                                  <th class="border"><?= ucfirst($Column) ?></th>
                                <?php } ?>
                                <th class="border position-sticky end-0 text-bg-light">Actions</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php foreach($Records as $Record){ ?>
                                <tr>
                                  <?php foreach($Record as $Column => $Value){ ?>
                                    <td class="border"><?= $Value ?></td>
                                  <?php } ?>
                                  <td class="border position-sticky end-0 text-bg-light">
                                    <input type="hidden" class="d-none" name="csrf" value="<?= $phpCSRF->token() ?>">
                                    <div class="btn-group border shadow">
                                      <a href="relationship.php?type=<?= $RecordTypes[$Table] ?>&id=<?= $Record[$Identifiers[$RecordTypes[$Table]]] ?>" class="btn btn-sm btn-primary">Details</a>
                                      <button type="submit" name="unlink" value="<?= $Table ?>:<?= $Record['id'] ?>" class="btn btn-sm btn-danger">Unlink</button>
                                    </div>
                                  </td>
                                </tr>
                              <?php } ?>
                            </tbody>
                          </table>
                        </form>
                      </div>
                    <?php } ?>
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
    <script>
      $('#selectType').change(function(){
        const Select = $(this)
        $('#user').addClass('d-none');
        $('#organization').addClass('d-none');
        $('#group').addClass('d-none');
        $('#role').addClass('d-none');
        $('#' + Select.val()).removeClass('d-none');
      })
    </script>
  </body>
</html>
