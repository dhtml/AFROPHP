<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Session dbase Class
 *
 */

 /**
 * Default session handler for storing sessions in the database. Can use
 * any type of database from SQLite to MySQL. If you wish to use your own
 * class instead of this one please set session::$session_handler to
 * the name of your class (see session class). If you wish to use memcache
 * then then set the session::$session_handler to FALSE and configure the
 * settings shown in http://php.net/manual/en/memcache.examples-overview.php
 */
class dbase_session_handler  extends model {

	/**
	* the table holding the session
	*/
  public $table;

  /**
	* Store the starting session ID so we can check against current id at close
	*/
	public $session_id		= NULL;

	/**
	* Table to look for session data in
	*/
	public $session_table	= NULL;

	/**
	* How long are sessions good?
	*/
	public $expiration		= NULL;


	/**
	* session database handler class constructor
	*/
  public function __construct()
  {
  	$this->table		          =  '{' . config_item('session_table','sessions') . '}';

  	$this->expiration			    = config_item('session_expiration',7200,true);
    parent::__construct();
  }


	/**
	* Create session table if it does not exist
	*
	* @return void
	*/
  public function create_schema()
  {
      parent::create_schema("
			session_id varchar(40) DEFAULT '0' NOT NULL,
			ip_address varchar(45) DEFAULT '0' NOT NULL,
			user_agent varchar(120) NOT NULL,
			last_activity int(10) unsigned DEFAULT 0 NOT NULL,
			user_data text NOT NULL,
			PRIMARY KEY (session_id),
			KEY `last_activity_idx` (`last_activity`)
      ");
  }


	/**
	 * Record the current sesion_id for later
	 * @return boolean
	 */
	public function open() {
		//Store the current ID so if it is changed we will know!
		$this->session_id = session_id();
		return TRUE;
	}


	/**
	 * Superfluous close function
	 * @return boolean
	 */
	public function close() {
		return TRUE;
	}


	/**
	 * Attempt to read a session from the database.
	 * @param	string	$id
	 */
	public function read($id = NULL) {

		$query=$this->db
			->select('user_data')
			->from($this->table)
			->where('session_id',$id)
			->get();

			$row=$query->unbuffered_row('array');


			return isset($row['user_data']) ? $row['user_data'] : '';
	}


	/**
	 * Attempt to create or update a session in the database.
	 * The $data is already serialized by PHP.
	 *
	 * @param	string	$id
	 * @param	string 	$data
	 */
	public function write($id = NULL, $user_data = '') {
		/*
		 * Case 1: The session we are now being told to write does not match
		 * the session we were given at the start. This means that the ID was
		 * regenerated sometime durring the script and we need to update that
		 * old session id to this new value. The other choice is to delete
		 * the old session first - but that wastes resources.
		 */
		 //$raw_data=unserialize($user_data);




		//If the session was not empty at start && regenerated sometime durring the page
		if($this->session_id && $this->session_id != $id) {


			//Update the data and new session_id


			//Then we need to update the row with the new session id (and data)
			$this->db->update($this->table, array('user_data' => $user_data, 'session_id' => $id,'last_activity'=>time()),array('session_id'=>$this->session_id));

			return;
		}

		/*
		 * Case 2: We check to see if the session already exists. If it does
		 * then we need to update it. If not, then we create a new entry.
		 */
     $result=$this->db->select('session_id')->from($this->table)->where(array('session_id'=>$id))->get();
     $count = $result->num_rows();


		if($count>0) {
			$this->db->update($this->table, array('user_data' => $user_data,'last_activity'=>time()),array('session_id'=>$id));
		} else {
			$this->db->insert($this->table, array('user_data' => $user_data,'session_id'=>$id,'ip_address'=>ip_address(),'user_agent'=>$_SERVER['HTTP_USER_AGENT'],'last_activity'=>time()));
		}

	}


	/**
	 * Delete a session from the database
	 * @param	string	$id
	 * @return	boolean
	 */
	public function destroy($id) {
		$this->db->delete($this->table,array('session_id'=>$id));
		return TRUE;
	}


	/**
	 * Garbage collector method to remove old sessions
	 */
	public function gc() {
		//The max age of a session
		$time = date('Y-m-d H:i:s', time() - $this->expiration);

		//Remove all old sessions
		$this->db->delete($this->table,array('last_activity <'=>$time));

		return TRUE;
	}
}
