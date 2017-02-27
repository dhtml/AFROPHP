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
      if(isset(get_instance()->theme->$key)) {return get_instance()->theme->$key;}
      else if(isset(get_instance()->loader->$key)) {return get_instance()->loader->$key;}
      return get_instance()->$key;
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
      if(method_exists(get_instance()->theme,$name)) {$result=call_user_func_array(array(get_instance()->theme, $name),$arguments);}
      else if(method_exists(get_instance()->loader,$name)) {$result=call_user_func_array(array(get_instance()->loader, $name),$arguments);}
      else if(method_exists(get_instance(),$name)) {$result=call_user_func_array(array(get_instance(), $name),$arguments);}
      else {
        show_error("There is no method called: {$name}()");
      }

      return $result;
    }

}
