<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Menu
{


  /**
   * Menu item data
   *
   * @var	array
   */
   private $data=array();

  /**
  * route class constructor, it also registers the menu with navigation
  *
  * @param  string    $key              The key of the menu (optional)
  *
  * @return void
  */
  public function __construct($key=null)
  {

    //set defaults
    $this->data=array('key'=>$key,'priority'=>null,'parent'=>null,'title'=>'','uri'=>'','icons'=>array());


    //register menu with navigation
    get_instance()->navigation->register($this);
  }

 /**
 * creates a new instance of the menu object
 *
 * @param   string    $key    The key of the menu
 *
 * @return object
 */
  private static function create($key)
  {
    return new Menu($key);
  }


   /**
   * get
   *
   * gets an old instance with key else creates a new instance of the menu object
   *
   * @param  string $key  The unique key of the menu
   *
   * @param  boolean $overwrite  Should the menu be created afresh/overwritten if key exists?
   *
   *
   * @return object
   */
   public static function get($key,$overwrite=false)
   {
     $obj= $overwrite==true ? null : get_instance()->navigation->find($key);

     if($obj==null) {
       $obj=self::create($key);
       $obj->isNew=true;
     } else {
       $obj->isNew=false;
     }

     return $obj;
   }

   /**
   * Retrieves the entire data of the menu item
   *
   * @return object
   */
   public function getData()
   {
     return $this->data;
   }

   /**
   * Sets the entire data of the menu item
   *
   * @return voids
   */
   public function setData($data)
   {
     $this->data=$data;
   }

  /**
  * This is used to set or get variables from the menu object
  *
  * setting property:
  * menu::get('cms_home_dash_board')
  *  ->setParent('cms_home')
  *  ->setTitle('dashboard')
  *  ->setUri('admin');
  *
  * how to retrieve property:
  * echo menu::get('cms_home_dash_board')->getParent();
  *
  */
  function __call($func,$args)
  {
    $par1=isset($args[0]) ? $args[0] : null;

    $fx=strtolower($func);

    $cmd=substr($fx,0,3);
    $op=substr($fx,3);

    switch($cmd) {
    case 'set':
    $this->data["$op"]=$par1;
    break;
    case 'get':
    return isset($this->data["$op"]) ? $this->data["$op"] : null;
    break;
    default:
    show_error("Menu item can only accept get or set directives");
    break;
    }

    return $this;
  }

}
