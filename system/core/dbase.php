<?php
defined('BASEPATH') or exit('No direct script access allowed');

include_once BASEPATH."base/dhtmlpdo.php";

class Dbase extends DHTMLPDO
{


/**
* retrieves the current database configuration
*
* @return array
*/
public function get_config()
{
  $dbase['dsn']=config_item('dbase_dsn');
  $dbase['driver']=config_item('dbase_driver');
  $dbase['database']=config_item('dbase_name');
  $dbase['hostname']=config_item('dbase_hostname');
  $dbase['username']=config_item('dbase_username');
  $dbase['password']=config_item('dbase_password');
  $dbase['port']=config_item('dbase_port',null,true);
  $dbase['char_set']=config_item('dbase_char_set','');
  $dbase['dbcollat']=config_item('dbase_collat','');
  $dbase['prefix']=config_item('dbase_prefix');
  $dbase['schema']=config_item('dbase_schema','public');
  $dbase['cache']=config_item('dbase_cache',false);
  $dbase['persistent']=config_item('dbase_persistent',false);
  return $dbase;
}


/**
* Attempts to open a connection if one is not opened
* and returns the status of the connection
*
* @return boolean
*/
 public function connect()
 {
   static $initialized;

   if(!$initialized) {
     $config=$this->get_config();

     self::configure($config);
     $initialized=true;
   }

   return parent::connect();
 }


}
