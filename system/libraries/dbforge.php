<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once __DIR__."/dhtmlsql.php";

class Dbforge
{

  /**
   * Creates a connection to the database via dhtmlsql
   *
   * @return	void
   */
    public function __construct()
    {
      static $init;
      if($init) {return;}
      $this->dhtmlsql_connect();
      $init=true;
    }

    /**
    * dhtmlsql_connect
    *
    * Sets dhtmlsql as the default database driver
    *
    * @return void
    */
    private function dhtmlsql_connect()
    {
      $dbase['database']=config_item('dbase_name');
      $dbase['hostname']=config_item('dbase_hostname');
      $dbase['username']=config_item('dbase_username');
      $dbase['password']=config_item('dbase_password');
      $dbase['port']=config_item('dbase_port',null,true);
      $dbase['char_set']=config_item('dbase_char_set');
      $dbase['dbcollat']=config_item('dbase_collat');
      $dbase['prefix']=config_item('dbase_prefix');



      get_instance()->db = $db = DHTMLSQL::connect($dbase['hostname'], $dbase['username'], $dbase['password'], $dbase['database'], $dbase['port']);


      if(!$db->connected()) {
        show_error("Unable to connect to database. Reason: ".$db->connect_error(),'Failed To Connect To Database');
      } else {
        //optionally set your character-set and collation here
        $db->set_charset($dbase['char_set'],$dbase['dbcollat']);

        //set database prefix
        $db->prefix($dbase['prefix']);
      }
    }
}
