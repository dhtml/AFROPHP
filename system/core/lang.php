<?php
namespace System\Core;

defined('BASEPATH') or exit('No direct script access allowed');

use Stichoza\GoogleTranslate\TranslateClient;


class lang extends \System\Base\Singleton
{
    /**
     * List of Languages
     *
     * @var	array
     */
    public $data=Array();


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

    /**
    * translate
    *
    * Translate a block of text from one language to another
    *
    * This translation uses internet
    *
    * @param  string  $text     The text to translate
    * @param  string  $to       The language to translate to (default is current language)
    * @param  string  $from     The language to translate from (default is auto)
    *
    * @return   string
    */
    public function translate($text,$to=null,$from='auto')
    {

      $langfile=APPPATH."config/lang/translation.xml";
      static $_data;
      if (empty($_data) && file_exists($langfile)) {
        $_data= xmlstring2array($langfile);
      }

      if(!is_array($_data)) {$_data=array();}





      $from= is_null($from) ? 'auto': $from;
      $to= is_null($to) ? config_item('language'): $to;

      //if data exist in cache, just return it
      $key=base64_encode("{$from}{$to}{$text}");
      if(isset($_data[$key])) {return $_data[$key];}

      //initiate translation
      $tr = new TranslateClient(); // Default is from 'auto' to 'en'
      $tr->setSource($from);
      $tr->setTarget($to);

      //get value from internet
      $value=$tr->translate($text);

      $_data["$key"]=$value;
      $str=array2xml($_data,'resources');
      file_force_contents($langfile,$str);

      return $value;
    }


}
