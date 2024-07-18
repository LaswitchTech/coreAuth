<?php

// Declaring namespace
namespace LaswitchTech\coreAuth;

// Import additionnal class into the global namespace
use LaswitchTech\coreConfigurator\Configurator;
use LaswitchTech\coreDatabase\Database;
use LaswitchTech\coreLogger\Logger;
use LaswitchTech\coreCSRF\CSRF;
use Exception;

// Import Sub-Namespaces class into the global namespace
use LaswitchTech\coreAuth\Types\Request;
use LaswitchTech\coreAuth\Types\Session;
use LaswitchTech\coreAuth\Types\Cookie;
use LaswitchTech\coreAuth\Types\Bearer;
use LaswitchTech\coreAuth\Types\Basic;

class Authentication {

	// core Modules
	private $Logger;
    private $Configurator;
    private $Database;
    private $CSRF;

	// Auth Modules
    private $Session;
    private $Cookie;
    private $Bearer;
    private $Basic;
    private $Request;

	// Auth Objects
    public $User;
    private $userStatus = 0;

    // Additional Properties
    private $Method;

    /**
     * Create a new Authentication instance.
     *
     * @param  Object  $Database
     * @param  Object  $Logger
     * @param  Object  $CSRF
     * @return void
     * @throws Exception
     */
    public function __construct($Database = null) {

        // Initialize Configurator
        $this->Configurator = new Configurator('auth');

        // Initiate phpLogger
        $this->Logger = new Logger('auth');

        // Initiate CSRF
        $this->CSRF = new CSRF();

        // Initiate Database
        $this->Database = $Database;
        if(!$this->Database){
            $this->Database = new Database();
        }

        // Initialize Library
        return $this->init();
    }

    /**
     * Init Library.
     *
     * @return void
     * @throws Exception
     */
	private function init(){
		try {

			// Check if Database is Connected
			if(!$this->Database->isConnected()){
				throw new Exception("Database is not connected.");
			}

			// Initialize Authentication Modules
			$this->Session = new Session($this->Database);
			$this->Cookie = new Cookie($this->Database);
			$this->Bearer = new Bearer($this->Database);
			$this->Basic = new Basic($this->Database);
			$this->Request = new Request($this->Database);

			// Initialize Authentication
			$this->authenticate();

			// Check if a logout is requested
			if(isset($_REQUEST['logout']) || isset($_REQUEST['signout'])){

				// Check if user is logged in
				if($this->isConnected()){

					// CSRF Protection Validation
					if($this->CSRF->validate()){

						// Logout User
						$this->logout();
					} else {
						throw new Exception("Cross Site Forgery Detected");
					}
				}
			}

			// Return this Object
			return $this;
        } catch (Exception $e) {

            // If an exception is caught, log an error message
            $this->Logger->error('Error: '.$e->getMessage());
        }
	}

    /**
     * Check if 2FA is ready to be received.
     *
     * @return boolean
     */
    public function is2FAReady(){

        // Return
        return $this->Request->is2FAReady();
    }

    /**
     * Check if email is verified.
     *
     * @return boolean
     */
    public function isVerified(){

        // Return
        return ($this->User && $this->User->isVerified());
    }

    /**
     * Handling Authentication.
     *
     * @return void
     * @throws Exception
     */
	private function authenticate(){
		try {

			// Initialize User
			$User = null;

			// Retrieve User
			// by Session
			if(!$User){
				$User = $this->Session->getAuthentication();
				if($User){
					$this->Method = "Session";
				}
			}

			// by Cookie
			if(!$User){
				$User = $this->Cookie->getAuthentication();
				if($User){
					$this->Method = "Cookie";
				}
			}

			// by Bearer
			if(!$User){
				$User = $this->Bearer->getAuthentication();
				if($User){
					$this->Method = "Bearer";
				}
			}

			// by Basic
			if(!$User){
				$User = $this->Basic->getAuthentication();
				if($User){
					$this->Method = "Basic";
				}
			}

			// by Request
			if(!$User){
				$User = $this->Request->getAuthentication();
				if($User){
					$this->Method = "Request";
				}
			}

			// Check if a User was found
			if(!$User){
				throw new Exception("No user found");
			}

			// Save Status
			$this->userStatus = $User->status();

			// Check User Status
			switch($this->userStatus){
				case 1:
					throw new Exception("User is deleted");
					break;
				case 2:
					throw new Exception("User is banned");
					break;
				case 3:
					throw new Exception("User is locked out");
					break;
				case 4:
					throw new Exception("User has reached his limit");
					break;
				case 5:
					throw new Exception("User is inactive");
					break;
			}

			// Reset Attempts
			$User->resetAttempts();

			// Store User
			$this->User = $User;

			// Save Session
			$this->Session->save($this->User);

			// Save Cookies
			$this->Cookie->save($this->User);

            // Redirect User
            $this->redirect();
        } catch (Exception $e) {

            // If an exception is caught, log an error message
            $this->Logger->warning('Failed: '.$e->getMessage());
            return false;
        }
	}

    /**
     * Redirect User.
     *
     * @return boolean
     * @throws Exception
     */
    protected function redirect(){
        if($this->User && $this->Method = "Request"){
            if(isset($_REQUEST['redirect'])){

                // Redirect User
                header('Location: '.$_REQUEST['redirect']);
                exit();
            }
        }
	}

    /**
     * Logout User.
     *
     * @return boolean
     * @throws Exception
     */
    public function logout(){
        try{

            // Validate CSRF Protection
            if(!$this->CSRF->validate()){
                throw new Exception("Request forgery detected");
            }

            // Clear Cookies
            $this->Cookie->clear();

            // Clear Session
            $this->Session->clear($this->User);

            // Clear User
            $this->User = null;

            // Return True
            return true;
        } catch (Exception $e) {

            // If an exception is caught, log an error message
            $this->Logger->warning('Failed: '.$e->getMessage());
            return false;
        }
	}

    /**
     * Retrieve Authentication Error.
     *
     * @return string
     */
	public function error(){

		// Check if User is logged in
		if($this->isConnected()){
			return $this->User->error();
		}
	}

    /**
     * Retrieve Authentication Status.
     *
     * @return int
     */
	public function status(){

		// Check if User is logged in
		if($this->isConnected()){
			return $this->User->status();
		}
	}

	/**
	 * Check if User is connected.
	 *
	 * @return boolean
	 */
	public function isConnected(){
		return ($this->User !== null);
	}

	/**
	 * Check if User is authenticated.
	 *
	 * @return boolean
	 */
	public function isAuthenticated(){
		return ($this->User !== null);
	}
}
