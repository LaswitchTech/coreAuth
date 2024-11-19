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
use LaswitchTech\coreAuth\Authentication;
use LaswitchTech\coreAuth\Authorization;
use LaswitchTech\coreAuth\Compliance;
use LaswitchTech\coreAuth\Management;
use LaswitchTech\coreAuth\Installer;

class Auth {

	// core Modules
	private $Configurator;
  	private $Database;
    private $Logger;
  	private $CSRF;

	// Auth Modules
  	public $Installer;
  	public $Authentication;
  	public $Authorization;
  	public $Management;
	public $Compliance;

	// Auth Objects
	public $User;
	public $Organization;

	// Auth Switches
	private $Override = false;

	/**
	 * Create a new Auth instance.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function __construct($Override = false){

		// Initialize Configurator
		$this->Configurator = new Configurator('auth');

		// Initialize Compliance
		$this->Compliance = new Compliance();

		// Initiate Logger
		$this->Logger = new Logger('auth');

		// Initiate CSRF
		$this->CSRF = new CSRF();

		// Override
		$this->Override = $Override;

		// Initialize
		$this->init();
	}

	/**
	 * Configure Library.
	 *
	 * @param  string  $option
	 * @param  bool|int  $value
	 * @return void
	 * @throws Exception
	 */
	public function config($option, $value){
		try {
			if(is_string($option)){
				switch($option){
					case"maxAttempts":
					case"maxRequests":
					case"lockoutDuration":
					case"windowAttempts":
					case"windowRequests":
					case"window2FA":
					case"windowVerification":
						if(is_int($value)){

							// Save to Configurator
							$this->Configurator->set('auth',$option, $value);
						} else{
							throw new Exception("2nd argument must be an integer.");
						}
						break;
					case"basic":
					case"bearer":
					case"request":
					case"cookie":
					case"session":
					case"2fa":
						if(is_bool($value)){

							// Save to Configurator
							$this->Configurator->set('auth',$option, $value);
						} else{
							throw new Exception("2nd argument must be a boolean.");
						}
						break;
					case"hostnames":
						if(is_array($value)){

							// Save to Configurator
							$this->Configurator->set('auth',$option, $value);
						} else{
							throw new Exception("2nd argument must be an array.");
						}
						break;
					default:
						throw new Exception("unable to configure $option.");
						break;
				}
			} else{
				throw new Exception("1st argument must be as string.");
			}
		} catch (Exception $e) {

			// If an exception is caught, log an error message
			$this->Logger->error('Error: '.$e->getMessage());
		}

		return $this;
  	}

	/**
	 * Init Library.
	 *
	 * @return void
	 * @throws Exception
	 */
    private function init(){
		try {

			// Debug Information
			$this->Logger->debug("Initializing");

			//Initiate Database
			$this->Database = new Database();

			// Check if Database is Connected
			if(!$this->Database->isConnected()){
				throw new Exception("Database is not connected.");
			}

			// Initialize Authentication
			$this->Authentication = new Authentication($this->Database);
			if($this->Authentication){

                // Store User
				$this->User = $this->Authentication->User;

                // Check if the user is authenticated
                if($this->Authentication->isAuthenticated()){

                    // Retrieve and Set the User's Organization
                    $this->Organization = $this->User->get('organization',true);
                }
			}

			// Initialize Authorization
			$this->Authorization = new Authorization($this->User, $this->Override);
		} catch (Exception $e) {

			// If an exception is caught, log an error message
			$this->Logger->error('Error: '.$e->getMessage());
		}

		return $this;
	}

	/**
	 * Install Auth and create the database tables required.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function install(){

		// Initialize Installer
		$this->Installer = new Installer($this->Database);

		// Return Installer
		return $this->Installer;
	}

	/**
	 * Manage Auth components.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function manage($Type){

		// Check available types
		if(!in_array($Type,['users','organizations','groups','roles','permissions'])){
			return null;
		}

		// Initialize Manager
		$this->Management = new Management($Type, $this->Database);

		// Return Manager
		return $this->Management;
	}
}
