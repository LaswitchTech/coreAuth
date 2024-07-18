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

class Cookie {

	// core Modules
	private $Configurator;
  	private $Database;
    private $Logger;

    // Properties
    private $Options;
    private $Categories = [
        "Essentials",
        "Performance",
        "Quality",
        "Advertisement",
        "Personalisations",
    ];

    /**
     * Create a new Cookie instance.
     *
     * @param  Object  $Database
     * @return void
     * @throws Exception
     */
    public function __construct($Database = null) {

        // Initialize Configurator
        $this->Configurator = new Configurator('auth');

        // Initiate phpLogger
        $this->Logger = new Logger('auth');

        // Initiate Database
        $this->Database = $Database;
        if(!$this->Database){
            $this->Database = new Database();
        }

        // Set default cookie options
        $this->Options = [
            'secure' => true,
            'httponly' => false,
            'path' => '/',
            'domain' => null,
            'samesite' => 'Strict',
            'expires' => time() + 60*60*24*30,
            'category' => 'Essentials',
            'force' => false,
        ];
    }

    /**
     * get Session Id.
     *
     * @return string
     * @throws Exception
     */
    private function getId(){
        try {

            // Initialize Id
            $Id = null;

            // Retrieve Session ID
            if(isset($_COOKIE,$_COOKIE['sessionId'])){
                $Id = $_COOKIE['sessionId'];
            }

            return $Id;
        } catch (Exception $e) {

            // If an exception is caught, log an error message
            $this->Logger->error('Error: '.$e->getMessage());
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
            $this->Logger->debug("Attempting connection using COOKIE");

            // Check if Cookie Authentication is enabled
            if(!$this->Configurator->get('auth','cookie')){
                throw new Exception("Cookie Authentication is Disabled");
            }

            // Retrieve sessionId
            $sessionId = $this->getId();

            // Validate Session Id
            if(!$sessionId){
                return false;
            }

            // Find an Active Session
            $Session = $this->Database->select("SELECT * FROM sessions WHERE sessionId = ?", [$sessionId]);

            // Validate Session
            if(!isset($Session[0])){
                return false;
            }

            // Identify Session
            $Session = $Session[0];

            // Create User Object
            $User = new User($Session['username'], 'username', $this->Database);

            // Retrieve User
            $User->retrieve();

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

            // Return the User Object
            return $User;
        } catch (Exception $e) {

            // If an exception is caught, log an error message
            $this->Logger->error('Error: '.$e->getMessage());
            return false;
        }
    }

    /**
     * Set Cookie.
     *
     * @param  string             $name     The name of the cookie to set.
     * @param  string|array|null  $data     The value of the cookie, as a string or array.
     * @param  array|null         $options  An array of optional settings for the cookie.
     * @return string                       Returns null on failure, otherwise returns true if the cookie that was set.
     * @throws Exception                    Throws an exception if an error occurs.
     */
    public function set($name, $data = null, $options = []){
        try{
            // Get the default cookie options from the class's CookieOptions property.
            $defaults = $this->Options;

            // Override any default options with any options that were passed in.
            foreach ($options as $key => $value) {
                if(isset($defaults[$key])){
                    $defaults[$key] = $value;
                }
            }

            // Ensure that certain options have the correct type.
            $defaults['expires'] = intval($defaults['expires']);
            $defaults['path'] = strval($defaults['path']);
            $defaults['domain'] = strval($defaults['domain']);
            $defaults['secure'] = boolval($defaults['secure']);
            $defaults['httponly'] = boolval($defaults['httponly']);
            $defaults['category'] = strval($defaults['category']);
            $defaults['force'] = boolval($defaults['force']);

            // If no value was passed in, set the data to an empty string.
            if($data == null){ $data = ''; }

            // if category exist
            if(!in_array($defaults['category'],$this->Categories)){
                throw new Exception("This category of cookie is not supported");
            }

            // Debug Information
            $this->Logger->debug(!$defaults['force']);
            $this->Logger->debug(!isset($_COOKIE,$_COOKIE['cookiesAccept'.$defaults['category']]));

            // if category exist
            if(!$defaults['force'] && isset($_COOKIE,$_COOKIE['cookiesAccept'.$defaults['category']])){
                return null;
            }

            // If the value passed in is an array, convert it to a JSON string.
            if(is_array($data)){ $data = json_encode($data,JSON_UNESCAPED_SLASHES); }

            // Unset unsupported options
            unset($defaults['force']);
            unset($defaults['category']);

            // Set the cookie using the setcookie function.
            setcookie($name, $data, $defaults);

            // Return true if completed
            return true;
        } catch (Exception $e) {

            // If an exception is caught, log an error message and return null.
            $this->Logger->error('Error: '.$e->getMessage());
            return;
        }
    }

    /**
     * Save Cookie.
     *
     * @param  Object  $User
     * @return boolean
     * @throws Exception
     */
    Public function save($User){
        try{

            // Check if User contains an object
            if(!$User){
                throw new Exception("This User Object does not contain anything");
            }

            // Build Cookies
            if(!isset($_COOKIE['sessionID'])){ $this->set( "sessionId", $_SESSION['sessionId'], ['expires' => $_SESSION['timestamp']] ); }
            if(!isset($_COOKIE['timestamp'])){ $this->set( "timestamp", $_SESSION['timestamp'], ['expires' => $_SESSION['timestamp']] ); }

            // Return true if completed
            return true;
        } catch (Exception $e) {

            // If an exception is caught, log an error message
            $this->Logger->error('Error: '.$e->getMessage());
            return false;
        }
    }

    /**
     * Clear Cookies.
     *
     * @return boolean
     * @throws Exception
     */
    Public function clear(){
        try{

            // clear cookie variables
            $options = $this->Options;
            $options['expires'] = -1;
            if(isset($_SERVER['HTTP_COOKIE'])){
                $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
                foreach($cookies as $cookie){
                    $parts = explode('=', $cookie);
                    $name = trim($parts[0]);
                    unset($_COOKIE[$name]);
                    $this->set($name, null, ['expires' => -1]);
                    $this->set($name, null, ['expires' => -1]);
                }
            }

            // return true if all was successfull
            return true;
        } catch (Exception $e) {

            // If an exception is caught, log an error message
            $this->Logger->error('Error: '.$e->getMessage());
            return false;
        }
    }
}
