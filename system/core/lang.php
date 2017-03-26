<?php
namespace System\Core;

defined('BASEPATH') or exit('No direct script access allowed');


class lang extends \System\Base\Singleton
{
    /**
     * List of Languages
     *
     * @var	array
     */
    public $data=Array();

    public $loaded=Array();


    /**
    * load language from file
    *
    * <code>
    * load('commander', '../en.xml');
    * </code>
    *
    * @param    string    $plugin  The name of the plugin
    * @param    mixed     $path     The full path of the xml language file
    *
    * @return object
    */
    public function load($plugin, $path)
    {
      $plugin=strtolower(trim($plugin));

      if(!file_exists($path)) {return;}

      $this->loaded["$plugin"][]=$path;

      $xmlstr=file_get_contents($path);

      $xmlcont=xmlstring2array($xmlstr);

      foreach($xmlcont as $name=>$value)
      {
        $this->data[$plugin.'_'.$name]=$value;
      }

       return $this;
    }


    /**
    * text
    *
    * translates a token from the language file loaded for a plugin
    *
    * <code>
    * text('base', 'greet','hello');
    * </code>
    *
    * @param    string    $plugin   The name of the plugin
    * @param    string    $name      The name of the token to translate
    * @param    string    $default   The default string to return in case there is no translation
    *
    * @return string
    */
    public function text($plugin, $name,$default=null)
    {
      $plugin=strtolower(trim($plugin));
      $key=$plugin.'_'.$name;

      if($default==null) {$default="$key";}

      return isset($this->data[$key]) ? $this->data[$key] : $default;
    }


}
