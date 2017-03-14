<?php
namespace System\Base;

/**
 * Base Pattern.
 *
 * Modern implementation.
 */
class Prototype
{


  /**
   * Call this method to get singleton
   */
  public static function instance()
  {
      static $instance = false;
      if ($instance === false) {
          // Late static binding (PHP 5.3+)
      $instance = new static();
      }

      return $instance;
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



      //instantiate dbase on demand
      switch($key) {
        case 'db':
        return get_instance()->load->database();
        break;
        case 'security':
        if(isset(get_instance()->security)) {return get_instance()->security;}
        include BASEPATH."core/security.php";
        return get_instance()->security =  \System\Core\security::instance();
        case 'lang':
        if(isset(get_instance()->lang)) {return get_instance()->lang;}
        include BASEPATH."core/lang.php";
        return get_instance()->lang =  \System\Core\lang::instance();

        case 'input':
        if(isset(get_instance()->input)) {return get_instance()->input;}
        include BASEPATH."core/input.php";
        return get_instance()->input =  new \System\Core\Input();

        case 'navigation':
        if(isset(get_instance()->navigation)) {return get_instance()->navigation;}
        include BASEPATH."core/navigation.php";
        return get_instance()->navigation =  \System\Core\navigation::instance();


        default:
        $lkey=strtolower($key);
        if(isset(get_instance()->theme->$lkey)) {return get_instance()->theme->$lkey;}
        else if(isset(get_instance()->loader->$lkey)) {return get_instance()->loader->$lkey;}
        else if(isset(get_instance()->$lkey)) {return get_instance()->$lkey;}
        else if(isset(get_instance()->theme->$key)) {return get_instance()->theme->$key;}
        else if(isset(get_instance()->loader->$key)) {return get_instance()->loader->$key;}
        else if(isset(get_instance()->$key)) {return get_instance()->$key;}

        else {
          show_error("There is no object called $key currently loaded",500,"Fatal Error");
        }
      }
    }


    /**
    * __call
    *
    * reroute method calls to the framework
    *
    * @return mixed
    */
    public function __call($name, $arguments)
    {
      $lname=strtolower($name);

      if(method_exists(get_instance()->theme,$lname)) {$result=call_user_func_array(array(get_instance()->theme, $lname),$arguments);}
      else if(method_exists(get_instance()->loader,$lname)) {$result=call_user_func_array(array(get_instance()->loader, $lname),$arguments);}
      else if(method_exists(get_instance(),$lname)) {$result=call_user_func_array(array(get_instance(), $lname),$arguments);}
      else if(method_exists(get_instance()->theme,$name)) {$result=call_user_func_array(array(get_instance()->theme, $name),$arguments);}
      else if(method_exists(get_instance()->loader,$name)) {$result=call_user_func_array(array(get_instance()->loader, $name),$arguments);}
      else if(method_exists(get_instance(),$name)) {$result=call_user_func_array(array(get_instance(), $name),$arguments);}

      else {
        //trigger_error("There is no method called ".get_class($this).'::'.$name, E_USER_ERROR);
        show_error("There is no method called: {$name}() currently loaded ",500,"Fatal Error");
      }
    }

}




/**
 * Singleton Pattern.
 *
 * Modern implementation.
 */
class Singleton extends \System\Base\Prototype
{


    /**
     * Make constructor private, so nobody can call "new Class".
     */
    public function __construct()
    {
    }

    /**
     * Make clone magic method private, so nobody can clone instance.
     */
    private function __clone()
    {
    }

    /**
     * Make sleep magic method private, so nobody can serialize instance.
     */
    private function __sleep()
    {
    }

    /**
     * Make wakeup magic method private, so nobody can unserialize instance.
     */
    private function __wakeup()
    {
    }

}
