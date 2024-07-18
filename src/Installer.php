<?php

// Declaring namespace
namespace LaswitchTech\coreAuth;

// Import additionnal class into the global namespace
use Exception;

// Import Sub-Namespaces class into the global namespace
use LaswitchTech\coreAuth\Objects\User;

class Installer {

	// core Modules
	private $Logger;
    private $Database;

    /**
     * Create a new Installer instance.
     *
     * @param  Object  $Database
     * @param  Object  $Logger
     * @param  Object  $CSRF
     * @return void
     * @throws Exception
     */
    public function __construct($Database){

        // Initialize Logger
        $this->Logger = new Logger('auth');

        // Initialize Database
        $this->Database = $Database;

        // Initialize Installer
        $this->init();
    }

    /**
     * Init Library.
     *
     * @return void
     * @throws Exception
     */
    private function init(){
        try{

            // Check if Database is Connected
            if(!$this->Database->isConnected()){
                throw new Exception("Database is not connected.");
            }

            // Drop Existing Tables
            $this->Database->drop('organizations');
            $this->Database->drop('users');
            $this->Database->drop('sessions');
            $this->Database->drop('groups');
            $this->Database->drop('roles');
            $this->Database->drop('permissions');
            $this->Database->drop('relationships');

            // Create Tables
            $this->Database->create('organizations',[
                'id' => [
                    'type' => 'BIGINT(10)',
                    'extra' => ['UNSIGNED','AUTO_INCREMENT','PRIMARY KEY']
                ],
                'created' => [
                    'type' => 'DATETIME',
                    'extra' => ['NOT NULL','DEFAULT CURRENT_TIMESTAMP']
                ],
                'modified' => [
                    'type' => 'DATETIME',
                    'extra' => ['NOT NULL','DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
                ],
                'name' => [
                    'type' => 'VARCHAR(60)',
                    'extra' => ['NOT NULL','UNIQUE']
                ],
                'sbnr/ein' => [
                    'type' => 'VARCHAR(60)',
                    'extra' => ['NULL','UNIQUE']
                ],
                'address' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'city' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'state' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'country' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'zipcode' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'email' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'fax' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'phone' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'tollfree' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'website' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'domain' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'database' => [
                    'type' => 'VARCHAR(10)',
                    'extra' => ['NOT NULL','DEFAULT "SQL"']
                ],
                'server' => [
                    'type' => 'JSON',
                    'extra' => ['NULL']
                ],
                'isSubsidiary' => [
                    'type' => 'INT(1)',
                    'extra' => ['NOT NULL','DEFAULT "0"']
                ],
                'isDeleted' => [
                    'type' => 'INT(1)',
                    'extra' => ['NOT NULL','DEFAULT "0"']
                ],
                'isActive' => [
                    'type' => 'INT(1)',
                    'extra' => ['NOT NULL','DEFAULT "0"']
                ],
            ]);
            $this->Database->create('users',[
                'id' => [
                    'type' => 'BIGINT(10)',
                    'extra' => ['UNSIGNED','AUTO_INCREMENT','PRIMARY KEY']
                ],
                'created' => [
                    'type' => 'DATETIME',
                    'extra' => ['NOT NULL','DEFAULT CURRENT_TIMESTAMP']
                ],
                'modified' => [
                    'type' => 'DATETIME',
                    'extra' => ['NOT NULL','DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
                ],
                'username' => [
                    'type' => 'VARCHAR(60)',
                    'extra' => ['NOT NULL','UNIQUE']
                ],
                'passwordSalt' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'passwordHash' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                '2FASalt' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                '2FAHash' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'last2FA' => [
                    'type' => 'TIMESTAMP',
                    'extra' => ['NULL']
                ],
                '2FAMethod' => [
                    'type' => 'JSON',
                    'extra' => ['NULL']
                ],
                'bearerToken' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL','UNIQUE']
                ],
                'name' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'address' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'city' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'state' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'country' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'zipcode' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'phone' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'mobile' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'status' => [
                    'type' => 'INT(1)',
                    'extra' => ['NOT NULL','DEFAULT "0"']
                ],
                'database' => [
                    'type' => 'VARCHAR(10)',
                    'extra' => ['NOT NULL','DEFAULT "SQL"']
                ],
                'server' => [
                    'type' => 'JSON',
                    'extra' => ['NULL']
                ],
                'domain' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'sessionId' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'sessionId' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'attempts' => [
                    'type' => 'INT(1)',
                    'extra' => ['NOT NULL','DEFAULT "0"']
                ],
                'lastAttempt' => [
                    'type' => 'TIMESTAMP',
                    'extra' => ['NULL']
                ],
                'requests' => [
                    'type' => 'INT(10)',
                    'extra' => ['NOT NULL','DEFAULT "1"']
                ],
                'lastRequest' => [
                    'type' => 'TIMESTAMP',
                    'extra' => ['NULL']
                ],
                'isActive' => [
                    'type' => 'INT(1)',
                    'extra' => ['NOT NULL','DEFAULT "0"']
                ],
                'isVerified' => [
                    'type' => 'INT(1)',
                    'extra' => ['NOT NULL','DEFAULT "0"']
                ],
                'verifiedSalt' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'verifiedHash' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'verifiedOn' => [
                    'type' => 'DATETIME',
                    'extra' => ['NULL']
                ],
                'verifiedUntil' => [
                    'type' => 'TIMESTAMP',
                    'extra' => ['NULL']
                ],
                'isBanned' => [
                    'type' => 'INT(1)',
                    'extra' => ['NOT NULL','DEFAULT "0"']
                ],
                'isDeleted' => [
                    'type' => 'INT(1)',
                    'extra' => ['NOT NULL','DEFAULT "0"']
                ],
                'isAPI' => [
                    'type' => 'INT(1)',
                    'extra' => ['NOT NULL','DEFAULT "0"']
                ],
                'isContactInfoDynamic' => [
                    'type' => 'INT(1)',
                    'extra' => ['NOT NULL','DEFAULT "1"']
                ],
            ]);
            $this->Database->create('sessions',[
                'id' => [
                    'type' => 'BIGINT(10)',
                    'extra' => ['UNSIGNED','AUTO_INCREMENT','PRIMARY KEY']
                ],
                'created' => [
                    'type' => 'DATETIME',
                    'extra' => ['NOT NULL','DEFAULT CURRENT_TIMESTAMP']
                ],
                'modified' => [
                    'type' => 'DATETIME',
                    'extra' => ['NOT NULL','DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
                ],
                'sessionId' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NOT NULL','UNIQUE']
                ],
                'username' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NOT NULL','UNIQUE']
                ],
                'userAgent' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'userBrowser' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'userIP' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NULL']
                ],
                'userConsent' => [
                    'type' => 'JSON',
                    'extra' => ['NULL']
                ],
                'userActivity' => [
                    'action' => 'ADD',
                    'type' => 'DATETIME',
                    'extra' => ['NOT NULL','DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
                ],
            ]);
            $this->Database->create('groups',[
                'id' => [
                    'type' => 'BIGINT(10)',
                    'extra' => ['UNSIGNED','AUTO_INCREMENT','PRIMARY KEY']
                ],
                'created' => [
                    'type' => 'DATETIME',
                    'extra' => ['NOT NULL','DEFAULT CURRENT_TIMESTAMP']
                ],
                'modified' => [
                    'type' => 'DATETIME',
                    'extra' => ['NOT NULL','DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
                ],
                'name' => [
                    'type' => 'VARCHAR(60)',
                    'extra' => ['NOT NULL','UNIQUE']
                ],
                'description' => [
                    'type' => 'TEXT',
                    'extra' => ['NULL']
                ],
                'isDefault' => [
                    'type' => 'INT(1)',
                    'extra' => ['NOT NULL','DEFAULT "0"']
                ],
            ]);
            $this->Database->create('roles',[
                'id' => [
                    'type' => 'BIGINT(10)',
                    'extra' => ['UNSIGNED','AUTO_INCREMENT','PRIMARY KEY']
                ],
                'created' => [
                    'type' => 'DATETIME',
                    'extra' => ['NOT NULL','DEFAULT CURRENT_TIMESTAMP']
                ],
                'modified' => [
                    'type' => 'DATETIME',
                    'extra' => ['NOT NULL','DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
                ],
                'name' => [
                    'type' => 'VARCHAR(60)',
                    'extra' => ['NOT NULL','UNIQUE']
                ],
                'description' => [
                    'type' => 'TEXT',
                    'extra' => ['NULL']
                ],
                'permissions' => [
                    'type' => 'JSON',
                    'extra' => ['NULL']
                ],
                'isDefault' => [
                    'type' => 'INT(1)',
                    'extra' => ['NOT NULL','DEFAULT "0"']
                ],
            ]);
            $this->Database->create('permissions',[
                'id' => [
                    'type' => 'BIGINT(10)',
                    'extra' => ['UNSIGNED','AUTO_INCREMENT','PRIMARY KEY']
                ],
                'created' => [
                    'type' => 'DATETIME',
                    'extra' => ['NOT NULL','DEFAULT CURRENT_TIMESTAMP']
                ],
                'modified' => [
                    'type' => 'DATETIME',
                    'extra' => ['NOT NULL','DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
                ],
                'name' => [
                    'type' => 'VARCHAR(60)',
                    'extra' => ['NOT NULL','UNIQUE']
                ],
                'description' => [
                    'type' => 'TEXT',
                    'extra' => ['NULL']
                ],
                'level' => [
                    'type' => 'INT(1)',
                    'extra' => ['NOT NULL','DEFAULT "0"']
                ],
            ]);
            $this->Database->create('relationships',[
                'id' => [
                    'type' => 'BIGINT(10)',
                    'extra' => ['UNSIGNED','AUTO_INCREMENT','PRIMARY KEY']
                ],
                'created' => [
                    'type' => 'DATETIME',
                    'extra' => ['NOT NULL','DEFAULT CURRENT_TIMESTAMP']
                ],
                'modified' => [
                    'type' => 'DATETIME',
                    'extra' => ['NOT NULL','DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
                ],
                'sourceTable' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NOT NULL']
                ],
                'sourceId' => [
                    'type' => 'BIGINT(10)',
                    'extra' => ['NOT NULL']
                ],
                'targetTable' => [
                    'type' => 'VARCHAR(255)',
                    'extra' => ['NOT NULL']
                ],
                'targetId' => [
                    'type' => 'BIGINT(10)',
                    'extra' => ['NOT NULL']
                ],
            ],[
                'uniqueRelationship' => [
                    'sourceTable',
                    'sourceId',
                    'targetTable',
                    'targetId',
                ],
            ]);
        } catch (Exception $e) {

            // If an exception is caught, log an error message
            $this->Logger->error('Error: '.$e->getMessage());
        }
	}

    /**
     * Init Library.
     *
     * @param string $Type
     * @param array  $Data
     * @return void
     * @throws Exception
     */
	public function create($Type, $Data){
		try{

			// Validate Type
			if(!is_string($Type)){
				throw new Exception("Wrong data type for Type.");
			}

			// Sanitize Type
			$Type = strtoupper($Type);

			// Validate Data
			if(!is_array($Data)){
				throw new Exception("Wrong data type for Data.");
			}

			// Iterate through available Object Types
			switch($Type){
				case"API":

					// Check that a username was provided
					if(!isset($Data['username'])){
						throw new Exception("No username provided.");
					}

					// Create a User Object
					$User = new User($Data['username'], 'username', $this->Logger, $this->Database);

					// Create the User
					$User->new($Data, true);

					// Return the User Object
					return $User;
					break;
				case"USER":

					// Check that a username was provided
					if(!isset($Data['username'])){
						throw new Exception("No username provided.");
					}

					// Create a User Object
					$User = new User($Data['username'], 'username', $this->Logger, $this->Database);

					// Create the User
					$User->new($Data);

					// Return the User Object
					return $User;
					break;
				default:

					// Throw an exception if type of object does not exist
					throw new Exception("Invalid Type.");
					break;
			}
		} catch (Exception $e) {

			// If an exception is caught, log an error message
            $this->Logger->error('Error: '.$e->getMessage());
        }
	}
}
