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

class Session {

	// core Modules
	private $Configurator;
  	private $Database;
    private $Logger;
    private $CSRF;

    /**
     * Create a new Session instance.
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

        // Initiate Database
        $this->Database = $Database;
        if(!$this->Database){
            $this->Database = new Database();
        }

        // Initialize Library
        $this->init();
    }

    /**
     * Init Library.
     *
     * @return void
     * @throws Exception
     */
	private function init(){
		try {

            // Check if a Session was started
            if(session_status() === PHP_SESSION_NONE) {
                throw new Exception("Session is was not started.");
            }

            return true;
        } catch (Exception $e) {

            // If an exception is caught, log an error message
            $this->Logger->error('Error: '.$e->getMessage());
            throw new Exception('Error: '.$e->getMessage());
        }
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
            if(isset($_SESSION,$_SESSION['sessionId'])){
                $Id = $_SESSION['sessionId'];
            } elseif(session_id()){
                $Id = session_id();
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
            $this->Logger->debug("Attempting connection using SESSION");

            // Check if Session Authentication is enabled
            if(!$this->Configurator->get('auth','session')){
                throw new Exception("Session Authentication is Disabled");
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

            // Check if user is verified
            if(!$User->isVerified()){

                // Check if Code was submitted
                if(isset($_REQUEST['verifiedCode'])){

                    // Retrieve Verification Code
                    $verifiedCode = $_REQUEST['verifiedCode'];

                    // Check CSRF
                    if($this->CSRF->validate()){

                        // Validate Verification Code
                        if(!$User->validateVerificationCode($verifiedCode)){
                            throw new Exception("Invalid Verification Code");
                        }
                    }
                }
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
     * Get User Consent.
     *
     * @return string JSON
     * @throws Exception
     */
    private function getUserConsent(){
        try{

            // Retrieve User Consent
            $userConsent = [];

            // Essentials
            if(isset($_COOKIE,$_COOKIE['cookiesAcceptEssentials'])){

                // Set Cookie `cookiesAcceptEssentials`
                $userConsent[] = 'cookiesAcceptEssentials';
            }

            // Performance
            if(isset($_COOKIE,$_COOKIE['cookiesAcceptPerformance'])){

                // Set Cookie `cookiesAcceptPerformance`
                $userConsent[] = 'cookiesAcceptPerformance';
            }

            // Quality
            if(isset($_COOKIE,$_COOKIE['cookiesAcceptQuality'])){

                // Set Cookie `cookiesAcceptQuality`
                $userConsent[] = 'cookiesAcceptQuality';
            }

            // Personalisations
            if(isset($_COOKIE,$_COOKIE['cookiesAcceptPersonalisations'])){

                // Set Cookie `cookiesAcceptPersonalisations`
                $userConsent[] = 'cookiesAcceptPersonalisations';
            }

            // Advertisement
            if(isset($_COOKIE,$_COOKIE['cookiesAcceptAdvertisement'])){

                // Set Cookie `cookiesAcceptAdvertisement`
                $userConsent[] = 'cookiesAcceptAdvertisement';
            }

            // Convert to JSON
            $userConsent = json_encode($userConsent, JSON_UNESCAPED_SLASHES);

            // Return
            return $userConsent;
        } catch (Exception $e) {

            // If an exception is caught, log an error message
            $this->Logger->error('Error: '.$e->getMessage());
        }
    }

    /**
     * Get Client Browser.
     *
     * This function attempts to determine the user's web browser based on the 'HTTP_USER_AGENT'
     * header in the request. If the header cannot be found or is not recognizable, the function
     * returns 'Unknown'.
     *
     * @return string The name of the user's web browser
     * @throws Exception
     */
    private function getClientBrowser(){

        try{

            if(!isset($_SERVER['HTTP_USER_AGENT'])){
                // If no recognizable browser was found, return 'Unknown'
                return 'Unknown';
            }

            // Retrieve the HTTP_USER_AGENT header and convert it to lowercase for easier comparison
            $t = strtolower($_SERVER['HTTP_USER_AGENT']);

            // Append a space to the beginning of the header value to make the code below easier to write
            $t = " " . $t;

            // Check the header value for each browser in turn. If a match is found, return the browser name
            if     (strpos($t, 'opera'     ) || strpos($t, 'opr/')     ) return 'Opera'            ;
            elseif (strpos($t, 'edge'      )                           ) return 'Edge'             ;
            elseif (strpos($t, 'chrome'    )                           ) return 'Chrome'           ;
            elseif (strpos($t, 'safari'    )                           ) return 'Safari'           ;
            elseif (strpos($t, 'firefox'   )                           ) return 'Firefox'          ;
            elseif (strpos($t, 'msie'      ) || strpos($t, 'trident/7')) return 'Internet Explorer';

            // If no recognizable browser was found, return 'Unknown'
            return 'Unknown';

        } catch (Exception $e) {

            // If an exception is caught, log an error message and return 'Unknown'
            $this->Logger->error('Error: '.$e->getMessage());
            return 'Unknown';
        }
    }

    /**
     * Save Session.
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

            // Find all user Sessions to be purged
            $Sessions = $this->Database->select("SELECT * FROM sessions WHERE username = ? AND sessionId != ?", [$User->get('username'),session_id()]);

            // Delete any existing sessions that do not match with the session id
            foreach($Sessions as $session){
                $this->Database->delete("DELETE FROM sessions WHERE id = ?", [$session['id']]);
            }

            // Find an active session
            $Sessions = $this->Database->select("SELECT * FROM sessions WHERE username = ? AND sessionId = ?", [$User->get('username'),session_id()]);

            // Check if an active session was found
            if(count($Sessions) > 0){

                // Identify Session
                $Session = $Sessions[0];

                // Update the session
                $this->Database->update("UPDATE sessions SET userAgent = ?, userBrowser = ?, userIP = ?, userConsent = ? WHERE sessionId = ?", [$this->Logger->agent(),$this->getClientBrowser(),$this->Logger->ip(),$this->getUserConsent(),session_id()]);
            } else {

                // Create the session
                $this->Database->insert("INSERT INTO sessions (sessionId,username,userAgent,userBrowser,userIP,userConsent) VALUES (?,?,?,?,?,?)", [session_id(),$User->get('username'),$this->Logger->agent(),$this->getClientBrowser(),$this->Logger->ip(),$this->getUserConsent()]);
            }

            // Update the session id
            $User->save('sessionId', session_id());

            // Build Session
            // Save Session Id
            $_SESSION['sessionId'] = session_id();

            // Save Timestamp
            if(isset($_REQUEST['remember'])){
                $_SESSION['timestamp'] = time() + (86400 * 30);
            } else {
                $_SESSION['timestamp'] = time();
            }

            // Return true if completed
            return true;
        } catch (Exception $e) {

            // If an exception is caught, log an error message
            $this->Logger->error('Error: '.$e->getMessage());
            return false;
        }
    }

    /**
     * Clear Session.
     *
     * @param  Object  $User
     * @return boolean
     * @throws Exception
     */
    Public function clear($User){
        try{

            // Delete stored session
            $this->Database->delete("DELETE FROM sessions WHERE username = ?", [$User->get('username')]);

            // clear session variables
            if(isset($_SESSION) && !empty($_SESSION)){
                foreach($_SESSION as $key => $value){ unset($_SESSION[$key]); }
            }

            // remove all session variables
            session_unset();

            // destroy the session
            session_destroy();

            // start a new session
            session_start();

            // return true if all was successfull
            return true;
        } catch (Exception $e) {

            // If an exception is caught, log an error message
            $this->Logger->error('Error: '.$e->getMessage());
            return false;
        }
    }
}
