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

    // Sanitize Form
    if(isset($_POST['isBanned'])){
      $_POST['isBanned'] = intval($_POST['isBanned']);
      if($_POST['isBanned']){
        if($_POST['isBanned'] !== $Object->get('isBanned')){ $Object->ban(); }
      } else {
        if($_POST['isBanned'] !== $Object->get('isBanned')){ $Object->unban(); }
      }
      unset($_POST['isBanned']);
    }
    if(isset($_POST['isActive'])){
      $_POST['isActive'] = intval($_POST['isActive']);
      if($_POST['isActive']){
        if($_POST['isActive'] !== $Object->get('isActive')){ $Object->activate(); }
      } else {
        if($_POST['isActive'] !== $Object->get('isActive')){ $Object->deactivate(); }
      }
      unset($_POST['isActive']);
    }
    if(array_key_exists("isContactInfoDynamic",$_POST)){ $_POST['isContactInfoDynamic'] = intval($_POST['isContactInfoDynamic']); }
    if(array_key_exists("database",$_POST)){ $_POST['database'] = strval($_POST['database']); }
    if(isset($Columns['2FAMethod'])){
      $Method2FA = [];
      if(array_key_exists("2FAMethodSMS",$_POST)){ $Method2FA[] = 'sms'; unset($_POST['2FAMethodSMS']); }
      if(array_key_exists("2FAMethodSMTP",$_POST)){ $Method2FA[] = 'smtp'; unset($_POST['2FAMethodSMTP']); }
      $_POST['2FAMethod'] = $Method2FA;
    }

    // // $Object->save($_POST);
    if($Manager->update($Id, $_POST)){
      $Object->retrieve(true);
      if(strval($Id) !== strval($_POST[$Identifiers[$Page]])){
        header('Location: edit.php?type=' . $Page . '&id=' . $_POST[$Identifiers[$Page]]);
        exit();
      }
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
                <h3 class="mt-5 mb-3">Edit this <strong><?= $Header ?></strong> <small>(<?= $Object->get($Identifiers[$Page]); ?>)</small></h3>
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
                        <?php foreach($Columns as $Column => $DataType){ ?>
                          <?php if(in_array($Column,['passwordSalt','passwordHash','2FASalt','2FAHash','bearerToken','permissions','attempts','requests','lastAttempt','lastRequest','last2FA','sessionId','2FAMethod','server'])){ continue; } ?>
                          <?php if(isset($Defaults[$Column]) || isset($OnUpdate[$Column]) || $Column == $Primary){ continue; } ?>
                          <?php $Value = $Object->get($Column); ?>
                          <?php if(is_array($Value)){ $Value = json_encode($Value, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); } ?>
                          <div class="row mb-2 mx-0">
                            <div class="col-12">
                              <div class="input-group">
                                <span class="input-group-text <?php if(in_array($Column,$Required)){ echo "text-bg-primary"; } ?>" id="label<?= $Column ?>"><?= $Column ?></span>
                                <?php
                                  switch($DataType){
                                    case"longtext":
                                    case"text":
                                    case"json":
                                      ?><textarea class="form-control" name="<?= $Column ?>" placeholder="<?= ucfirst($Column) ?>" aria-label="<?= ucfirst($Column) ?>" aria-describedby="label<?= $Column ?>" <?php if(in_array($Column,$Required)){ echo "required"; } ?>><?= $Value ?></textarea><?php
                                      break;
                                    case"date":
                                      ?><input type="date" name="<?= $Column ?>" value="<?= $Value ?>" class="form-control" placeholder="<?= ucfirst($Column) ?>" aria-label="<?= ucfirst($Column) ?>" aria-describedby="label<?= $Column ?>" <?php if(in_array($Column,$Required)){ echo "required"; } ?>><?php
                                      break;
                                    case"time":
                                      ?><input type="time" name="<?= $Column ?>" value="<?= $Value ?>" class="form-control" placeholder="<?= ucfirst($Column) ?>" aria-label="<?= ucfirst($Column) ?>" aria-describedby="label<?= $Column ?>" <?php if(in_array($Column,$Required)){ echo "required"; } ?>><?php
                                      break;
                                    case"datetime":
                                      ?><input type="datetime-local" name="<?= $Column ?>" value="<?= $Value ?>" class="form-control" placeholder="<?= ucfirst($Column) ?>" aria-label="<?= ucfirst($Column) ?>" aria-describedby="label<?= $Column ?>" <?php if(in_array($Column,$Required)){ echo "required"; } ?>><?php
                                      break;
                                    case"int":
                                    case"bigint":
                                      ?><input type="number" name="<?= $Column ?>" value="<?= $Value ?>" class="form-control" placeholder="<?= ucfirst($Column) ?>" aria-label="<?= ucfirst($Column) ?>" aria-describedby="label<?= $Column ?>" <?php if(in_array($Column,$Required)){ echo "required"; } ?>><?php
                                      break;
                                    default:
                                      ?><input type="text" name="<?= $Column ?>" value="<?= $Value ?>" class="form-control" placeholder="<?= ucfirst($Column) ?>" aria-label="<?= ucfirst($Column) ?>" aria-describedby="label<?= $Column ?>" <?php if(in_array($Column,$Required)){ echo "required"; } ?>><?php
                                      break;
                                  }
                                ?>
                              </div>
                            </div>
                          </div>
                        <?php } ?>
                        <?php if(isset($Columns['server'])){ ?>
                          <?php $Server = $Object->get('server'); ?>
                          <div class="row mb-2 mx-0">
                            <div class="col-12">
                              <div class="input-group d-flex">
                                <span class="input-group-text" id="labelHost">Server Host</span>
                                <input type="text" name="serverHost" value="<?php if(array_key_exists('host',$Server)){ echo $Server['host']; } ?>" class="form-control" placeholder="Host" aria-label="Host" aria-describedby="labelHost">
                              </div>
                            </div>
                          </div>
                          <div class="row mb-2 mx-0">
                            <div class="col-12">
                              <div class="input-group d-flex">
                                <span class="input-group-text" id="labelPort">Server Port</span>
                                <input type="number" name="serverPort" value="<?php if(array_key_exists('port',$Server)){ echo $Server['port']; } ?>" class="form-control" placeholder="Port" aria-label="Port" aria-describedby="labelPort">
                              </div>
                            </div>
                          </div>
                          <div class="row mb-2 mx-0">
                            <div class="col-12">
                              <div class="input-group d-flex">
                                <span class="input-group-text" id="labelEncryption">Server Encryption</span>
                                <select class="form-select" name="serverEncryption" placeholder="Encryption" aria-label="Encryption" aria-describedby="labelEncryption">
                                  <option value="none" <?php if(array_key_exists('encryption',$Server) && $Server['encryption'] === "none"){ echo "selected"; } ?>>None</option>
                                  <option value="ssl" <?php if(array_key_exists('encryption',$Server) && $Server['encryption'] === "ssl"){ echo "selected"; } ?>>SSL</option>
                                  <option value="tls" <?php if(array_key_exists('encryption',$Server) && $Server['encryption'] === "tls"){ echo "selected"; } ?>>TLS</option>
                                </select>
                              </div>
                            </div>
                          </div>
                        <?php } ?>
                        <?php if(isset($Columns['isDefault'])){ ?>
                          <div class="row mb-2 mx-0">
                            <div class="col-12">
                              <div class="input-group d-flex">
                                <span class="input-group-text">Default</span>
                                <input type="radio" class="btn-check" value="1" name="isDefault" id="isDefaultYes" autocomplete="off" <?php if($Object->get('isDefault')){ echo "checked"; } ?>>
                                <label class="btn btn-block btn-outline-primary flex-grow-1" for="isDefaultYes">Yes</label>

                                <input type="radio" class="btn-check" value="0" name="isDefault" id="isDefaultNo" autocomplete="off" <?php if(!$Object->get('isDefault')){ echo "checked"; } ?>>
                                <label class="btn btn-block btn-outline-primary flex-grow-1" for="isDefaultNo">No</label>
                              </div>
                            </div>
                          </div>
                        <?php } ?>
                        <?php if(isset($Columns['isContactInfoDynamic'])){ ?>
                          <div class="row mb-2 mx-0">
                            <div class="col-12">
                              <div class="input-group d-flex">
                                <span class="input-group-text">Dynamic Contact Info</span>
                                <input type="radio" class="btn-check" value="1" name="isContactInfoDynamic" id="isContactInfoDynamicYes" autocomplete="off" <?php if($Object->get('isContactInfoDynamic')){ echo "checked"; } ?>>
                                <label class="btn btn-block btn-outline-primary flex-grow-1" for="isContactInfoDynamicYes">Yes</label>

                                <input type="radio" class="btn-check" value="0" name="isContactInfoDynamic" id="isContactInfoDynamicNo" autocomplete="off" <?php if(!$Object->get('isContactInfoDynamic')){ echo "checked"; } ?>>
                                <label class="btn btn-block btn-outline-primary flex-grow-1" for="isContactInfoDynamicNo">No</label>
                              </div>
                            </div>
                          </div>
                        <?php } ?>
                        <?php if(isset($Columns['isBanned'])){ ?>
                          <div class="row mb-2 mx-0">
                            <div class="col-12">
                              <div class="input-group d-flex">
                                <span class="input-group-text">Banned</span>
                                <input type="radio" class="btn-check" value="1" name="isBanned" id="isBannedYes" autocomplete="off" <?php if($Object->get('isBanned')){ echo "checked"; } ?>>
                                <label class="btn btn-block btn-outline-primary flex-grow-1" for="isBannedYes">Yes</label>

                                <input type="radio" class="btn-check" value="0" name="isBanned" id="isBannedNo" autocomplete="off" <?php if(!$Object->get('isBanned')){ echo "checked"; } ?>>
                                <label class="btn btn-block btn-outline-primary flex-grow-1" for="isBannedNo">No</label>
                              </div>
                            </div>
                          </div>
                        <?php } ?>
                        <?php if(isset($Columns['isActive'])){ ?>
                          <div class="row mb-2 mx-0">
                            <div class="col-12">
                              <div class="input-group d-flex">
                                <span class="input-group-text">Active</span>
                                <input type="radio" class="btn-check" value="1" name="isActive" id="isActiveYes" autocomplete="off" <?php if($Object->get('isActive')){ echo "checked"; } ?>>
                                <label class="btn btn-block btn-outline-primary flex-grow-1" for="isActiveYes">Yes</label>

                                <input type="radio" class="btn-check" value="0" name="isActive" id="isActiveNo" autocomplete="off" <?php if(!$Object->get('isActive')){ echo "checked"; } ?>>
                                <label class="btn btn-block btn-outline-primary flex-grow-1" for="isActiveNo">No</label>
                              </div>
                            </div>
                          </div>
                        <?php } ?>
                        <?php if(isset($Columns['isVerified'])){ ?>
                          <div class="row mb-2 mx-0">
                            <div class="col-12">
                              <div class="input-group d-flex">
                                <span class="input-group-text">Verified</span>
                                <input type="radio" class="btn-check" value="1" name="isVerified" id="isVerifiedYes" autocomplete="off" <?php if($Object->get('isVerified')){ echo "checked"; } ?>>
                                <label class="btn btn-block btn-outline-primary flex-grow-1" for="isVerifiedYes">Yes</label>

                                <input type="radio" class="btn-check" value="0" name="isVerified" id="isVerifiedNo" autocomplete="off" <?php if(!$Object->get('isVerified')){ echo "checked"; } ?>>
                                <label class="btn btn-block btn-outline-primary flex-grow-1" for="isVerifiedNo">No</label>
                              </div>
                            </div>
                          </div>
                        <?php } ?>
                        <?php if(isset($Columns['database'])){ ?>
                          <div class="row mb-2 mx-0">
                            <div class="col-12">
                              <div class="input-group d-flex">
                                <span class="input-group-text">Database</span>
                                <input type="radio" class="btn-check" value="SQL" name="database" id="databaseSQL" autocomplete="off" <?php if($Object->get('database') === "SQL"){ echo "checked"; } ?>>
                                <label class="btn btn-block btn-outline-primary flex-grow-1" for="databaseSQL">SQL</label>

                                <input type="radio" class="btn-check" value="SMTP" name="database" id="databaseSMTP" autocomplete="off" <?php if($Object->get('database') === "SMTP"){ echo "checked"; } ?>>
                                <label class="btn btn-block btn-outline-primary flex-grow-1" for="databaseSMTP">SMTP</label>

                                <input type="radio" class="btn-check" value="IMAP" name="database" id="databaseIMAP" autocomplete="off" <?php if($Object->get('database') === "IMAP"){ echo "checked"; } ?>>
                                <label class="btn btn-block btn-outline-primary flex-grow-1" for="databaseIMAP">IMAP</label>
                              </div>
                            </div>
                          </div>
                        <?php } ?>
                        <?php if(isset($Columns['2FAMethod'])){ ?>
                          <div class="row mb-2 mx-0">
                            <div class="col-12">
                              <div class="input-group d-flex">
                                <span class="input-group-text">2FA Methods</span>
                                <input type="checkbox" class="btn-check" value="sms" name="2FAMethodSMS" id="2FAMethodSMS" autocomplete="off" <?php if(in_array('sms',$Object->get('2FAMethod'))){ echo "checked"; } ?>>
                                <label class="btn btn-block btn-outline-primary flex-grow-1" for="2FAMethodSMS">SMS</label>

                                <input type="checkbox" class="btn-check" value="smtp" name="2FAMethodSMTP" id="2FAMethodSMTP" autocomplete="off" <?php if(in_array('smtp',$Object->get('2FAMethod'))){ echo "checked"; } ?>>
                                <label class="btn btn-block btn-outline-primary flex-grow-1" for="2FAMethodSMTP">SMTP</label>
                              </div>
                            </div>
                          </div>
                        <?php } ?>
                        <input type="hidden" class="d-none" name="csrf" value="<?= $phpCSRF->token() ?>">
                        <input type="hidden" class="d-none" name="id" value="<?= $Object->get('id') ?>">
                        <div class="btn-group w-100 border shadow mb-4">
                          <button type="submit" class="btn btn-block btn-success">Save</button>
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
