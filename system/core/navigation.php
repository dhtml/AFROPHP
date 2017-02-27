<?php
namespace System\Core;

defined('BASEPATH') or exit('No direct script access allowed');

class Navigation extends \System\Base\Singleton {

  /**
  * List of registered routes
  *
  * @var	array
  */
  public $menus=array();


  /**
  * registers a menu with navigation
  *
  * @param  object	$menu
  *
  * @return object
  */
  public function register(& $menu)
  {
    $key=$menu->getKey();
    if(is_null($key) || empty($key)) {
      $key=mt_rand()+time();
      $menu->setKey($key);
    }
    $this->menus["$key"]=$menu;
    return $this;
  }

  /**
  * searches for and retrieves a registered menu object by its key
  *
  * @param  string	$key The key of the menu
  *
  * @return object
  */
  public function find($key)
  {
    return isset($this->menus["$key"]) ? $this->menus["$key"] : null;
  }

  /**
  * compiles all the current menus and produces an associative array of the menus
  *
  * @return array
  */
  public function compile()
  {
    $precompile=array();


    foreach($this->menus as $menu)
    {
      $data=$menu->getData();

      //gkey can be used to sort
      $gkey= (is_null($data['parent']) && empty($data['parent'])) ? null : $data['parent'];
      $gkey=  is_null($gkey) ? $data['key'] : $gkey;


      $data['_gkey']= $gkey;
      $precompile[$data['key']]=$data;
    }


    return $precompile;
  }


}
