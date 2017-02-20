<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model
{
	
	public $debug = array();
	public $errors = array();
	public $infos = array();
	public $messages = array();
	public $warnings = array();
	public $successes = array();
	public $flash_errors = array();
	public $flash_infos = array();
	public $flash_messages = array();
	public $flash_warnings = array();
	public $flash_successes = array();

	

    /**
     * Select the database connection from the group names defined inside the database.php configuration file or an
     * array.
     */
    protected $_database_connection = NULL;

    /** @var
     * This one will hold the database connection object
     */
    protected $_database;

    /** @var null
     * Sets table name
     */
    public $table = NULL;

	/** @var null
    * checks if soft deletes are enabled
    */
    public $soft_deletes = false;

    /** @var int
     * Sets the number of infinity data to retrieve per page
     */
    public $infinity_items_per_page = 40;
	
	public $total_rows;
	public $result=Array();
	public $links;	
	private $status_type='apb';

		//group statuses for the backend
	public $statuses=Array(
	);

	//define grouping actions for backend
	public $group_actions = Array(
		'action'=>'Apply',
		'options'=>Array('' => 'Bulk Actions','delete' => 'Delete'),
	);
	
	//define sorting actions for backend
	public $sort_actions = Array(
		'action'=>'Apply',
		'options'=>Array('' => 'Sorting Order', 'id' => 'Item ID'),
	);
	
	//model variables
	public $vars=Array(
	//Subject
	'subject'=>'item', 

	//Administrative Page title
	'title'=>'Manage Items', 
	
	//Admin columns
	'columns'=>array('Post'=>'','Status'=>'column','Dates'=>'column','&nbsp;'=>'column'),		
	
	//Pagination variable
	'pager_var'=>'pages',
	);
	
	//maximum pager links
	public $max_pager_links=11;

	//pager start
	public $start=0;
	
	//pager starting position e.g. 1
	public $start_pos;

	//pager stopping position e.g. 10
	public $stop_pos;

	//pager caption e.g. Displaying 1 - 10 of 47 users
	public $caption;

	//used internally to store built queries
	public $sql="";


	//the primary key of table table
    public $primary_key = 'id';

	
    /**
     * @var array
     * You can establish the fields of the table. If you won't these fields will be filled by MY_Model (with one query)
     */
    public $table_fields = array();

	//store name of tables, used internally to build query
	public $table_names = array();

    /** relationships variables */
    private $_relationships = array();
    public $has_one = array();

    public $fillable = array(); //the fields that are allowed during updates for a table

	/**
	* Fields to be used for selecting data
	* 
	*/
	public $select_fields="*";
	
    public function __construct()
    {
        parent::__construct();
		
		if(!db_connected) {return;}
		
		$this->check_table_health();
		
		$this->table_names[]=$this->table;
		
		//items per page selector - this is irrespective of model
		if($this->input->post('items_per_page_input')!=null) {
			$this->save_items_per_page();
		}
		
		
		// load error and info messages from flashdata (but don't store back in flashdata)
		$this->errors = $this->session->flashdata('errors') ?: array();
		$this->infos = $this->session->flashdata('infos') ?: array();
		$this->messages = $this->session->flashdata('messages') ?: array();
		$this->warnings = $this->session->flashdata('warnings') ?: array();
		$this->successes = $this->session->flashdata('successes') ?: array();
    }

	/**
	* Filter functions, meant to be overridden
	*/
	public function filter() {
		return "";
	}
	
	/**
	* process_tblactions
	*
	* Process bulk tabular actions
	*/
	public function process_tblactions() {
		$ids=$this -> input -> post('trans');
		$action=$this -> input -> post('tblaction');
		
		$target="action_{$action}";
		
				
		if(method_exists($this,$target)) {
			$this->$target($ids);
		} else {
				$this -> session -> set_flashdata('message', "Please declare method $target in your model");
		}
	}
	
	/**
	* Initialize the pagination routine
	* @param	int		$per_page		The number of items to render per page
	*/
	public function paginate($per_page=null,$infinity=false) {
		$pager_var=$this->getvar('pager_var');

		if($infinity) {
			$this->max_pager_links=1000000; //you cannot really get to this now, can you?
		}
		
		//get total items to show on a page
		if($per_page==null || empty($per_page)) {
			$per_page=(int) $this->variable->get('items_per_page_input',uri_string(),"20");
		}

		if($per_page==0) {
		//no limits, therefore show everything
		$current_page=0;
		} else {
		//determine the starting limit here
		$current_page=$this->input->get($pager_var);
		if($current_page!=null) {
		$this->start=$current_page;
		}
		}
		
		$where=$this->filter();
		
		$sql=$this->select($where,true)->sql;
		

		$sql.= $per_page==0 ? " " :  " limit $this->start,$per_page";
		
		
        $query = $this->db->query($sql);
		$this->result=(array) $query->result_array();
		$this->sub_total=$query->num_rows();

        $query2 = $this->db->query("select FOUND_ROWS() total");
		$this->total_rows=$query2->row()->total;		
		

		//do the actual pagination something
		$uri=uri_string();
		
		
		$this->load->library('pagination');

		
		//$config['full_tag_open'] = '<span>'.ucfirst(plural($this->getvar('subject'))).':</span> ';
		$config['full_tag_open'] = '';
		$config['full_tag_close'] = '';
		$config['num_links'] = $this->max_pager_links;
		$config['query_string_segment']=$pager_var;
		$config['page_query_string'] = TRUE;
		$config['reuse_query_string'] = true;
		$config['base_url'] = rtrim(current_url(),'/').'/';
		$config['total_rows'] = $this->total_rows;
		$config['per_page'] = $per_page;
		
		$this->pagination->initialize($config);
		
		$this->links=$this->pagination->create_links();
		
		$this->start_pos=$current_page+1;
		$this->stop_pos=$current_page+$this->sub_total;
		
		if($this->stop_pos>$this->total_rows) {$this->stop_pos=$this->total_rows;}

		if($this->total_rows>0) {
			$this->caption="". $this->start_pos ." to ". $this->stop_pos . " of ".$this->total_rows ." " . ($this->total_rows==1 ? singular($this->getvar('subject')) : plural($this->getvar('subject'))  );
		}
		
		return $this;
	}

	/**
	* Render pages links
	*/
	public function pager($prefix='') {
		return empty($this->links) ? '' : '<span class="my_pagination">'.$prefix.$this->links."</span>" ;
	}

	/**
	* save_items_per_page
	* 
	* Saves the items per page and redirects back to the calling page
	* 
	*/
	private function save_items_per_page() {
		$value=$this->input->post('items_per_page_input');
		$this->load->library('variable');
		$this->variable->set('items_per_page_input',$value,uri_string());
		redirect(browser_url);
	}
	
	
	 /**
     * public function getvar()
     * Retrieves a model_var variable
     * @param null $name
     * @return mixed
     */
	public function getvar($name) {
		return isset($this->vars[$name]) ? $this->vars[$name] : null;
	}
	
	/**
	* Count number of entries
	*/
	public function count($where='') {
		$query = $this->db->query("SELECT count(*) count FROM $this->table $where");
		return $query->row()->count;
	}
	
	
	/**
	 * is_unique
	 *
	 * Checks to make sure that the specified field values are not presently in the database
	 *
	 * @param		array			 $fields			an array like this array('username'=>'tony','email'=>'diltony@yahoo.com')
	 * @param		array			 $feedback			an array like this array('username'=>'Username is not unique','email'=>'E-mail is not unique')
	 * @param		string			 $primary_id		The primary ID of the model item in case of an update
     * 
     * It also uses the debugger to set debug vars of duplicate fields
     *
	 * @return bool
	 */
	public function is_duplicate($fields=array(),$feedback=array(),$primary_id=null) {
		if(!is_array($fields)||empty($fields) || !is_array($feedback)||empty($feedback)  ) {
			return false; //it is not duplicate because input is so wrong
		}
			
		$duplicates=Array();
		foreach($fields as $field=>$value) {
			if($primary_id!=null) {$this->db->where_not_in($this->primary_key,$primary_id);}
			$this->db->where($field, $value);
			$query = $this->db->get($this->table);
			if ($query->num_rows() != 0) {
				if(isset($feedback[$field])) {$this->error($feedback[$field]);}
				$duplicates[]=$field;
				}
		}

		if (!empty($duplicates)) {
			$this->debug('duplicate',$duplicates);
			return true;
		}

		return FALSE;
	}

	/**
	* debug
	* 
	* An error log with key and value, for debugging
	* @param	string		$name 		The name of the error
	* @param	mixed		$value 		The value of the error
	* 
	*/
	public function debug($name,$value) {
		$this->debug["$name"]=$value;
	}
	
	/**
	* clone_data
	* 
	* puts the model's data into post variables if no other post variable is set
	*
	* @param 	string		$value		The value of the primary key to clone
	*
	* return single object
	*/
	public function clone_data($value=null) {
		if($value==null||!empty($_POST)) {return;}
		$viewdata=$this->select("where {$this->table}.{$this->primary_key}=$value")->get_array();
		$_POST=$viewdata;
		return $this;
	}
	
	/**
	* Gets primary_key matching
	*
	* @param	string	$where			Where clause e.g. where 1
	* @param	boolean	$calc_results	Determine if calc_results is set to true
	*
	* Returns a single object
	*/
	public function select($where='',$calc_results=false) {
		$fields=explode(",",$this->select_fields);

		$sql="select ". ($calc_results==true? 'SQL_CALC_FOUND_ROWS ' : '') . " $this->select_fields from $this->table ";
		$tables=Array();
		foreach($fields as $field) {
			$f=explode('.',$field);
			if(count($f)!=2 || in_array($f[0],$tables)) {continue;}
			$tables[]=$f[0];
		}
		
		//link the selected tables together
		foreach($tables as $table) {
			if(!isset($this->has_one[$table])) {continue;}
			$rel=$this->has_one[$table];
			$sql.=" left join $table on $table.$rel[foreign_key] = $this->table.$rel[local_key]";
		}
		
		if($where!=null) {$sql.=" $where";}
		
		$this->sql=$sql;
		$this->reset_query();
		
		return $this;
	}
	
	/**
	* Insert query
	* @param	array	$data		The data that is meant to be inserted
	* @param	array	$unique		The name of fields with their error messages to enforce avoid duplicates on e.g. 'name'=>'A role with this name already exist','description'=>'A role with this description already exist'
	*/
	public function insert($data,$unique=null) {
		$this->sanitize_user_data($data);

		if($unique!=null && is_array($unique)) {
			$fields=Array(); $feedback=Array();
			foreach($unique as $key=>$value) {
				if(!isset($data["$key"])) {return false;}
			
			$fields["$key"]=$data["$key"];			
			$feedback["$key"]=$value;			
			}
			
			if($this->is_duplicate($fields,$feedback)) {
				return false;
			}
		}
		

		$data['created_at'] = date("Y-m-d H:i:s");		
		$data['created_by'] = $this->person->id;		
		
		$response=$this->db->insert($this->table, $data);
		return $response==true ? $this->db->insert_id() : false;
	}
	
	

	/**
	* Update query
	* @param	array	$data			The data that is meant to be updated
	* @param	array	$primary_id		The value of the primary id e.g 1
	* @param	array	$unique		The name of fields with their error messages to enforce avoid duplicates on e.g. 'name'=>'A role with this name already exist','description'=>'A role with this description already exist'
	*/
	public function update($data,$primary_id=0,$unique=null) {
		$this->sanitize_user_data($data);

		
		if($unique!=null && is_array($unique)) {
			$fields=Array(); $feedback=Array();
			foreach($unique as $key=>$value) {
				if(!isset($data["$key"])) {return false;}
			
			$fields["$key"]=$data["$key"];			
			$feedback["$key"]=$value;			
			}
			
			if($this->is_duplicate($fields,$feedback,$primary_id)) {
				return false;
			}
		}

		$data['updated_at'] = date("Y-m-d H:i:s");		
		$data['updated_by'] = $this->person->id;		
		
		$this->db->where($this->primary_key, $primary_id);
		$this->db->update($this->table, $data);

		return $this->db->affected_rows();
	}
	
	/**
	* Scans a model if the url field matches the supplied one
	*
	* returns true if url exists otherwise false
	*/
	public function does_url_exist($url) {
		$query = $this->db->get_where($this->table, array('url' => $url));
		
		return $query->num_rows()>0 ? true : false;
	}
	
	
	/**
	* Scans the current url from this model's table, the table must contain a url,publish field
	* @param	the array to merge results with
	*
	* returns true if url exists otherwise false
	*/
	public function match_current_url($params=Array(),$path=null) {
		static $_current_url_response=null;
		
		//save this, so that subsequent calls returns stored value
		if($_current_url_response!=null) {return $_current_url_response;}
		
		$path= $path==null ? request_url2 : $path;
		
		$response=Array();
		$query = $this->db->get_where($this->table, array('url' => $path,'publish'=>'1'));
		if($query->num_rows()==0) {return $response;}
		
		$data=array_merge($query->row_array(),$params);

		$response[$path]=$data;
		
		$_current_url_response=$response;
		return $_current_url_response;
	}
	
	
	/**
	* Scans the all urls of this model
	* @param	the array to merge results with
	* returns true if url exists otherwise false
	*/
	public function match_all_url() {

	$response=Array();
	$query = $this->db->get_where($this->table);

	foreach($query->result_array() as $row) {
		$url=$row['url'];
		$row['type']=MENU_NORMAL_ITEM;
		$response["$url"]=$row;
	}
	
	return $response;
	}

	/**
	* delete_items
	*
	* Delete multiple items from the model e.g. array(2,4,6)
	* @param	mixed	$ids	An array of ids to be deleted (or a single id)
	*
	* @returns the total number of items deleted
	*/
    public function trash($ids=Array()) {
		$ids=array($ids);

		if($this->soft_deletes) {
		$data=array();
		$data['deleted_at'] = date("Y-m-d H:i:s");		
		$data['deleted_by'] = $this->person->id;		
		
		$this->db->where_in($this->primary_key,_flatten_object($ids));
		$this->db->update($this->table, $data);
			
		} else {
		$this->db->where_in($this->primary_key,_flatten_object($ids));
        $this->db->delete($this->table);
		}
		
		
		
		return $this->db->affected_rows();
    }

	//restores soft deletes
    public function untrash($ids=Array()) {
		$ids=array($ids);

		$data=array();
		$data['deleted_at'] = null;		
		$data['deleted_by'] = null;		

		$data['updated_at'] = date("Y-m-d H:i:s");		
		$data['updated_by'] = $this->person->id;		
		
		$this->db->where_in($this->primary_key,_flatten_object($ids));
		$this->db->update($this->table, $data);

		return $this->db->affected_rows();
	}	
	
	
	public function thrash($ids=Array()) {
			return $this->trash($ids);
	}


	//this is like trash in hard delete mode
    public function expunge($ids=Array()) {
		$ids=array($ids);

		$this->db->where_in($this->primary_key,_flatten_object($ids));
        $this->db->delete($this->table);
		
		return $this->db->affected_rows();
    }

	
	/**
	* Bulk delete action from admin manager
	*
	*/
	public function action_delete($ids) {
		$count=$this->trash($ids);

		$text=action2text(array(
			'count'=>$count,
			'item'=>$this->getvar('subject'),
			'action'=>'deleted',
			));	

		$this -> flash -> message($text);
		redirect(browser_url);
	}
	
	/**
	* Bulk undelete action from admin manager
	*
	*/
	public function action_undelete($ids) {
		$count=$this->untrash($ids);

		$text=action2text(array(
			'count'=>$count,
			'item'=>$this->getvar('subject'),
			'action'=>'restored',
			));	

		$this -> flash -> message($text);
		redirect(browser_url);
	}
	
	/**
	* Bulk undelete action from admin manager
	*
	*/
	public function action_expunge($ids) {
		$count=$this->expunge($ids);

		$text=action2text(array(
			'count'=>$count,
			'item'=>$this->getvar('subject'),
			'action'=>'permanently deleted',
			));	

		$this -> flash -> message($text);
		redirect(browser_url);
	}
	
	
	/**
	* Gets primary_key matching
	*
	* @param	string	$where			Where clause e.g. where 1
	* @param	boolean	$calc_results	Determine if calc_results is set to true
	*
	* Returns a single object
	*/
	public function query($where='',$calc_results=false) {
		$this->sql="select ". ($calc_results==true? 'SQL_CALC_FOUND_ROWS ' : '') .implode(',',$this->table_fields) . " from " . implode(' ',$this->table_names) . " $where";

		$this->reset_query();
		
		return $this;
	}

	/**
	* Resets query
	*/
	public function reset_query() {
		//reset resources
		$this->table_names=$this->table_fields=Array();
		$this->table_names[]=$this->table;
		return $this;
	}
	
	
	/**
	* get
	* return a single object
	* returns object
	*/
	public function get() {
		$query=$this->db->query($this->sql);
		return $query->row();
	}
	
	/**
	* get_array
	* return a single array
	* returns array
	*/
	public function get_array() {
		return (array) $this->get();
	}
	
	/**
	* get_object
	* return a single array
	* returns object
	*/
	public function get_object() {
		return $this->get();
	}
	
	/**
	* Gets all results
	* returns a single row
	*/
	public function get_all() {
		$query=$this->db->query($this->sql);
		return $query->result();
	}

	/**
	* Retrieves the last sql
	*/
	public function last_query() {
		return $this->sql;
	}
	
	/**
	* Check if fillable fields is set, otherwise all table fields are fetched
	*/
	public function fillable() {
		if(!db_connected) {return;}
		
		if(empty($this->fillable)) {
			$sql="show columns from $this->table;";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row)
			{
				$this->fillable[]=$row['Field'];
			}
		}
		
	}
	
	/**
	* sanitize_user_data
	*
	* makes sure that the fields inside the data is fillable
	*/
	public function sanitize_user_data(&$data) {
		$this->fillable();
		
		$_data=$data;
		foreach($_data as $field=>$value) {
			if(!array_search($field,$this->fillable)) {unset($data[$field]);}
		}
	}

	
	
	//unsed internally to build query
	public function add_table_fields($table,$fields) {
		$fields=explode(',',$fields);
		foreach($fields as $field) {
			$this->table_fields[]="$table.$field";
		}
	}
	
	/**
	* Gets the fields to process
	*
	*/
	public function get_fields($fields) {
		$this->add_table_fields($this->table,$fields);
	}

	/**
	* Join table
	*/
	public function get_with($table,$fields) {
		if(!isset($this->has_one[$table])) {return;}
		$rel=$this->has_one[$table];
			
		$this->table_names[]="left join $table on $table.$rel[foreign_key] = $this->table.$rel[local_key]";
		
		$this->add_table_fields($table,$fields);
	}




	
	
	//fetches the next item of the result set
	public function fetch_object() {
		if(empty($this->result)) {return array();}

		$current=current($this->result);
		next($this->result);
		
		return $current;
	}
	
	//fetches the next item of the result set
	public function fetch_array() {
		return _flatten_object($this->fetch_object());
	}

	//generates the items per page selector
	function items_per_page_selector($text='Show entries') {
		$options=config_item('admin_pager_records_per_page');

		$selected=$this->variable->get('items_per_page_input',uri_string(),"20");
		
		//return '<label class="control-label">'.$text.'</label>'. form_dropdown('items_per_page_input', $options, $selected,' onchange="this.form.submit();" class="form-control form-control-min" style="padding-left: 5px;padding-right: 5px;" ');
		return form_dropdown('items_per_page_input', $options, $selected,' onchange="this.form.submit();" class="form-control  form-control-flat input-sm" style="padding-left: 5px;padding-right: 5px;" ');
	}
	
	/**
	* Display options liek Suspend, delete etc
	*
	*/
	function group_actions() {
		$options=$this->group_actions['options'];
		$action=$this->group_actions['action'];
		//echo '<form class="form-inline" role="form">'."\n";
		echo form_dropdown('sm_form_action', $options,'',' id="sm_form_action" onchange="" style="margin-left:10px;" class="form-control  form-control-flat input-sm" ');
		echo '<button id="sm_form_action_button" class="btn btn-default no-display btn-sm btn-flat" type="button">'.$action.'</button>'."\n";
	}
	
	
	/**
	* This renders the form for sorting and ordering
	*
	*/
	function sort_actions() {
		$options=$this->sort_actions['options'];
		$action=$this->sort_actions['action'];

		$selector=new httpquery(browser_url);
		
		$data=Array(); $_data=Array();
		foreach($options as $key=>$value) {
			if($key=='') {
				$path='';
			} else {
				$selector->set('sortfield',$key);
				$selector->remove('ordering');
				$path=$selector->rebuild();
			}
			
			$data["$path"]=$value;

			$_data["$key"]=$path;
		}


		//fetch last query sortfield 
		$sortfield=$this->input->get('sortfield');
		if($sortfield!=null) {$sortfield=isset($_data[$sortfield]) ? $_data[$sortfield] : null;}
		
		echo form_dropdown('', $data,$sortfield,' required id="sm_order_action" style="margin-left:10px;" class="form-control  form-control-flat input-sm" ');
		echo form_dropdown('', array('asc'=>'Ascending','desc'=>'Descending'),$this->input->get('ordering'),' id="sm_order_by" class="form-control  form-control-flat input-sm" ');
		echo '<button id="sm_form_action_button" class="btn btn-default btn-sm btn-flat" type="submit">'.$action.'</button>'."\n";
	}
	

	/**
	* Display all statuses
	*
	*/
	public function statuses() {
		if(empty($this->statuses)||!is_array($this->statuses)) {return;}
		$sql=Array();
		
		$pos=0;
		foreach($this->statuses as $key=>$value) {
			$name="item_{$pos}";
			//$name=fvalidator::sanitize_username($key);
			$sql[]="($value ) $name";
			$pos++;
		}
		$sql='select '.implode(',',$sql);
		
		$row = $this->db->query($sql)->row_array();

		$keys=array_keys($this->statuses);
		$values=array_values($row);

		$response=Array();
		
		
        $selector=new httpquery(browser_url2);
		for($i=0;$i<count($keys);$i++) {
			if($values[$i]==0) {continue;}
			$name=$keys[$i];
			$class=strtolower(fvalidator::sanitize_username($name));
			
			$selector->set($this->status_type,$class);
			$path=$selector->rebuild();

			
			//determine if this is the current url
			$linkclass="";
			$current_type=isset($_GET[$this->status_type]) ? $_GET[$this->status_type] : 'all';
			if($current_type==$class) {$linkclass='current';}
			
			$response[]='<li class="'.$class.'"><a class="'.$linkclass.'" href="'.$path.'">'.$keys[$i].' <span class="count">('.$values[$i].')</span></a> ';
		}
		
		
		
		$response='<ul class="subsubsub"><li>Filter: </li>'.implode(" | ",$response).'</ul>';
		
		
		return $response;
	}
	
	/**
	*
	* Get the total number of item/itams
	*/
	public function inflect($_item='entry') {
		$count=count($this->result);
		$item=$this->total_rows=='1' ? singular($_item) : plural($_item);
		
		$text="";
		if($this->total_rows=='1') {
			$text=" $this->total_rows";
			} else {
			$text=" $this->total_rows $item";
			}
			$response='<span id="entry-item-count" data-item-sing="'.singular($_item).'">'.$text."</span>";
		return $response;
	}

	/**
	* select_options
	* Gets two fields to use to form an array for select options
	* @param	$fields 	The fields to be selected e.g. id,name
	* @param	$where		The conditions e.g. where 1
	* returns an array like 
    * array (size=3)
    * 2 => string 'authenticated user' (length=18)
    * 3 => string 'moderator' (length=9)
    * 4 => string 'superadmin' (length=10)
	*
	*/
	public function select_options($fields,$where) {
		$sql="select $fields from $this->table $where";
			$query = $this->db->query($sql);
			
			$options=Array();
			foreach ($query->result_array() as $row)
			{
				$row=array_values($row);
				$options[$row[0]]=$row[1];
			}
			
			return $options;
	}

	
		/**
	 * Get Errors Array
	 * Return array of errors
	 * @return array Array of messages, empty array if no errors
	 */
	public function get_errors_array()
	{
		return $this->errors;
	}

	/**
	 * Print Errors
	 * 
	 * Prints string of errors separated by delimiter
	 * @param string $divider Separator for errors
	 */
	public function print_errors($divider = '<br />')
	{
		$msg = '';
		$msg_num = count($this->errors);
		$i = 1;
		foreach ($this->errors as $e)
		{
			$msg .= $e;

			if ($i != $msg_num)
			{
				$msg .= $divider;
			}
			$i++;
		}
		echo $msg;
	}
	
	/**
	 * Clear Errors
	 * 
	 * Removes errors from error list and clears all associated flashdata
	 */
	public function clear_errors()
	{
		$this->errors = array();
		$this->session->set_flashdata('error', $this->errors);
		$this->session->set_flashdata('info', $this->errors);
		$this->session->set_flashdata('message', $this->errors);
		$this->session->set_flashdata('success', $this->errors);
		$this->session->set_flashdata('warning', $this->errors);
	}


	/**
	 * Error
	 * Add message to error array and set flash data
	 * @param string $message Message to add to array
	 * @param boolean $flashdata if TRUE add $message to CI flashdata (deflault: FALSE)
	 */
	public function error($message = '', $flashdata = TRUE){
		$this->errors[] = $message;
		if($flashdata)
		{
			$this->flash_errors[] = $message;
			$this->session->set_flashdata('error', $this->flash_errors);
		}
	}

	//alias to error
	public function danger($message="", $flashdata = TRUE) {
		return $this->error($message,$flashdata);
	}

	/**
	 * Info
	 *
	 * Add message to info array and set flash data
	 * 
	 * @param string $message Message to add to infos array
	 * @param boolean $flashdata if TRUE add $message to CI flashdata (deflault: FALSE)
	 */
	public function info($message = '', $flashdata = TRUE)
	{
		$this->infos[] = $message;
		if($flashdata)
		{
			$this->flash_infos[] = $message;
			$this->session->set_flashdata('info', $this->flash_infos);
		}
	}


	/**
	 * message
	 *
	 * Add message to info array and set flash data
	 * 
	 * @param string $message Message to add to infos array
	 * @param boolean $flashdata if TRUE add $message to CI flashdata (deflault: FALSE)
	 */
	public function message($message = '', $flashdata = TRUE)
	{

		$this->messages[] = $message;
		if($flashdata)
		{
			$this->flash_messages[] = $message;
			$this->session->set_flashdata('message', $this->flash_messages);
		}
	}


	/**
	 * warnings
	 *
	 * Add message to info array and set flash data
	 * 
	 * @param string $message Message to add to infos array
	 * @param boolean $flashdata if TRUE add $message to CI flashdata (deflault: FALSE)
	 */
	public function warning($message = '', $flashdata = TRUE)
	{
		$this->warnings[] = $message;
		if($flashdata)
		{
			$this->flash_warnings[] = $message;
			$this->session->set_flashdata('warning', $this->flash_warnings);
		}
	}

	/**
	 * successes
	 *
	 * Add message to success array and set flash data
	 * 
	 * @param string $message Message to add to infos array
	 * @param boolean $flashdata if TRUE add $message to CI flashdata (deflault: FALSE)
	 */
	public function success($message = '', $flashdata = TRUE)
	{
		$this->successes[] = $message;
		if($flashdata)
		{
			$this->flash_successes[] = $message;
			$this->session->set_flashdata('success', $this->flash_successes);
		}
	}
    
	/**
	* Unflash
	* Converts flash data to string
	*/
	public function unflash($name="message") {
		$response=$this->session->flashdata($name);
		
		if(is_array($response) && !empty($response)) {
			if(count($response)==1) {
			  $response=$response[0];
			} else {
			  $response='<ul class="flash_data"><li>'. implode('<li>',$response)."</ul>";
			}
		}
		
		
		return $response;
	}

	/**
	* Checks a table if it exists, if it does not, then it recreates it
	*
	*/
	public function check_table_health() {
		if(!db_connected) {return;}
		$this->load->model('data_model');
		
		
		if($this->data_model->table_exists($this->table)) {return;}

		$this->load->dbforge();
		
		if(method_exists($this,'create_schema')) {
			$this->create_schema();
		} else {
			show_error("There is no table called $this->table");
		}
	}
	
	public function __call($method, $arguments)
    {
		if($method=='fields') {
			$this->get_fields($arguments[0]);
		} else  if(substr($method,0,5)=='with_') {
			$this->get_with(substr($method,5),$arguments[0]);
		}
		
		return $this;
	}

}
