<?php

// Declaring namespace
namespace LaswitchTech\coreAuth;

// Import additionnal class into the global namespace
use LaswitchTech\coreConfigurator\Configurator;
use LaswitchTech\coreLogger\Logger;
use Exception;

class Authorization {

	// core Modules
	private $Logger;
	private $Configurator;

	// Auth Objects
	private $User;
	private $Groups = [];
	private $Roles = [];
	private $Relationships = [];
	private $Hostnames = [];

	// Auth Switches
	private $Override = false;

	/**
	 * Create a new Authentication instance.
	 *
	 * @param  Object  $User
	 * @param  Object  $Logger
	 * @return void
	 */
	public function __construct($User, $Override = false){

		// Initialize Logger
		$this->Logger = new Logger('auth');

		// Initialize Configurator
		$this->Configurator = new Configurator('auth');

		// Retrieve Hostnames
		$this->Hostnames = $this->Configurator->get('auth', 'hostnames') ?: $this->Hostnames;

		// Initialize User
		$this->User = $User;

		// Initialize Override
		$this->Override = $Override;
	}

	/**
	 * Verify if the user access through this hostname.
	 *
	 * @return boolean
	 */
	public function isAuthorized(){

		// Check if Override is enabled
		if($this->Override){

			// Debug Information
			$this->Logger->debug("Override enabled");

			// Log request
			if($this->User){
				$this->Logger->warning("User [" . $this->User->get('username') . "] is requesting access through {$Hostname} with Override enabled");
			}

			// Return
			return true;
		}

		// Initiate Hostname
		$Hostname = 'localhost';

		// Retrieve Hostname
		if(isset($_SERVER['SERVER_NAME'])){
			$Hostname = $_SERVER['SERVER_NAME'];
		}
		if(isset($_SERVER['HTTP_HOST'])){
			$Hostname = $_SERVER['HTTP_HOST'];
		}

		// Log request
		if($this->User){
			$this->Logger->info("User [" . $this->User->get('username') . "] is requesting access through {$Hostname}");
		}

		// Validate Hostname
		if(in_array($Hostname,$this->Hostnames)){

			// Log request
			if($this->User){
				$this->Logger->success("User [" . $this->User->get('username') . "] was granted access through {$Hostname}");
			}

			// Return
			return true;
		} else {

			// Log request
			if($this->User){
				$this->Logger->error("User [" . $this->User->get('username') . "] was denied access through {$Hostname}");
			}

			// Return
			return false;
		}
	}

	/**
	 * Verify if the user has a specific permission.
	 *
	 * @return boolean
	 */
	public function hasPermission($permissionName, $requiredLevel = 1){

		// Check if Override is enabled
		if($this->Override){

			// Debug Information
			$this->Logger->debug("Override enabled");

			// Log request
			if($this->User){
				$this->Logger->warning("User [" . $this->User->get('username') . "] is requesting access through {$Hostname} with Override enabled");
			}

			// Return
			return true;
		}

		if($this->User === null){

			// User should not have access
			return false;
		}

		if(!$this->User->isVerified() || $this->User->isDeleted()){

			// User should not have access
			return false;
		}

		// Retrieve the Relationships if not already done
		if(!$this->Relationships){
			$this->Relationships = $this->User->get('relationships', true);
		}

		// Retrieve the Roles if not already done
		if(!$this->Roles && isset($this->Relationships['roles'])){
			$this->Roles = $this->Relationships['roles'];
		}

		// Retrieve the Groups if not already done
		if(!$this->Groups && isset($this->Relationships['groups'])){
			$this->Groups = $this->Relationships['groups'];

			// Retrieve the Roles of each Group
			foreach($this->Groups as $Id => $Group){
				$Relationships = $Group->get('relationships', true);

				// If Role is not already present, add it
				if(isset($Relationships['roles'])){
					foreach($Relationships['roles'] as $Rid => $Role){
						if(!isset($this->Roles['roles'][$Rid])){
							$this->Roles['roles'][$Rid] = $Role;
						}
					}
				}
			}
		}

		// Log request
		$this->Logger->info("User [" . $this->User->get('username') . "] is requesting ({$permissionName})");

		// Check permissions in user roles
		foreach ($this->Roles as $RoleId => $Role) {

			// Assign Role Object
			$Object = $Role;

			// Check if Object is an array
			if(is_array($Role)){

				// Create a new Role Object
				$Object = $Role[array_key_first($Role)];
			}

			// Retrieve Roles's Permissions
			$permissions = $Object->get('permissions');

			// Debug Information
			$this->Logger->debug($permissions);

			// Validate Permission Level
			if (isset($permissions[$permissionName]) && $permissions[$permissionName] >= $requiredLevel) {

				// Log request
				$this->Logger->success("User [" . $this->User->get('username') . "] is requesting ({$permissionName}) and was granted access");

				// Return
				return true;
			}
		}

		// Log request
		$this->Logger->error("User [" . $this->User->get('username') . "] is requesting ({$permissionName}) and was denied access");

		// Return
        return false;
	}
}
