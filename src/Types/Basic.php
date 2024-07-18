<?php

// Declaring namespace
namespace LaswitchTech\coreAuth\Types;

// Import additionnal class into the global namespace
use LaswitchTech\coreConfigurator\Configurator;
use LaswitchTech\coreDatabase\Database;
use LaswitchTech\coreLogger\Logger;
use Exception;

// Import Sub-Namespaces class into the global namespace
use LaswitchTech\coreAuth\Objects\User;

class Basic {

	// core Modules
	private $Configurator;
  	private $Database;
    private $Logger;

    /**
     * Create a new Basic instance.
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

        // Initiate Database
        $this->Database = $Database;
        if(!$this->Database){
            $this->Database = new Database();
        }
    }

    /**
     * get Authentication Header.
     *
     * @return string
     * @throws Exception
     */
    private function getAuthenticationHeader(){
        try{

            // Initialize Headers
            $headers = null;

            // Check if Authorization header exists in $_SERVER array and store it in $headers variable
            if(isset($_SERVER['Authorization'])){

                // Save Headers
                $headers = trim($_SERVER['Authorization']);

            // Check if HTTP_AUTHORIZATION header exists in $_SERVER array and store it in $headers variable
            } else if (isset($_SERVER['HTTP_AUTHORIZATION'])){

                // Save Headers
                $headers = trim($_SERVER['HTTP_AUTHORIZATION']);

            // If apache_request_headers() function exists, use it to retrieve the Authorization header
            } elseif (function_exists('apache_request_headers')){
                $requestHeaders = apache_request_headers();
                $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
                if (isset($requestHeaders['Authorization'])){
                    $headers = trim($requestHeaders['Authorization']);
                }
            }

            // Return the Authorization header, or null if it wasn't found
            return $headers;
        } catch (Exception $e) {

            // If an exception is caught, log an error message and return null
            $this->Logger->error('Error: '.$e->getMessage());
            return;
        }
    }

    /**
     * get Basic Credentials.
     *
     * @return array|null
     * @throws Exception
     */
    private function getBasicCredentials() {
        try{

            // Call the getAuthenticationHeader method to get the headers
            $headers = $this->getAuthenticationHeader();

            // Check if headers exist and if the header contains the 'Basic' keyword and the PHP_AUTH_USER and PHP_AUTH_PW variables are set and not empty
            if (!empty($headers)) {
                if (strpos($headers, 'Basic') !== false && isset($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {

                    // Return the username and password as decoded strings
                    return [ "username" => base64_decode($_SERVER['PHP_AUTH_USER']), "password" => base64_decode($_SERVER['PHP_AUTH_PW']) ];
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
     * getAuthentication through Session.
     *
     * @return string
     * @throws Exception
     */
    public function getAuthentication(){
        try {

            // Debug Information
            $this->Logger->debug("Attempting connection using BASIC");

            // Check if Basic Authentication is enabled
            if(!$this->Configurator->get('auth','basic')){
                throw new Exception("Basic Authentication is Disabled");
            }

            // Retrieve Basic Credentials
            $credentials = $this->getBasicCredentials();

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

            // Validate Password
            if(!$User->validate($credentials['password'])){
                throw new Exception("Wrong password");
            }

            // Check if user is isLockedOut
            if($User->isLockedOut()){
                throw new Exception("API User is currently locked out");
            }

            // Check if user is isLockedOut
            if($User->isRateLimited()){
                throw new Exception("API User has reached the limit of requests");
            }

            // Record Authentication Request
            $User->recordRequest();

            // Return the User Object
            return $User;
        } catch (Exception $e) {

            // If an exception is caught, log an error message
            $this->Logger->error('Error: '.$e->getMessage());
            return false;
        }
    }
}
