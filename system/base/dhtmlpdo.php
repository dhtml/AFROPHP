<?php
 /**
 *  DHTMLPDO
 *
 *  An advanced, compact and lightweight PDO database wrapper library, built around PHP.
 * It provides methods for interacting with your
 *  databases that are more secure, powerful and intuitive than PHP's default ones.
 *
 *  It encourages developers to write maintainable code and provides a better default security layer by encouraging the
 *  use of prepared statements, where arguments are escaped automatically.
 *
 *  For more resources visit {@link http://github.com/dhtml}
 *
 *  @author     Anthony Ogundipe a.k.a dhtml <diltony@yahoo.com>
 *  @author url     http://www.dhtmlextreme.com , http://www.africoders.com
 *  @version    1.0.0 (last revision: January 01, 2016)
 *  @copyright  (c) 2016 Anthony Ogundipe
 *  @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE
 *  @package    DHTMLPDO
 */

class DHTMLPDO
{

  /**
  *  query builder data
  *
  *  @access private
  *
  *  @var  array
  */
 private $builder=array();

 /**
 *  dhtml_pdo result object
 *
 *  @access private
 *
 *  @var  object
 */
 private $dhtmlpdo_result;

  /**
  *  table prefix
  *
  *  @access private
  *
  *  @var  string
  */
  private $prefix='';

  /**
  *  last raw sql
  *
  *  @access private
  *
  *  @var  string
  */
  private $last_raw_sql='';


    /**
    *  last sql
    *
    *  @access private
    *
    *  @var  string
    */
    private $last_sql='';


  /**
  *  last raw params
  *
  *  @access private
  *
  *  @var  string
  */
  private $last_raw_params;

  /**
  *  pdo object
  *
  *  @access private
  *
  *  @var  array
  */
  private $con;

  /**
  *  pdo exception
  *
  *  @access private
  *
  *  @var  array
  */
  private $exception;

 /**
 *  configurational data
 *
 *  @access private
 *
 *  @var  array
 */
 private static $config;

 /**
 * stores the configuration details statically
 *
 * @param array $config The configuration array
 *
 * Example:
 * <code>
 * array (
 *  'dsn'=>'pgsql:dbname=afrophp;host=localhost;user=admin;password=pass;port=5432',
 *  'driver' => 'mysql',
 *  'hostname' => 'localhost',
 *  'username' => 'admin',
 *  'password' => 'pass',
 *  'database' => 'afrophp',
 *  'port' => null,
 *  'char_set' => 'utf8',
 *  'collat' => 'utf8_general_ci',
 *  'prefix' => 'afro_',
 *  'schema'=>'public',
 *  'persistent'=>true
 * );
 * </code>
 *
 * If the dns is not set, it will be automatically created from the provided details
 *
 * @return void
 */
 public static function configure($config)
 {
     self::$config=$config;
 }

/**
* class constructor
*
* @param array $config (optional) The configuration data of the database
*
* @return void
*/
 public function __construct($config=null)
 {
     if (!is_null($config)) {
         self::configure($config);
     }
 }



  /**
  * queries database
  *
  * @param string $sql the sql statement
  * example:
  * $sql=INSERT INTO `my_table` (`title`, `name`) VALUES(?,?)
  *
  * @param array $params the paramters as an array
  *
  * params = Array
  * (
  *    [0] => ce'o7
  *    [1] => ton'y7
  * )
  *
  *
  * @param boolean $exec should the query be executed?
  *
  * @return object
  */
  public function query($sql, $params=array())
  {
      //if connection is not secured, return false
 if (!$this->connect()) {
     return false;
 }

 //prefix tables
 $sql=$this->dbprefix($sql);

      try {
          $stmt = $this->con->prepare($sql);

          $this->last_raw_sql=$sql;
          $this->last_raw_params=$params;

          $i = 1;
          if (is_array($params) && !empty($params)) {
              foreach ($params as $param) {
                  $stmt->bindValue($i, $param);
                  $i++;
              }
          }

          $stmt->execute();
      } catch (PDOException $e) {
          $this->exception=$e;
          return false;
      }

      $this->builder=array(); //reset directives

  //store last generated sql query
  $this->last_sql=$stmt->queryString;

      $this->dhtmlpdo_result=new dhtmlpdo_result($stmt, $this);

      return $this->dhtmlpdo_result;
  }




 /*
 * frees last result
 */
 public function free_result()
 {
     $this->pdo_result=null;
 }



/**
* Attempts to open a connection if one is not opened
* and returns the status of the connection
*
* @return boolean
*/
 public function connect()
 {

   //if connection is still active, then dont bother to reconnect
   if ($this->con!=null) {
       return true;
   }


     $config=self::$config;

   //if config is not an array, initialize to empty array
   if (!is_array($config)) {
       $config=array();
   }

   //if configuration details are missing, set them to default
   if (!isset($config['dsn'])) {
       $config['dsn']='';
   }
     if (!isset($config['driver'])) {
         $config['driver']='mysql';
     }
     if (!isset($config['hostname'])) {
         $config['hostname']='localhost';
     }
     if (!isset($config['username'])) {
         $config['username']='root';
     }
     if (!isset($config['password'])) {
         $config['password']='';
     }
     if (!isset($config['database'])) {
         $config['database']='test';
     }
     if (!isset($config['port'])) {
         $config['port']=null;
     }
     if (!isset($config['char_set'])) {
         $config['char_set']='utf8';
     }
     if (!isset($config['collat'])) {
         $config['collat']='utf8_general_ci';
     }
     if (!isset($config['prefix'])) {
         $config['prefix']='';
     }
     if (!isset($config['schema'])) {
         $config['schema']='public';
     }
     if (!isset($config['persistent'])) {
         $config['persistent']=false;
     }

   //correct the configuration if necessary
   if ($config!=self::$config) {
       self::$config=$config;
   }

     extract($config);

   //if no dsn, then build one
   if (empty($dsn)) {
       $dsn="{$driver}:host={$hostname};port={$port};dbname={$database};charset={$char_set}";
   }

   //extract the driver from dsn
   $e=explode(':', $dsn);
     $driver=strtolower($e[0]);

   //database prefix
   $this->prefix = $prefix;

     $options=array();

     if (($driver=='mysql' || $driver=='mysqli') && $char_set!='' && $collat!='') {
         $options[PDO::MYSQL_ATTR_INIT_COMMAND]="SET NAMES {$char_set} COLLATE $collat";
     }

     if ($persistent==true) {
         $options[PDO::ATTR_PERSISTENT] = true;
     }

     try {
         $this->con = new PDO($dsn,
                     $username, $password, $options
                     );
         $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

         if ($driver=='pgsql' || $driver=='odbc') {
             $this->con->exec("SET search_path TO $schema");
         }
     } catch (PDOException $e) {
         $this->exception=$e;
         return false;
     }

     return true;
 }

/**
*  Retrieves last pdo error
*
*  <code>
*	 echo $db->last_error();
*  </code>
*
*
*  @return string
*/
 public function last_error()
 {
     return is_null($this->exception) ? '' : $this->exception->getMessage();
 }

/**
* Retrieves the database host info
*
* @return string
*/
public function host_info()
{
    return $this->db_driver().' '.$this->db_version();
}


/**
* Gets the version of the database
*
* @return string
*/
public function db_version()
{
    if (is_null($this->con)) {
        return '';
    }

    try {
        $version = $this->con->query('select version()')->fetchColumn();
    } catch (PDOException $e) {
        $this->exception=$e;
        $version='';
    }
    return $version;
}

/**
* Gets the driver of the database
*
* @return string
*/
public function db_driver()
{
    if (is_null($this->con)) {
        return '';
    }

    try {
        $driver=$this->con->getAttribute(PDO::ATTR_DRIVER_NAME);
    } catch (PDOException $e) {
        $this->exception=$e;
        $driver='';
    }

    return $driver;
}





 /**
  *  db_prefix_tables
  *
  *  Allows you prefix your table names in curly braces in an sql statement
  *
  * Cover the tables with curly braces e.g. select * from {users}
  */
 public function dbprefix($stmt)
 {
     preg_match_all('/{(.*?)}/', $stmt, $matches);


     if (!empty($matches[1])) {
         $search=array();
         $replace=array();
         foreach ($matches[1] as $table) {
             $search[]='{'.$table.'}';
             $replace[]="{$this->prefix}{$table}";
         }
         $stmt=str_replace($search, $replace, $stmt);
     }
     return $stmt;
 }




 /**
 * prefixes a database table
 *
 */
 public function set_dbprefix($prefix)
 {
     $this->prefix=$prefix;
     return $this;
 }


   /**
   * Returns the escaped value
   *
   * @param  array|string $item The item to escape
   *
   * return string
   */
   public function escape($item)
   {
       $this->connect();
       if (is_array($item)) {
           foreach ($item as $key=>$it) {
               $item["$key"]=$this->escape($it);
           }
           return $item;
       } else {
           return $this->con->quote($item);
       }
   }



   /**
   * returns number of rows in last query
   */
   public function affected_rows()
   {
       return $this->num_rows();
   }


   /**
   * update data
   */
   public function update($table, $value = array(), $where=null, $real=true)
   {
       $this->set($value);
       $this->where($where);

       $this->builder['table']=$table;

       $this->_compile_update();

       return $this->_exec_query($real);
   }

    public function get_compiled_update($table, $value = array(), $where=null)
    {
        return $this->update($table, $value, $where, false);
    }


   /**
   * compile update
   */
   private function _compile_update()
   {
       $this->_prepare_builder();

       $fields=isset($this->builder['set']) ? $this->builder['set'] : array('*');

       $table=isset($this->builder['table']) ? $this->builder['table'] : 'table';


       $this->builder['sql']="update ". $table . ' set ';


       $bindString = "";

       foreach ($this->builder['set'] as $key=>$value) {
           $bindString .= "$key=?,";
           $this->builder['bind'][]=$value;
       }

       $this->builder['sql'].= trim($bindString, ',') . ' where 1';

       $this->_compile_where();
   }

    public function truncate($table)
    {
        return $this->query("truncate $table");
    }

    public function drop($table)
    {
        return $this->query("drop table $table");
    }


    public function empty_table($table)
    {
        return $this->delete($table);
    }

   /**
   * delete
   */
   public function delete($table, $where = null, $real=true)
   {
       if (is_array($table)) {
           foreach ($table as $t) {
               $this->delete($t, $where, $real);
           }
           return $this;
       } else {
           $this->where($where);
           $this->builder['table']=$table;
           $this->_compile_delete();
           return $this->_exec_query($real);
       }
   }

    public function get_compiled_delete($table, $where=null)
    {
        return $this->delete($table, $where, false);
    }


   /**
   * compile update
   */
   private function _compile_delete()
   {
       $this->_prepare_builder();
       $table=isset($this->builder['table']) ? $this->builder['table'] : 'table';
       $this->builder['sql']="DELETE from ". $table . ' WHERE 1 ';
       $this->_compile_where();
   }

   /**
   * insert data
   */
   public function insert($table, $value = array(), $type='INSERT', $real=true)
   {
       $this->builder['insert_type']=$type;
       $this->set($value);
       $this->builder['table']=$table;

       $this->_compile_insert();

       return $this->_exec_query($real);
   }

    public function get_compiled_insert($table, $value=array(), $type='INSERT')
    {
        return $this->insert($table, $value, $type, false);
    }

    public function get_compiled_replace($table, $value=array(), $type='REPLACE')
    {
        return $this->insert($table, $value, $type, false);
    }





   /**
   * compile insert
   */
   private function _compile_insert()
   {
       $this->_prepare_builder();

       $fields=isset($this->builder['set']) ? $this->builder['set'] : array('*');

       $table=isset($this->builder['table']) ? $this->builder['table'] : 'table';


       $this->builder['sql']=$this->builder['insert_type'] . " into ". $table . '(' . implode(',', array_keys($fields)) .') values ('. implode(',', $this->array2question(array_keys($fields))) .');';

       $this->builder['bind']=array_values($fields);
   }

    public function array2question($arr)
    {
        return array_map(function ($val) {
            return '?';
        }, $arr);
    }

    public function set($name, $value=null)
    {
        if (empty($name)) {
            return $this;
        }

        if (!isset($this->builder['set'])) {
            $this->builder['set']=array();
        }

        if (is_object($name)) {
            $name=(array) $name;
        }

        if (is_array($name)) {
            foreach ($name as $key=>$value) {
                $this->set($key, $value);
            }
        } else {
            $this->builder['set'][$name]=$value;
        }

        return $this;
    }


    public function replace($table, $value = array())
    {
        return $this->insert($table, $value, 'REPLACE');
    }


   /**
   * count table rows
   */
   public function count_all($table)
   {
       $stmt=$this->query("select count(*) c from $table");
       while ($row=$stmt->fetch(\PDO::FETCH_ASSOC)) {
           return $row['c'];
       }
       return 0;
   }

   /**
   * gets the current platform
   */
   public function platform()
   {
       $this->connect();
       return $this->con->getAttribute(PDO::ATTR_DRIVER_NAME);
   }

   /**
   * returns last compiled sql query
   */
   public function last_query($real=false)
   {
       return $real ? $this->last_sql : $this->query_string($this->last_raw_sql, $this->last_raw_params);
   }

   /**
   * get database version
   *
   */
   public function version()
   {
       $this->connect();
       return $this->con->getAttribute(PDO::ATTR_SERVER_VERSION);
   }

   /**
   * Returns the number of rows
   *
   */
   public function num_fields()
   {
       return is_object($this->stmt) ? $this->stmt->columnCount() : 0;
   }

   /**
   * lists all fields of result set
   *
   */
   public function list_fields($table=null)
   {
       if ($table!=null) {
         $stmt=$this->get($table);
         return $stmt->list_fields();
       }
   }


   /**
   * gets field meta data
   *
   */
   public function field_data($table=null)
   {
       if ($table!=null) {
           $this->query("select * from $table limit 1");
       }


       $columns=array();
       if (is_object($this->stmt)) {
           for ($i = 0; $i < $this->stmt->columnCount(); $i++) {
               $col = (object) $this->stmt->getColumnMeta($i);
               if (!isset($col->max_length) && isset($col->len)) {
                   $col->max_length=$col->len;
               }
               if (!isset($col->type) && isset($col->native_type)) {
                   $col->type=$col->native_type;
               }
               if (!isset($col->primary_key) && isset($col->flags) && is_array($col->flags) && in_array('primary_key', $col->flags)) {
                   $col->primary_key=1;
               } else {
                   $col->primary_key=0;
               }
               $columns[] = $col;
           }
       }
       return $columns;
   }



   /**
   * used to get last query
   */
   public function query_string($string, $data=array())
   {
       if (empty($data)) {
           return $string;
       }
       $indexed=$data==array_values($data);
       foreach ($data as $k=>$v) {
           if (is_string($v)) {
               $v="".$this->escape($v)."";
           }

           if ($indexed) {
               $string=preg_replace('/\?/', $v, $string, 1);
           } else {
               $string=str_replace(":$k", $v, $string);
           }
       }
       return $string;
   }


   /**
   * returns query for a table
   */
   public function get($table=null, $limit=null, $offset=null, $real=true)
   {
       $this->from($table);
       $this->limit($limit, $offset);
       $this->_compile_select();
       return $this->_exec_query($real);
   }

    public function get_compiled_select($table=null, $limit=null, $offset=null)
    {
        return $this->get($table, $limit, $offset, false);
    }



   /**
   * get where
   */
   public function get_where($table=null, $where=null, $limit=null, $offset=null)
   {
       $this->from($table);
       if (is_array($where)) {
           $this->where($where);
       }
       $this->limit($limit, $offset);
       $this->_compile_select();
       return $this->_exec_query();
   }

   /**
   * after compiling a query, this will execute the query
   *
   * @return object
   */
   private function _exec_query($real=true)
   {
       if ($real) {
           return $this->query($this->builder['sql'], $this->builder['bind']);
       } else {
           return $this->query_string($this->builder['sql'], $this->builder['bind']);
       }
   }


   /**
   */
   public function having($name, $value=null)
   {
       if ($name!='') {
           //$items=explode(',',$fields);

   $have = isset($this->builder['have']) ? $this->builder['have'] : array();

           if (is_array($name)) {
               $have=array_merge($have, $name);
           } elseif (is_string($name) && $value==null && $value!==0) {
               $have[]=$name;
           } else {
               $have["$name"]=$value;
           }

           $this->builder['have']=array_unique($have);
       }

       return $this;
   }


   /**
   */
   public function order_by($name, $value=null)
   {
       if ($name!='') {
           $order = isset($this->builder['order']) ? $this->builder['order'] : array();

           if (strtolower($value)=='random' && is_numeric($name)) {
               $name="RAND($name)";
               $value=null;
           } elseif (strtolower($value)=='random' && !is_numeric($name)) {
               $name="RAND()";
               $value=null;
           }


           if (is_string($name) && $value==null && $value!==0) {
               $order[]=$name;
           } else {
               $order["$name"]=$value;
           }

           $this->builder['order']=array_unique($order);
       }

       return $this;
   }



   /**
   * where?
   *
   * @param string $fields fields separated by comas e.g. title,email
   *
   * @return object
   */
   public function where($name, $value=null)
   {
       if ($name!='') {
           //$items=explode(',',$fields);


   $match = isset($this->builder['match']) ? $this->builder['match'] : array();

           if (is_array($name)) {
               $match=array_merge($match, $name);
           } elseif (is_string($name) && $value==null && $value!==0) {
               $match[]=$name;
           } else {
               $match["$name"]=$value;
           }

   //$fields=array_merge($fields,$items);
   $this->builder['match']=array_unique($match);
           ;
       }

       return $this;
   }

    public function where_in($name, array $value)
    {
        if (!isset($this->builder['wherein'])) {
            $this->builder['wherein']=array();
        }

        $this->builder['wherein'][]=['key'=>$name,'value'=>$value];
        return $this;
    }

    public function where_not_in($name, $value=null)
    {
        return $this->where_in('!!'.$name, $value);
    }

    public function or_where_not_in($name, $value=null)
    {
        return $this->where_in('||!!'.$name, $value);
    }

    public function or_like($name, $value=null, $wildcard='both')
    {
        if (is_array($name)) {
            foreach ($name as $key=>$value) {
                $this->like('||'.$key, $value, $wildcard);
            }
        } else {
            $this->like('||'.$name, $value, $wildcard);
        }
        return $this;
    }

    public function not_like($name, $value=null, $wildcard='both')
    {
        if (is_array($name)) {
            foreach ($name as $key=>$value) {
                $this->like('!!'.$key, $value, $wildcard);
            }
        } else {
            $this->like('!!'.$name, $value, $wildcard);
        }
        return $this;
    }

    public function or_not_like($name, $value=null, $wildcard='both')
    {
        if (is_array($name)) {
            foreach ($name as $key=>$value) {
                $this->like('||!!'.$key, $value, $wildcard);
            }
        } else {
            $this->like('||!!'.$name, $value, $wildcard);
        }
        return $this;
    }


    public function like($name, $value=null, $wildcard='both')
    {
        if ($name!='') {
            $like = isset($this->builder['like']) ? $this->builder['like'] : array();

            if (is_array($name)) {
                foreach ($name as $key=>$value) {
                    $this->like($key, $value, $wildcard);
                }
                return $this;
            } else {
                $prefix= ($wildcard=='both' || $wildcard=='before') ? '%' : '';
                $suffix= ($wildcard=='both' || $wildcard=='after') ? '%' : '';
                $like["$name"]=$prefix.$value.$suffix;
            }

            $this->builder['like']=array_unique($like);
        }

        return $this;
    }


    public function or_where($name, $value=null)
    {
        return $this->where('||'.$name, $value);
    }


    public function join($table, $cond, $type='')
    {
        $join=isset($this->builder['join']) ? $this->builder['join'] : array();
        $join[]=trim("{$type} join {$table} on $cond");
        $this->builder['join']=array_unique($join);
        return $this;
    }

    public function group_by($column)
    {
        $group=isset($this->builder['group']) ? $this->builder['group'] : array();
        if (is_array($column)) {
            $group=array_merge($group, $column);
        } else {
            $group[]=$column;
        }
        $this->builder['group']=array_unique($group);
        return $this;
    }


    public function distinct()
    {
        $this->builder['distinct']=true;
        return $this;
    }

    private function _prepare_builder()
    {
        $this->builder['bind']=array();
        $this->builder['sql']='';
    }

    private function _compile_where()
    {
        //where
     if (isset($this->builder['match'])) {
         $bindString = "";
         $i = 1;
         foreach ($this->builder['match'] as $key=>$value) {
             $opr = $this->contains($key, array('>','<','!','=')) ? '' : '=';

             if (substr($key, 0, 2)=='||') {
                 $join='or';
                 $key=substr($key, 2);
             } else {
                 $join='and';
             }

             if (is_int($key)) {
                 $bindString .= " {$join} {$value}";
             } else {
                 $this->builder['bind'][]=$value;
                 $bindString .= " {$join} {$key}{$opr}?";
             }
         }
         $this->builder['sql'].=$bindString;
     }


     //wherein
     if (isset($this->builder['wherein'])) {
         $bindString = "";
         $wherein=$this->builder['wherein'];
         foreach ($wherein as $item) {
             $key=$item['key'];
             $value=$item['value'];

             if (substr($key, 0, 2)=='||') {
                 $join='or';
                 $key=substr($key, 2);
             } else {
                 $join='and';
             }

             if (substr($key, 0, 2)=='!!') {
                 $not='not ';
                 $key=substr($key, 2);
             } else {
                 $not='';
             }


             $v=rtrim(str_repeat("?,", count($value)), ',');
             $bindString.=" {$join} $key {$not}in ($v)";

             $this->builder['bind']=array_merge($this->builder['bind'], $value);
         }
         $this->builder['sql'].=$bindString;
     }

     //like
     if (isset($this->builder['like'])) {
         $bindString = "";
         $like=$this->builder['like'];
         foreach ($like as $key=>$value) {
             if (substr($key, 0, 2)=='||') {
                 $join='or';
                 $key=substr($key, 2);
             } else {
                 $join='and';
             }

             if (substr($key, 0, 2)=='!!') {
                 $not='not ';
                 $key=substr($key, 2);
             } else {
                 $not='';
             }


             $bindString.=" {$join} $key {$not}like ? ESCAPE '!' ";
             $this->builder['bind'][]=$value;
         }


         $this->builder['sql'].=$bindString;
     }
    }

   /**
   * compile select
   */
   private function _compile_select()
   {
       $this->_prepare_builder();

       $fields=isset($this->builder['fields']) ? $this->builder['fields'] : array('*');

       $table=isset($this->builder['table']) ? $this->builder['table'] : 'table';

       if (isset($this->builder['join'])) {
           $join=' '.implode(" ", $this->builder['join']);
       } else {
           $join='';
       }


       $this->builder['sql']="select ". (isset($this->builder['distinct'])?'DISTINCT ':'') .implode(',', $fields) .' from ' . $table . $join . ' WHERE 1';


     //where
     $this->_compile_where();


     //group by
     if (isset($this->builder['group'])) {
         $this->builder['sql'].=' GROUP BY '.implode(",", $this->builder['group']);
     }



     //having
     if (isset($this->builder['have'])) {
         $bindString = "";
         $i = 1;
         foreach ($this->builder['have'] as $key=>$value) {
             $opr = $this->contains($key, array('>','<','!','=')) ? '' : '=';

             if (is_int($key)) {
                 $bindString .= " {$value},";
             } else {
                 $this->builder['bind'][]=$value;
                 $bindString .= " {$key} {$opr} ?,";
             }
         }
         $bindString=' HAVING '. trim($bindString, ',');
         $this->builder['sql'].=$bindString;
     }

     //order by
     if (isset($this->builder['order'])) {
         $bindString = "";
         $i = 1;
         foreach ($this->builder['order'] as $key=>$value) {
             if (is_int($key)) {
                 $bindString .= " {$value},";
             } else {
                 $bindString .= " {$key} {$value},";
             }
         }
         $bindString=' ORDER BY '. trim($bindString, ',');
         $this->builder['sql'].=$bindString;
     }


     //limit and offset
     if (isset($this->builder['limit'])) {
         $this->builder['sql'].=" LIMIT  ".$this->builder['limit'];
     }
       if (isset($this->builder['offset'])) {
           $this->builder['sql'].=" OFFSET ".$this->builder['offset'];
       }



       return $this;
   }


   /**
   * sets value of offset
   *
   * @param int $offset The offset value
   *
   */
   public function offset($offset=null)
   {
       if ($offset!=null) {
           $this->builder['offset']=(int) $offset;
       }
       if ($offset!=null && !isset($this->builder['limit'])) {
           $this->builder['limit']=100;
       }
       return $this;
   }

   /**
   * sets value of limit and offset
   *
   * @param int $value The limit value
   * @param int $offset The offset value
   *
   */
   public function limit($value, $offset=null)
   {
       $this->offset($offset);
       if ($value!=null) {
           $this->builder['limit']=(int) $value;
       }

       return $this;
   }

   /**
   * select fields from the database table
   *
   * @param string $fields fields separated by comas e.g. title,email
   *
   * @return object
   */
   public function select($fields=null)
   {
       if ($fields!='') {
           $items=explode(',', $fields);
           $fields= isset($this->builder['fields']) ? $this->builder['fields'] : array();
           $fields=array_merge($fields, $items);
           $this->builder['fields']=array_unique($fields);
           ;
       }


       return $this;
   }

    public function select_max($field, $alias=null)
    {
        return ($alias==null ? $this->select("max($field)") : $this->select("max($field) $alias"));
    }

    public function select_min($field, $alias=null)
    {
        return ($alias==null ? $this->select("min($field)") : $this->select("min($field) $alias"));
    }

    public function select_avg($field, $alias=null)
    {
        return ($alias==null ? $this->select("avg($field)") : $this->select("avg($field) $alias"));
    }

    public function select_sum($field, $alias=null)
    {
        return ($alias==null ? $this->select("sum($field)") : $this->select("sum($field) $alias"));
    }



   /**
   * sets table of query builder
   *
   * @param int $table The table of query builder
   *
   */
   public function from($table=null)
   {
       if ($table!=null) {
           $this->builder['table']=$table;
       }
       return $this;
   }



   /**
   * checks if a string contains strings in array
   *
   * @param string $str The target string
   * @param array|string $arr The strings array to match
   *
   */
   public function contains($str, $arr)
   {
       $arr=(array) $arr;

       $ptn = '';
       foreach ($arr as $s) {
           if ($ptn != '') {
               $ptn .= '|';
           }
           $ptn .= preg_quote($s, '/');
       }
       return preg_match("/$ptn/i", $str);
   }


   /**
   * Close database connection
   *
   * @return object
   */
   public function close()
   {
       $this->con=null;
       return $this;
   }


   /**
   * get a list of available drivers
   *
   * @param boolean $print Should result be printed?
   *
   * @return array
   */
   public function drivers($print=false)
   {
       $drivers=PDO::getAvailableDrivers();

       if ($print) {
           $this->stdout($drivers);
       }

       return $drivers;
   }



   /**
    * standard output message processing
    *
    * @param   mixed    $info        The variable to be displayed on screen/console
    * @param   bool  $exit        Should execution be ceased after output?
    *
    * @param   void
    */
   public function stdout($info, $exit=false)
   {
       $bt = debug_backtrace();
       $caller = array_shift($bt);

       $summary="";

       if (isset($caller['file']) && isset($caller['line'])) {
           $summary=$caller['file'].':'.$caller['line']."\n";
       }


       if (PHP_SAPI === 'cli') {
           print_r($info);
           echo "\n";
       } else {
           print '<pre style="padding: 1em; margin: 1em 0;">';
           echo "$summary";
           if (func_num_args() < 2) {
               print_r($info);
           } else {
               print_r($info);
           //print_r(func_get_args());
           }
           print '</pre>';
       }

       if ($exit) {
           exit();
       }
   }


   /**
   * gets all database tables
   *
   */
   public function list_tables()
   {
       $data=$this->query("show tables")->stmt->fetchAll();

       $tables=array();
       foreach ($data as $t) {
           $tables[]=$t[0];
       }
       return $tables;
   }

   /**
   * checks if a table exists
   *
   */
   public function table_exists($table, $field='1')
   {
       if ($this->query("select $field from $table", array(), true)) {
           return true;
       } else {
           return false;
       }
   }

   /**
   * checks if a field exists in a table
   */
   public function field_exists($field, $table)
   {
       return $this->table_exists($table, $field);
   }

   /**
   * Retrieves the current database object
   *
   * @param object $db (optional) A new PDO database object to set as default
   *
   * @return object
   */
   public function pdo($db=null)
   {
       if ($db!=null) {
           $this->con=$db;
       }
       $this->connect();

       return $this->con;
   }

   /**
   * returns the last insert id
   *
   * @oaram string $name The name of the sequence
   *
   * @return int
   */
   public function insert_id($name=null)
   {
       return (int) $this->con->lastInsertId($name);
   }






     /**
     *  insert_json_url
     *
    *  Shorthand for inserting multiple rows from a json url
    *
     *  The json structure looks like this:
     *
     *  [
     *    {
     *        "id": "1",
     *        "user_id": "1",
     *    },
     *    {
     *        "id": "2",
     *        "user_id": "1255",
     *    }
     *]
     *
    *  When using this method column names will be enclosed in grave accents " ` " (thus, allowing seamless usage of
    *  reserved words as column names) and values will be automatically {@link escape()}d in order to prevent SQL injections.
    *
    *  <code>
    *  $db->insert_json_url(
    *      'table',
    *      'http://localhost/sample.json');
    *  </code>
     *
     *   or
     *
     *  <code>
    *  $db->insert_json_url(
    *      'table',
    *      '/user/bin/sample.json');
    *  </code>
     *
     *
    *  @param  string  $table          Table in which to insert.
    *
    *  @param  string   $url            The url or local file resource to load data from
    *
    *
    *  @return boolean                 Returns TRUE on success of FALSE on error.
     *
    */
    public function insert_json_url($table, $url)
    {
        return $this->insert_json_string($table, file_get_contents($url));
    }
     /**
     *  insert_json_string
     *
    *  Shorthand for inserting multiple rows from a json string
    *
     *  The json structure looks like this:
     *
     *  [
     *    {
     *        "id": "1",
     *        "user_id": "1",
     *    },
     *    {
     *        "id": "2",
     *        "user_id": "1255",
     *    }
     *]
     *
    *  When using this method column names will be enclosed in grave accents " ` " (thus, allowing seamless usage of
    *  reserved words as column names) and values will be automatically {@link escape()}d in order to prevent SQL injections.
    *
    *  <code>
    *  $db->insert_json_url(
    *      'table',
    *      '[json string goes here]');
    *  </code>
     *
     *
    *  @param  string  $table          Table in which to insert.
    *
    *  @param  string  $json          The json array string
    *
    *
    *  @return boolean                 Returns TRUE on success of FALSE on error.
     *
    */
    public function insert_json_string($table, $json)
    {
        $data=(array) json_decode($json, true);
        return $this->insert_bulk($table, null, $data);
    }


        /**
      *  insert_bulk
      *
    	*  Shorthand for inserting multiple rows in a single query.
    	*
    	*  When using this method column names will be enclosed in grave accents " ` " (thus, allowing seamless usage of
    	*  reserved words as column names) and values will be automatically {@link escape()}d in order to prevent SQL injections.
    	*
    	*  <code>
    	*  $db->insert_bulk(
    	*      'table',
    	*      array('column1', 'column2'),
    	*      array(
    	*          array('value1', 'value2'),
    	*          array('value3', 'value4'),
    	*          array('value5', 'value6'),
    	*          array('value7', 'value8'),
    	*          array('value9', 'value10')
    	*      )
    	*  ));
    	*  </code>
    	*
    	*  @param  string  $table          Table in which to insert.
    	*
    	*  @param  array   $columns        An array with columns to insert values into.
    	*
    	*                                  Column names will be enclosed in grave accents " ` " (thus, allowing seamless
      *                                  usage of reserved words as column names).
      *                                  If columns is null, then it will be autogenerated
    	*
    	*  @param  array  $data           An array of an unlimited number of arrays containing values to be inserted.
    	*
    	*                                  Values will be automatically {@link escape()}d in order to prevent SQL injections.
    	*
    	*  @param  boolean $ignore         (Optional) By default, trying to insert a record that would cause a duplicate
    	*                                  entry for a primary key would result in an error. If you want these errors to be
    	*                                  skipped set this argument to TRUE.
    	*
    	*                                  For more information see {@link http://dev.mysql.com/doc/refman/5.5/en/insert.html MySQL's INSERT IGNORE syntax}.
    	*
    	*                                  Default is FALSE.
    	*
    	*  @return boolean                 Returns TRUE on success of FALSE on error.
         *
    	*/
        public function insert_bulk($table, $columns, $data, $ignore = false)
        {
            if (empty($columns)&&empty($data)) {
                trigger_error("You cannot pass an empty array to ".get_class($this).'::'."insert_bulk", E_USER_ERROR);
            }
            if ($columns==null) {
                $columns=array_keys($data[0]);
            }
            // we can't do array_values(array_pop()) since PHP 5.3+ as will trigger a "strict standards" error
            $values = array_values($data);
            // if $data is not an array of arrays
            if (!is_array(array_pop($values))) {
                // save debug information
            return false;
            }
            // if arguments are ok
            else {
                // start preparing the INSERT statement
                $sql = '
    				INSERT' . ($ignore ? ' IGNORE' : '') . ' INTO
    					`' . $table . '`
    					(' . '`' . implode('`,`', $columns) . '`' . ')
    				VALUES
    			';
                // iterate through the arrays and escape values
                foreach ($data as $values) {
                    $sql .= '(' . $this->implode($values) . '),';
                }
                // run the query
          $sql=trim($sql, ',');
          //echo $sql;
          //die();
          return $this->query($sql);
            }
            // if script gets this far, return false as something must've been wrong
            return false;
        }


      /**
         *  implode
         *
         *  Works similarly to PHP's implode() function with the difference that the "glue" is always the comma, and that
         *  this method {@link escape()}'s arguments.
         *
         *  <i>This was useful for escaping an array's values used in SQL statements with the "IN" keyword, before adding
         *  arrays directly in the replacement array became possible in version 2.8.6</i>
         *
         *  <code>
         *  $array = array(1,2,3,4);
         *
         *  //  this would work as the WHERE clause in the SQL statement would become
         *  //  WHERE column IN ('1','2','3','4')
         *  $db->query('
         *      SELECT
         *          column
         *      FROM
         *          table
         *      WHERE
         *          column IN (' . $db->implode($array) . ')
         *  ');
         *
         *
         *  $db->query('
         *      SELECT
         *          column
         *      FROM
         *          table
         *      WHERE
         *          column IN (?)
         *  ', array($array));
         *  </code>
         *
         *
         *  @param  array   $pieces     An array with items to be "glued" together
         *
         *
         *  @return string              Returns the string representation of all the array elements in the same order,
         *                              escaped and with commas between each element.
         *
         */
        public function implode($pieces)
        {
            $result = '';
            // iterate through the array's items and "glue" items together
            foreach ($pieces as $piece) {
                $result .= ($result != '' ? ',' : '') .  $this->escape($piece);
            }
            return $result;
        }


        /**
  *  dlookup
  *
  *  Returns one or more columns from ONE row of a table.
  *
  *  <code>
  *  // get name, surname and age of all male users
  *  $result = $db->dlookup('name, surname, age', 'users', 'gender = "M"');
  *
  *  // when working with variables you should use the following syntax
  *  // this way you will stay clear of SQL injections
  *  $result = $db->dlookup('name, surname, age', 'users', 'gender = ?', array($gender));
  *  </code>
  *
  *  @param  string  $column         One or more columns to return data from.
  *
  *                                  <i>If only one column is specified the returned result will be the specified
  *                                  column's value. If more columns are specified the returned result will be an
  *                                  associative array!</i>
  *
  *                                  <i>You may use "*" (without the quotes) to return all the columns from the
  *                                  row.</i>
  *
  *  @param  string  $table          Name of the table in which to search.
  *
  *  @param  string  $where          (Optional) A MySQL WHERE clause (without the WHERE keyword).
  *
  *                                  Default is "" (an empty string).
  *
  *  @param  array   $replacements   (Optional) An array with as many items as the total parameter markers ("?", question
  *                                  marks) in <i>$where</i>. Each item will be automatically {@link escape()}-ed and
  *                                  will replace the corresponding "?". Can also include an array as an item, case in
  *                                  which each value from the array will automatically {@link escape()}-ed and then
  *                                  concatenated with the other elements from the array - useful when using <i>WHERE
  *                                  column IN (?)</i> conditions. See second example {@link query here}.
  *
  *                                  Default is "" (an empty string).
  *
  *
  *  @return mixed                   Found value/values or FALSE if no records matching the given criteria (if any)
  *                                  were found. It also returns FALSE if there are no records in the table or if there
  *                                  was an error.
  *
  */
public function dlookup($column, $table, $where = '', $replacements = '')
{
    // run the query
 $query=$this->query('
   SELECT
     ' . $column . '
   FROM
     `'. $table . '`' .
 ($where != '' ? ' WHERE ' . $where : '') . '
   LIMIT 1
 ', $replacements);
 // if query was executed successfully and one or more records were returned
 if ($row=$query->row(0, 'array')) {
     // fetch the result
   // if there is only one column in the returned set
   // return as a single value
   if (count($row) == 1) {
       return array_pop($row);
   }
   // if more than one columns, return as an array
   else {
       return $row;
   }
 }
 // if error or no records
 return false;
}


    public function __get($name)
    {
        $this->connect();

        if (isset($this->con->$name)) {
            return $this->con->$name;
        } else {
            trigger_error("Call to undefined property ".get_class($this).'::'."$name", E_USER_ERROR);
            exit();
        }
    }


    public function __call($name, $arguments)
    {
        $this->connect();
        if (method_exists($this->con, $name)) {
            return call_user_func_array(array($this->con, $name), $arguments);
        } else {
            trigger_error("Call to undefined method ".get_class($this).'::'."$name", E_USER_ERROR);
        }
    }
}




/**
* dhtml pdo result object
*/
class dhtmlpdo_result
{

  /**
  *  pdo statement object
  *
  *  @access private
  *
  *  @var  resource
  */
  private $stmt;


  /**
  *  dhtmlpdo  object
  *
  *  @access private
  *
  *  @var  object
  */
  private $dhtmlpdo;

    public function __construct($stmt, $dhtmlpdo)
    {
        $this->stmt=$stmt;
        $this->dhtmlpdo=$dhtmlpdo;
    }

  /**
  * fetches unbuffered result
  *
  * @param string $class The class to use, possible values are array, default, or a custom class name
  *
  * @return array | object
  */
  public function unbuffered_row($class='stdClass')
  {
      if ($class=='array' && ($row=$this->stmt->fetch(\PDO::FETCH_ASSOC))) {
          return $row;
      } elseif ($class!='array' && $row=$this->stmt->fetchObject($class)) {
          return $row;
      }
  }





  /**
  * return result as an object
  *
  * @param string $class Name of class
  *
  * @return array
  */
  public function result($class='stdClass')
  {
      $results=array();
      while ($row=$this->stmt->fetchObject($class)) {
          $results[]=$row;
      }
      return $results;
  }



  /**
  * return result as an array
  */
  public function result_array()
  {
      $results=$this->stmt->fetchAll();

      return $results;
  }




  /**
  * Returns row count
  */
  public function num_rows()
  {
      return $this->stmt->rowCount();
  }




  /**
  * returns a single result as an object
  *
  * @param integer $pos Position of array
  * @param string $class Name of class
  *
  * @return object|null
  */
  public function row($pos=0, $class='stdClass')
  {
      $results=array();

      $count=0;
      while ($row= strtolower($class)=='array' ? $this->stmt->fetch(\PDO::FETCH_ASSOC) : $this->stmt->fetchObject($class)) {
          if ($count==$pos) {
              return $row;
          }
          $count++;
      }
  }


  /**
  * lists all fields of result set
  *
  */
  public function list_fields()
  {
      $columns=array();
      if (is_object($this->stmt)) {
          for ($i = 0; $i < $this->stmt->columnCount(); $i++) {
              $col = $this->stmt->getColumnMeta($i);
              $columns[] = $col['name'];
          }
      }
      return $columns;
  }


  /**
  * returns a single result as an array
  *
  * @param integer $pos Position of array
  *
  * @return array|null
  */
  public function row_array($pos=0)
  {
      $results=array();

      $count=0;
      while ($row=$this->stmt->fetch(\PDO::FETCH_ASSOC)) {
          if ($count==$pos) {
              return $row;
          }
          $count++;
      }
  }



    public function __get($name)
    {
        if (isset($this->stmt->$name)) {
            return $this->stmt->$name;
        } else {
            return $this->dhtmlpdo->$name;
        }
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->stmt, $name)) {
            return call_user_func_array(array($this->stmt, $name), $arguments);
        } else {
            return call_user_func_array(array($this->dhtmlpdo, $name), $arguments);
        }
    }
}
