<?php
// Initiate Session
session_start();

// Import Auth class into the global namespace
// These must be at the top of your script, not inside a function
use LaswitchTech\coreAuth\Auth;
use LaswitchTech\coreCSRF\CSRF;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Initiate Auth
$Auth = new Auth();

// Initiate CSRF
$CSRF = new CSRF();

//Render
?>
<!doctype html>
<html lang="en" class="h-100 w-100">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <title>Index</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  </head>
  <body class="h-100 w-100">
    <div class="row h-100 w-100 m-0 p-0">
      <?php if($Auth->Authorization->isAuthorized()){ ?>
        <div class="col h-100 m-0 p-0 d-none">
          <div class="container h-100 p-5">
            <div class="d-flex flex-column h-100 align-items-center justify-content-center text-light text-break p-5 rounded-5" style="background-color: rgba(0, 0, 0, 0.7)">
              <?php if(isset($_SESSION)){ ?>
                <p>_SESSION ID: <?= json_encode(session_id(), JSON_PRETTY_PRINT) ?></p>
                <p>_SESSION: <?= json_encode($_SESSION, JSON_PRETTY_PRINT) ?></p>
              <?php } ?>
              <?php if(isset($_COOKIE)){ ?>
                <p>_COOKIE: <?= json_encode($_COOKIE, JSON_PRETTY_PRINT) ?></p>
              <?php } ?>
              <?php if(isset($_POST)){ ?>
                <p>_POST: <?= json_encode($_POST, JSON_PRETTY_PRINT) ?></p>
              <?php } ?>
              <?php if(isset($_REQUEST)){ ?>
                <p>_REQUEST: <?= json_encode($_REQUEST, JSON_PRETTY_PRINT) ?></p>
              <?php } ?>
            </div>
          </div>
        </div>
        <div class="col h-100 m-0 p-0">
          <div class="container h-100">
            <div class="d-flex h-100 row align-items-center justify-content-center">
              <div class="col-7">
                <?php if($Auth->Authentication->isAuthenticated()){ ?>
                  <?php if(!$Auth->Authentication->isVerified()){ ?>
                    <h3 class="mb-4">Account <strong>Verification</strong></h3>
                    <?php if($Auth->Authentication->error()){ ?>
                      <div class="card text-bg-info mb-4">
                        <div class="card-body">
                          <p class="m-0"><?= $Auth->Authentication->error() ?></p>
                        </div>
                      </div>
                    <?php } ?>
                    <form method="post">
                      <div class="form-floating my-3">
                        <input type="text" name="verifiedCode" class="form-control form-control-lg" placeholder="Verification Code" id="2fa">
                        <label for="verifiedCode">Verification Code</label>
                      </div>
                      <input type="hidden" class="d-none" name="csrf" value="<?= $CSRF->token() ?>">
                      <div class="btn-group w-100 border shadow">
                        <button type="submit" name="login" class="btn btn-block btn-primary">Verify</button>
                      </div>
                    </form>
                  <?php } else { ?>
                    <h3>Logged in to <strong>Auth</strong></h3>
                    <p class="mb-4">
                      <p>User: <?= $Auth->Authentication->User->get('username') ?></p>
                    </p>
                    <div class="btn-group w-100 border shadow">
                      <a href="/" class="btn btn-block btn-light">Refresh</a>
                      <a href="manage.php?type=user" class="btn btn-block btn-primary">Management</a>
                      <a href="install.php" class="btn btn-block btn-warning">Re-Install</a>
                      <a href="?logout&csrf=<?= $CSRF->token() ?>" class="btn btn-block btn-primary">Log Out</a>
                    </div>
                  <?php } ?>
                <?php } else { ?>
                  <h3 class="mb-4">Login to <strong>Auth</strong></h3>
                  <?php if($Auth->Authentication->status() > 0 && $Auth->Authentication->status() < 6){ ?>
                    <div class="card text-bg-danger mb-4">
                      <div class="card-body">
                        <?php
                          switch($Auth->Authentication->status()){
                    				case 1:
                    					echo "<p class='m-0'>Your account has been deleted!</p>";
                    					break;
                    				case 2:
                    					echo "<p class='m-0'>Your account has been banned!</p>";
                    					break;
                    				case 3:
                    					echo "<p class='m-0'>Your account has been locked out!</p>";
                              echo "<p class='m-0'>You may try again in 1800 seconds.</p>";
                    					break;
                    				case 4:
                    					echo "<p class='m-0'>You attempted to login too many times.</p>";
                    					break;
                    				case 5:
                    					echo "<p class='m-0'>Your account is not active!</p>";
                    					break;
                    			}
                        ?>
                      </div>
                    </div>
                  <?php } ?>
                  <?php if($Auth->Authentication->error()){ ?>
                    <div class="card text-bg-info mb-4">
                      <div class="card-body">
                        <p class="m-0"><?= $Auth->Authentication->error() ?></p>
                      </div>
                    </div>
                  <?php } ?>
                  <form method="post">
                    <?php if($Auth->Authentication->is2FAReady()){ ?>
                      <div class="form-floating my-3">
                        <input type="text" name="2fa" class="form-control form-control-lg" placeholder="2-Factor Authentication Code" id="2fa">
                        <label for="2fa">2-Factor Authentication Code</label>
                      </div>
                      <input type="hidden" class="d-none" name="csrf" value="<?= $CSRF->token() ?>">
                      <input type="hidden" name="username" autocomplete="username" class="d-none" value="<?= $_POST['username'] ?>">
                      <input type="hidden" name="password" autocomplete="current-password" class="d-none" value="<?= $_POST['password'] ?>">
                      <?php if(isset($_POST['remember'])){ ?>
                        <input type="hidden" name="remember" class="d-none" value="<?= $_POST['remember'] ?>">
                      <?php } ?>
                      <div class="btn-group w-100 border shadow">
                        <button type="submit" name="login" class="btn btn-block btn-primary">Validate</button>
                      </div>
                    <?php } else { ?>
                      <div class="form-floating my-3">
                        <input type="text" name="username" autocomplete="username" class="form-control form-control-lg" placeholder="username@domain.com" id="username">
                        <label for="username">Username</label>
                      </div>
                      <div class="form-floating my-3">
                        <input type="password" name="password" autocomplete="current-password" class="form-control form-control-lg" placeholder="*******************" id="password">
                        <label for="password">Password</label>
                      </div>
                      <div class="form-check my-3 mb-5 form-switch">
                        <input class="form-check-input" style="margin-left: -1.4em; margin-right: 1.4em;transform: scale(1.8);" type="checkbox" role="switch" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                      </div>
                      <input type="hidden" class="d-none" name="csrf" value="<?= $CSRF->token() ?>">
                      <div class="btn-group w-100 border shadow">
                        <a href="install.php" class="btn btn-block btn-light">Install</a>
                        <button type="submit" name="login" class="btn btn-block btn-primary">Log In</button>
                      </div>
                    <?php } ?>
                  </form>
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
    <?= $Auth->Compliance->form() ?>
  </body>
</html>
