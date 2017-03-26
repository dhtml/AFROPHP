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
    public function bind($name, $callback,$object=null)
    {
        $this->events["$name"][]=array('callback'=>$callback,'object'=>$object);
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
    public function unbind($name, $callback, $object=null)
    {
      if(!isset($this->events["$name"])) {return $this;}
      $e=$this->events["$name"];
      $s=array('callback'=>$callback,'object'=>$object);
      $k=array_search($s,$e);
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

      if(!is_array($params)) {$params=array();}

      return $this->_trigger($name,$params);
    }

    public function _trigger($name,&$params=array())
    {
      $e=$this->events["$name"];
      foreach($e as $evt) {
        $callback=$evt['callback'];
        $object=$evt['object'];

        if(is_object($object)) {
          call_user_func_array(array($object, $callback),$params);
        } else if(is_string($callback)) {
          call_user_func_array($callback,$params);
        } else if(is_object($callback)) {
          call_user_func_array($callback,$params);
        }
      }

      return $this;
    }
}
