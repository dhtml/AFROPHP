<?php
/**
* my_model class
*
*/

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * My_Model Class
 *
 */
class My_Model extends Model
{

/**
* Select the database connection from the group names defined inside the database.php configuration file or an
* array.
*/
private $db_connected = false;


/** @var null
 * Sets table name
 */
public $table = null;

/** @var string
* stores the primary key of the table
*/
public $primary_key = 'id';

/** @var boolean
* checks if soft deletes are enabled
*/
public $soft_deletes = false;


    /**
     * Class constructor
     *
     * @return	void
     */
    public function __construct()
    {

        //check if dbase is loaded
            if (!defined('db_connected')) {
                $this->db_connected=true;
                $this->load->library("dbforge");
                define('db_connected', 1);
            }

        if ($this->table!=null) {
            $this->setup_table();
        }
    }



    /**
    *  get
    *
    *  Fetch data from the table of the model
    *
    *
    *  @param  integer    $length           The length of data to return
    *
    *  @param  integer    $start           The starting point of the query
    *
    *  @param  string   $order         The order aspect of the query
    *
    *  @param  string   $suffix        Any other addition to the query
    *
    *  @return array                  Associative array of the response
    */
    public function get($length=0, $start=0, $order='', $suffix='')
    {
        return $this->table==null ? null: $this->db->get($this->table, $length, $start, $order, $suffix);
    }


    /**
    *  Shorthand for UPDATE queries.
    *
    *
    *  @param  array   $columns        An associative array where the array's keys represent the columns names and the
    *                                  array's values represent the values to be inserted in each respective column.
    *
    *
    *  @param  string  $where          (Optional) A MySQL WHERE clause (without the WHERE keyword).
    *
    *  @param  array   $replacements   (Optional) An array with as many items as the total parameters.
    *
    *
    *  @return boolean                 Returns TRUE on success of FALSE on error
    */
    public function update($columns, $where = '', $replacements = '')
    {
        return $this->table==null ? null: $this->db->update($this->table, $columns, $where, $replacements);
    }


    /**
    *  truncate
    *
    *  Shorthand for truncating the current table.
    *
    *
    *  @return boolean                 Returns TRUE on success of FALSE on error.
    *
    */
    public function truncate()
    {
        return $this->table==null ? null: $this->db->truncate($this->table);
    }


    /**
    *  Checks whether the current table exists in the current database.
    *
    *
    *  @return boolean             Returns TRUE if table given as argument exists in the database or FALSE if not.
    */
    public function table_exists()
    {
        return $this->table==null ? null: $this->db->table_exists($this->table);
    }


    /**
    *  Shorthand for inserting multiple rows from a json url
    *
    *
    *  @param  string   $url            The url or local file resource to load data from
    *
    *
    *  @return boolean                 Returns TRUE on success of FALSE on error.
    *
    */
    public function insert_json_url($url)
    {
        return $this->table==null ? null: $this->db->insert_json_url($this->table, $url);
    }

    /**
  	*  Shorthand for inserting multiple rows from a json string
  	*
  	*
  	*  @param  string  $json          The json array string
  	*
  	*
  	*  @return boolean                 Returns TRUE on success of FALSE on error.
    *
  	*/
  	function insert_json_string($json)
  	{
      return $this->table==null ? null: $this->db->insert_json_string($this->table, $json);
  	}

		/**
		*  Shorthand for inserting multiple rows in a single query.
		*
		*  @param  array   $columns        An array with columns to insert values into.
		*
		*  @param  array  $data           An array of an unlimited number of arrays containing values to be inserted.
		*
		*  @param  boolean $ignore         (Optional) By default, trying to insert a record that would cause a duplicate
		*                                  entry for a primary key would result in an error. If you want these errors to be
		*                                  skipped set this argument to TRUE.
		*
		*  @return boolean                 Returns TRUE on success of FALSE on error.
		*/
		function insert_bulk($columns, $data, $ignore = false)
		{
			return $this->table==null ? null: $this->db->insert_bulk($this->table, $columns, $data, $ignore);
		}

	/**
  *  When using this method, if a row is inserted that would cause a duplicate value in a UNIQUE index or PRIMARY KEY,
  *  an UPDATE of the old row is performed.
  *
  *
  *  @param  string  $table          Table in which to insert/update.
  *
  *  @param  array   $columns        An associative array where the array's keys represent the columns names and the
  *                                  array's values represent the values to be inserted in each respective column.
  *
  *
  *  @param  array   $update         (Optional) An associative array where the array's keys represent the columns names
  *                                  and the array's values represent the values to update the columns' values to.
  *
  *
  *  @return boolean                 Returns TRUE on success of FALSE on error.
  *
  */
	function insert_update($columns, $update = array())
	{
		return $this->table==null ? null: $this->db->insert_update($this->table, $columns, $update);
	}


	  /**
		*  insert
		*
		*  Shorthand for INSERT queries.
		*
		*  @param  array   $columns        An associative array where the array's keys represent the columns names and the
		*                                  array's values represent the values to be inserted in each respective column.
		*
		*  @param  boolean $ignore         (Optional) By default trying to insert a record that would cause a duplicate
		*
		*  @return boolean                 Returns TRUE on success of FALSE on error.
	  *
	  */
		function insert($columns, $ignore = false)
		{
			return $this->table==null ? null: $this->db->insert($this->table, $columns,$ignore);
		}


			/**
			*  Shorthand for deleting some or all items in a table.
			*
		  *  @param  string  $where          (Optional) A MySQL WHERE clause (without the WHERE keyword).
		  *
		  *
		  *  @param  array   $replacements   (Optional) An array with as many items as the total parameter markers.
		  *
			*
			*
		  *  @return mixed                   On success, returns a resource
			*/
			function del($where = '', $replacements = '')
			{
				return $this->table==null ? null: $this->db->del($this->table, $where,$replacements);
			}

			/**
	     *  Returns one or more columns from ONE row of a table.
	     *
	     *  @param  string  $column         One or more columns to return data from.
	     *
	     *  @param  string  $where          (Optional) A MySQL WHERE clause (without the WHERE keyword).
	     *
	     *  @param  array   $replacements   (Optional) An array with as many items as the total parameter markers.
	     *
	     *
	     *  @return mixed
	     *
	     */
			function dlookup($column, $where = '', $replacements = '')
		 	{
				return $this->table==null ? null: $this->db->dlookup($column,$this->table, $where,$replacements);
			}


		/**
		*  select
		*
		*  Shorthand for simple SELECT queries.
		*
		*
		*  @param  mixed  $columns         A string with comma separated values or an array representing valid column names
		*                                  as used in a SELECT statement.
		*
		*
		*  @param  string  $where          (Optional) A MySQL WHERE clause (without the WHERE keyword)
		*
		*  @param  array   $replacements   (Optional) An array with as many items as the total parameter markers.
		*
		*  @param  string  $order          (Optional) A MySQL ORDER BY clause
		*
		*
		*  @param  mixed   $limit          (Optional) A MySQL LIMIT clause.
		*
		*
		*
		*  @return mixed
		*
		*/
		function select($columns, $where = '', $replacements = '', $order = '', $limit = '')
		{
			return $this->table==null ? null: $this->db->select($columns,$this->table, $where,$replacements,$order,$limit);
		}

	  /**
	  *  replace
	  *
	  *  Shorthand for REPLACE queries.
	  *
	  *  @param  array   $columns        An associative array where the array's keys represent the columns names and the data
	  *
	  *
	  *  @return boolean                 Returns TRUE on success of FALSE on error.
	  *
	  */
		function replace($columns)
		{
			return $this->table==null ? null: $this->db->replace($this->table, $columns);
		}

    /**
    * setup_table
    *
    * preconfigure the table by prefixing, and making sure it exists
    *
    * @return object
    */
    public function setup_table()
    {
        //create table if it does not exist
            if (!$this->db->table_exists($this->table)) {
                $this->create_schema();
            }
        return $this;
    }

    /**
    * Creation of schema, this function is meant to be override by the model
    *
    * Create database schema
    *
    * @return mixed
    */
    public function create_schema()
    {
    }

}
