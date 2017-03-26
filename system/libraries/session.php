<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Session Class
 *
 */
class Session {


	/**
	* name of flash session vars
	*
	* @var array
	*/
	const ss_vars = '__ss_vars';



	/**
	* The current flash variables detected
	*
	* @var array
	*/
	private $flash_vars=array();

	/**
	 * Configure some default session setting and then start the session.
	 * @param	array	$config
	 * @return	void
	 */
	public function __construct() {
    static $init;

		//session does not run in cli mode
		if(is_cli()) {return;}

    if($init) {return;}
    $this->match_ip			      = config_item('session_match_ip',false,true);			//Require user IP to match?
  	$this->match_fingerprint	= config_item('session_match_fingerprint',false,true);				//Require user agent fingerprint to match?
  	$this->match_token			  = config_item('session_match_token',false,true);			//Require this token to match?
  	$this->session_handler		= config_item('session_handler','file');	//Class to use for storage, FALSE for native php
  	$this->session_table		  = config_item('session_table','sessions');		//If using a DB, what is the table name?
  	$this->session_name		    = config_item('session_name','afro_sessions');	//What should the session be called?
  	$this->session_id			    = config_item('session_id',null);				//Specify a custom ID to use instead of default cookie ID

  	$this->cookie_path			  = config_item('session_cookie_path',null);				//Path to set in session_cookie
  	$this->cookie_domain		  = config_item('session_cookie_domain',null);				//The domain to set in session_cookie
  	$this->cookie_secure		  = config_item('session_cookie_secure',null);				//Should cookies only be sent over secure connections?
  	$this->cookie_httponly		= config_item('session_cookie_httponly',null);				//Only accessible through the HTTP protocol?

  	$this->regenerate			    = config_item('session_regenerate',300,true);				//Update the session every five minutes
  	$this->expiration			    = config_item('session_expiration',7200,true);				//The session expires after 2 hours of non-use
  	$this->gc_probability		  = config_item('session_gc_probability',100,true);				//Chance (in 100) that old sessions will be removed

    $init=true;


		// Configure garbage collection
		ini_set('session.gc_probability', $this->gc_probability);
		ini_set('session.gc_divisor', 100);
		ini_set('session.gc_maxlifetime', $this->expiration);

		// Set the session cookie parameters
		session_set_cookie_params(
			$this->expiration + time(),
			$this->cookie_path,
			$this->cookie_domain,
			$this->cookie_secure,
			$this->cookie_httponly
		);

		// Name the session, this will also be the name of the cookie
		session_name($this->session_name);

		//If we were told to use a specific ID instead of what PHP might find
		if($this->session_id) {
			session_id($this->session_id);
		}

		//Create a session (or get existing session)
		$this->create();


		if($this->has_userdata(self::ss_vars)) {
			$vars=(array) $_SESSION[self::ss_vars];

			foreach($vars as $name) {
				if(isset($_SESSION[$name])) {
					$this->flash_vars["$name"]=$_SESSION[$name];
					unset($_SESSION[$name]);
				}
			}

			$_SESSION[self::ss_vars] = null; //remove the sent flash vars from session
		}
		//var_dump($_SESSION);

	}


	/**
	 * Start the current session, if already started - then destroy and create a new session!
	 * @return void
	 */
	function create() {

		//If this was called to destroy a session (only works after session started)
		//$this->destroy();

		//If there is a class to handle CRUD of the sessions
		if(!empty($this->session_handler) && $this->session_handler!='file') {

			include __DIR__."/session_".$this->session_handler."_handler.php";

			//Load the session handler class
      $name=$this->session_handler.'_session_handler';
			$handler = new $name();

			//Set the expiration and table name for the model
			$handler->expiration = $this->expiration;
			$handler->session_table = $this->session_table;

			// Register non-native driver as the session handler
			session_set_save_handler (
				array($handler, 'open'),
				array($handler, 'close'),
				array($handler, 'read'),
				array($handler, 'write'),
				array($handler, 'destroy'),
				array($handler, 'gc')
			);
		}

		// Start the session!
		session_start();


		//Check the session to make sure it is valid
		if( ! $this->check()) {
			//Destroy invalid session and create a new one
			return $this->create();
		}


	}


	/**
	 * Check the current session to make sure the user is the same (or else create a new session)
	 * @return unknown_type
	 */
	function check() {

		//On creation store the useragent fingerprint
		if(empty($_SESSION['fingerprint'])) {
			$_SESSION['fingerprint'] = $this->generate_fingerprint();

		//If we should verify user agent fingerprints (and this one doesn't match!)
		} elseif($this->match_fingerprint && $_SESSION['fingerprint'] != $this->generate_fingerprint()) {
			return FALSE;
		}

		//If an IP address is present and we should check to see if it matches
		if(isset($_SESSION['ip_address']) && $this->match_ip) {
			//If the IP does NOT match
			if($_SESSION['ip_address'] != ip_address()) {
				return FALSE;
			}
		}

		//Set the users IP Address
		$_SESSION['ip_address'] = ip_address();


		//If a token was given for this session to match
		if($this->match_token) {
			if(empty($_SESSION['token']) OR $_SESSION['token'] != $this->match_token) {
				//Remove token check
				$this->match_token = FALSE;
				return FALSE;
			}
		}

		//Set the session start time so we can track when to regenerate the session
		if(empty($_SESSION['regenerate'])) {
			$_SESSION['regenerate'] = time();

		//Check to see if the session needs to be regenerated
		} elseif($_SESSION['regenerate'] + $this->regenerate < time()) {

			//Generate a new session id and a new cookie with the updated id
			session_regenerate_id();

			//Store new time that the session was generated
			$_SESSION['regenerate'] = time();

		}

		return TRUE;
	}


	/**
	 * Destroys the current session and user agent cookie
	 * @return  void
	 */
	public function destroy() {

		//If there is no session to delete (not started)
		if (session_id() === '') {
			return;
		}

		// Get the session name
		$name = session_name();

		// Destroy the session
		session_destroy();

		// Delete the session cookie (if exists)
		if (isset($_COOKIE[$name])) {

			//Get the current cookie config
			$params = session_get_cookie_params();

			// Delete the cookie from globals
			unset($_COOKIE[$name]);

			//Delete the cookie on the user_agent
			setcookie($name, '', time()-43200, $params['path'], $params['domain'], $params['secure']);
		}
	}


	/**
	 * Generates key as protection against Session Hijacking & Fixation. This
	 * works better than IP based checking for most sites due to constant user
	 * IP changes (although this method is not as secure as IP checks).
	 * @return string
	 */
	function generate_fingerprint()  {
		//We don't use the ip-adress, because it is subject to change in most cases
		foreach(array('ACCEPT_CHARSET', 'ACCEPT_ENCODING', 'ACCEPT_LANGUAGE', 'USER_AGENT') as $name) {
			$key[] = empty($_SERVER['HTTP_'. $name]) ? NULL : $_SERVER['HTTP_'. $name];
		}
		//Create an MD5 has and return it
		return md5(implode("\0", $key));
	}


	public function userdata($item)
	{
		return $this->$item;
	}

	public function set_userdata($data)
	{
		if(is_array($data)) {
			foreach($data as $key=>$value) {
				$_SESSION["$key"]=$value;
			}
		}
	}


	public function unset_userdata($data)
	{
		if(is_array($data)) {
			foreach($data as $value) {
				$this->unset_userdata($value);
			}
		} else if(is_string($data)){
			if(isset($_SESSION["$data"])) {unset($_SESSION["$data"]);}
		}
	}


	public function mark_as_flash($data)
	{
			if(is_array($data)) {
				foreach($data as $value) {
					$this->mark_as_flash($value);
				}
			} else if(is_string($data)) {

				$mkey=self::ss_vars;
				if($this->has_userdata($mkey)) {
					$vars=(array) $_SESSION[$mkey];
				} else {
					$vars=array();
				}
				if(!in_array($data,$vars)) {$vars[]=$data;}
				$_SESSION[$mkey]=$vars;
			}
	}

	public function set_flashdata($name,$value=null)
	{
		if(is_array($name)) {
			foreach($name as $key=>$value) {
			$this->set_flashdata($key,$value);
			}
		} else {
		$_SESSION["$name"]=$value;
		$this->mark_as_flash($name);
		}

	}

	public function flashdata($name = null)
	{
		if(!is_null($name)) {
			return isset($this->flash_vars["$name"]) ? $this->flash_vars["$name"] : null;
		}

		return $this->flash_vars;
	}

	public function keep_flashdata($data)
	{
		if(is_array($data)) {
			foreach($data as $value) {
				$this->keep_flashdata($value);
			}
		} else if(is_string($data)) {
			if(!is_null($value=$this->flashdata($data))) {
				$_SESSION["$data"]=$value;
				$this->mark_as_flash($data);
			}
		}
	}

	public function has_userdata($key)
	{
		return isset($_SESSION["$key"]);
	}

	public function sess_destroy() {
		session_destroy();
	}


	/**
	* __get
	*
	* reroute variables in the framework
	*
	* @param $key the name of the required resource
	*
	* @return mixed
	*/
	public function __get($key)
	{
		return isset($_SESSION["$key"]) ? $_SESSION["$key"] : null;
	}

}
