<?php

// Declaring namespace
namespace LaswitchTech\coreAuth\Types;

// Import additionnal class into the global namespace
use LaswitchTech\coreConfigurator\Configurator;
use LaswitchTech\coreDatabase\Database;
use LaswitchTech\coreLogger\Logger;
use LaswitchTech\coreCSRF\CSRF;
use Exception;

// Import Sub-Namespaces class into the global namespace
use LaswitchTech\coreAuth\Objects\User;

class Request {

	// core Modules
	private $Configurator;
  	private $Database;
    private $Logger;
    private $CSRF;

	// Auth Switches
    private $Ready = false;

    /**
     * Create a new Request instance.
     *
     * @param  Object  $Database
     * @return void
     * @throws Exception
     */
    public function __construct($Database = null) {

        // Initialize Configurator
        $this->Configurator = new Configurator('auth');

        // Initiate Logger
        $this->Logger = new Logger('auth');

        // Initiate CSRF
        $this->CSRF = new CSRF();

        // Initiate phpDB
        $this->Database = $Database;
        if($this->Database === null){
            $this->Database = new Database();
        }
    }

    /**
     * get Request Credentials.
     *
     * @return array|null
     * @throws Exception
     */
    private function getRequestCredentials() {
        try{

            // Check if _REQUEST exist and if the header contains the 'username', 'password' and 'csrf' token
            if(isset($_REQUEST,$_REQUEST['username'],$_REQUEST['password'],$_REQUEST['csrf'])){

                // CSRF Protection Validation
                if($this->CSRF->validate()){

                    // Return the username and password
                    return [ "username" => $_REQUEST['username'], "password" => $_REQUEST['password'] ];
                }
            }

            // Return null if no credentials are found
            return;
        } catch (Exception $e) {

            // If an exception is caught, log an error message and return null
            $this->Logger->error('Error: '.$e->getMessage());
            return;
        }
    }

    /**
     * Check if 2FA is ready to be received.
     *
     * @return boolean
     */
    public function is2FAReady(){

        // Return
        return $this->Ready;
    }

    /**
     * getAuthentication through Session.
     *
     * @return string
     * @throws Exception
     */
    public function getAuthentication(){
        try {

            // Debug Information
            $this->Logger->debug("Attempting connection using REQUEST");

            // Check if Request Authentication is enabled
            if(!$this->Configurator->get('auth','request')){
                throw new Exception("Request Authentication is Disabled");
            }

            // Retrieve Request Credentials
            $credentials = $this->getRequestCredentials();

            // Check if Credentials were retrieved
            if(!$credentials){
                throw new Exception("Could not find the credentials");
            }

            // Create User Object
            $User = new User($credentials['username'], 'username', $this->Database);

            // Retrieve User
            $result = $User->retrieve();

            // Check if User was found
            if(!$result){
                throw new Exception("Could not find the user");
            }

            // Check if user is isLockedOut
            if($User->isLockedOut()){
                throw new Exception("User is currently locked out");
            }

            // Check if user is isLockedOut
            if($User->isRateLimited()){
                throw new Exception("User has reached the limit of attempts");
            }

            // Record Authentication Attempt
            $User->recordAttempt();

            // If 2FA is enable verify the code
            if($this->Configurator->get('auth','2fa')){

                // Check if the header contains the 2FA code
                if(isset($_REQUEST['2fa']) && !empty($_REQUEST['2fa'])){

                    // Validate 2FA Code
                    if(!$User->validate2FACode($_REQUEST['2fa'])){

                        // Return
                        return false;
                    }
                } else {

                    // Send 2FA Code
                    if($User->send2FACode()){

                        // Set Ready
                        $this->Ready = true;
                    }

                    // Return
                    return false;
                }
            }

            // Validate Password
            if(!$User->validate($credentials['password'])){
                throw new Exception("Wrong password");
            }

            // Clear Any LogOut request
            if(isset($_REQUEST['logout'])){
                unset($_REQUEST['logout']);
            }
            if(isset($_REQUEST['signout'])){
                unset($_REQUEST['signout']);
            }

            // Return the User Object
            return $User;
        } catch (Exception $e) {

            // If an exception is caught, log an error message
            $this->Logger->error('Error: '.$e->getMessage());
            return false;
        }
    }
}
