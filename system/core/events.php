<?php
namespace System\Core;

defined('BASEPATH') or exit('No direct script access allowed');

class events extends \System\Base\Singleton
{
    /**
     * List of Events
     *
     * @var	array
     */
    public $events=Array();

    /**
    * bind
    *
    * binds an event
    *
    * <code>
    * bind('menu', 'core_output_actions4');
    *
    * bind('menu', function() {
    *      echo "Mod Init Stub 3<br/>";
    * });
    * </code>
    *
    * @param    string    $name         The name of the event
    * @param    mixed     $callback     The event callback
    *
    * @return object
    */
    public function bind($name, $callback)
    {
        $this->events["$name"][]=$callback;
        return $this;
    }

    /**
    * unbinds an event
    *
    * <code>
    * unbind('menu', 'core_output_actions4');
    * </code>
    *
    * @param    string    $name         The name of the event
    * @param    mixed     $callback     The event callback
    *
    * @return object
    */
    public function unbind($name, $callback)
    {
      if(!isset($this->events["$name"])) {return $this;}
      $e=$this->events["$name"];
      $k=array_search($callback,$e);
      unset($this->events["$name"]["$k"]);
      return $this;
    }

    /**
    * trigger
    *
    * triggers an event
    *
    * <code>
    * trigger('menu');
    * </code>
    *
    * @param    string    $name               The name of the event
    *
    * @param    mixed     $parameters         The parameters of the event
    *
    * @return object
    */
    public function trigger($name,$params=array())
    {
      if(!isset($this->events["$name"])) {return $this;}
      $e=$this->events["$name"];
      $params=(array) $params;
      foreach($e as $evt) {
        if(is_string($evt)) {
          call_user_func_array($evt,$params);
        } else if(is_object($evt)) {
          call_user_func_array($evt,$params);
        }
      }

      return $this;

    }
}
