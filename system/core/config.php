<?php
namespace System\Core;


defined('BASEPATH') or exit('No direct script access allowed');

class Config
{
    /**
     * List of all loaded config values
     *
     * @var	array
     */
    public $config = array();

    /**
     * Class constructor
     *
     * @return	void
     */
    public function __construct()
    {
      $this->load(APPPATH."config/default.settings.php");
      $this->load(APPPATH."config/settings.php");

        //constant to determine state of UTF-8 compatibility
        define('UTF8_ENABLED',config_item('charset','UTF-8')=='UTF-8'?true:false);
    }


    /**
     * Load configuration files
     *
     * @param	string	$path	The path can be a directory holding configuration files or a single configuration file
     *                      A configuration file must have .xml extension
     *
     * @return	object
     */
    public function load($path)
    {
      $ext=pathinfo($path,PATHINFO_EXTENSION);
      if($ext!='php') {
        //loads all xml config in the path
        $files=browse($path,array('/sd'),'*.php');
        foreach($files as $file) {
          $this->load($file);
        }
        return;
      } else {
        //load a single xml config file
        if(file_exists($path)) {
          $this->config = &get_config(array(),$path);
        }
      }

    }

    /**
     * Fetch a config file item
     *
     * @param	string	$item	Config item name
     * @param	string	$index	Index name
     * @return	string|null	The configuration item or NULL if the item doesn't exist
     */
    public function item($item, $index = '')
    {
        if ($index == '') {
            return isset($this->config[$item]) ? $this->config[$item] : null;
        }
        return isset($this->config[$index], $this->config[$index][$item]) ? $this->config[$index][$item] : null;
    }

    /**
     * Set a config file item
     *
     * @param	string	$item	Config item key
     * @param	string	$value	Config item value
     * @return	void
     */
    public function set_item($item, $value)
    {
        $this->config[$item] = $value;
    }
}
