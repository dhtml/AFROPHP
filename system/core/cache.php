<?php
/**
*  Cache class
*/
namespace System\Core;

defined('BASEPATH') or exit('No direct script access allowed');

class cache extends \System\Base\Prototype
{

/**
*  The number of seconds it will take for a cache to expire
*
*  @access private
*
*  @var  integer
*/
private $cache_time=30;


/**
*  cache_data
*
*  The current/last data stored in the cache
*
*  @access private
*
*  @var  object
*/
private $cache_data='';


/**
*  cache_dir
*
*  The directory where cache is stored
*
*  @access private
*
*  @var  string
*/
private $cache_dir;

/**
* class constructor
*/
public function __construct()
{
  $this->cache_dir=APPPATH.'cache';

  directory_usable($this->cache_dir);

  $this->cache_time=config_item('cache_time',0,true);
}


/**
*  This gets the value of a cache variable from file (or memory)
*
* @param  string   $key   The key of the cache
*
* @param  boolean   $fast   When set to true (default), it allows data loading from memory when possible
*
*  @return object  The value of the cache
*/
public function get($key,$fast=true)
{
if($fast) {
  if($this->cache_data->key==$key) {
    $this->cache_data->key=''; //invalidate key
    $data=$this->cache_data->data;
    return $data;
  }
}

    $cachefile=$this->keyfile($key);

    $cdata=$this->get_cache_file($cachefile);

    return $cdata->data;
}



/**
*  Saves data to the cache
*
* @param  string   $key   The key for storing data to the cache
*
* @param  object   $data   The object to be stored in the cache
*
* @param  integer   $cache_time (optional)   The length of time in seconds for cache (default is the global cache_time)
*
*  @return string the generated path for the key
*/
public function save($key, $data, $cache_time=null)
{
  $ctime = $cache_time==null ? $this->cache_time : $cache_time;


  if($ctime==0) {return false;}

    $cachefile=$this->keyfile($key);

    $cdata=array(
      'key'=>$key,
      'data'=>$data,
      'cache_time'=>$ctime,
    );

    $data='<?php '."\n".'$cdata=<<<end'."\n".serialize($cdata)."\n".'end;'."\n".'?>';

    $cached = fopen($cachefile, 'w');
    fwrite($cached, $data);
    fclose($cached);

    chmod($cachefile, 0777);

    return true;
}


/**
*  has
*
*  This checks if the cache still holds an unexpired copy of the data
*
* @param  string   $key   The key of the cache
*
*  @return boolean  true if the cache is still available, else false
*/
public function has($key)
{
    $cachefile=$this->keyfile($key);


    //load cache
    $cdata=$this->get_cache_file($cachefile);

    //no cache
    if($cdata->key!=$key) {return false;}


    // Serve from the cache if it is younger than $cachetime
    if (time() - $cdata->cache_time < filemtime($cachefile)) {
      $this->cache_data=$cdata;
      return true;
    } else {
      // If so we'll delete it.
      @unlink($cachefile);

      $this->cache_data=null;
      return false;
    }
}



/**
*  Forgets cache file if value matches
*
* @param  string   $key   The key of the cache
*
*  @return boolean  true if the cache is no more, else false
*/
public function forget($key)
{
    $cachefile=$this->keyfile($key);

    if(file_exists($cachefile)) {
      unlink($cachefile);
      return true;
    } else {
      return false;
    }
}

/**
*  Flushes all cache files from disk
*
*  @return boolean
*/
public function flush()
{
$files=browse($this->cache_dir,array('/is','/sd'),'*.php');
if(empty($files)) {return false;}
foreach($files as $file)
{
  unlink($file);
}

return true;
}

/**
*  Loads the cache file from disk and retrieves the array structure
*
* @param  string   $cachefile   The path of the cache file
*
*  @return object  the cache object
*/
private function get_cache_file($cachefile) {
  $cdata=(object) array('key'=>'','data'=>'','cache_time'=>$this->cache_time);
  if (!file_exists($cachefile)) {return $cdata;}

  include $cachefile;
  $cdata=unserialize($cdata);

  if(!isset($cdata['key'])) {$cdata['key']='';}
  if(!isset($cdata['data'])) {$cdata['data']='';}
  if(!isset($cdata['cache_time'])) {$cdata['cache_time']=$this->cache_time;}

  return (object) $cdata;
}

/**
*  Generates the file path for a given key
*  It also creates the cache folder if it does not exist
*
* @param  string   $key   The key for storing data to the cache
*
*  @return string the generated path for the key
*/
private function keyfile($key)
{
    if (!is_dir($this->cache_dir)) {
        mkdir($this->cache_dir, 0755);
    }

    $key=md5($key);
    $path=rtrim($this->cache_dir, '/')."/$key".".php";
    return $path;
}


/**
*  Sets the cache time in seconds
*
* @param  int   $cache_time   The time you wish to set for the cache
*
*  @return integer the current cache time
*/
public function time($cache_time=null)
{
    if ($cache_time!=null) {
        $this->cache_time=$cache_time;
    }
    return $this->cache_time;
}

/**
*  Sets the cache directory
*
* @param  string   $cache_dir   The directory to store the cache
*
* @return string   the current cache directory
*/
public function dir($cache_dir=null)
{
    if ($cache_dir!=null) {
        $this->cache_dir=$cache_dir;
    }
    return $this->cache_dir;
}
}
