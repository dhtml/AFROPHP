<?php
/**
*
* system/core/loader.php contains a list of functions responsible for loading extensions
*
*/
namespace System\Core;

defined('BASEPATH') or exit('No direct script access allowed');

  class Loader extends \System\Base\Singleton
  {

    /**
    * List of packaged paths
    *
    * These are paths where reosurces are searched
    *
    * @var	array
    */
    protected $package_paths =	array(APPPATH,BASEPATH);


    /**
    * List of plugins
    *
    * @var	array
    */
    protected $plugins =	array('enabled'=>array(),'disabled'=>array());

    /**
    * locate a file in any of the registered package paths
    * you can specify the extension of the file (or .php will be assumed)
    *
    *<code>
    * $path=$app->load->locate('core/config');
    *
    * $path=$app->load->locate('core/config.php');
    *
    * $path=locate_file('core/config');
    *</code>
    *
    * @param  string    $spath      A shortened version of the path e.g. core/config, core/config.php, libraries/session
    * @param  boolean   $break      if the file is not found, apllication exits when set to true
    *
    * @return the full path of the file
    */
    public function locate($spath,$break=true)
    {
      if(file_exists($spath)) {return $spath;}

      $p=Array();

      foreach($this->package_paths as $pkpath) {
        $path=rtrim($pkpath,'/')."/{$spath}";
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if(empty($ext)) {$path.=".php";}
        if(file_exists($path)) {return $path;}
      }

      if($break) {
        $p=implode(" ",$p);
        //stdout($this->package_paths);
        show_error("Unable to find $spath in any of the package paths $p.",500,"Unable to locate file");
      }

      return null;
    }


    /**
    * Generic File Loader
    *
    * @param	string	$path	File path
    * @param	bool	$return	Should the file output be returned as a string?
    * @return	object|string
    */
    public function file($path, $return = FALSE)
    {
      return $this->load_file('',$path,false,array(),$return);
    }


    /**
     * Searches for and locates a file
     *
     * <code>
     * load_file('core','/config.php');
     * </code>
     *
     * if the file exists as config.php, then it is loaded
     * otherwise locate(core/config) is called to search for the file in the package paths
     *
     * @param	string	$sdir	  The partial directory e.g. views,libraries,models
     * @param	string	$spath	The full path of the file or the subpath under app or system folders
     * @param	boolean	$once		The directive whether to include or include_once
     * @param	array	  $params	Set parameters to extract before the view is loaded
     * @param	boolean	$return	Should the output of the file be returned?
     * @return	mixed
     */
      public function load_file($sdir,$spath, $once=false, $params=array(), $return=false)
      {
          if (is_array($params) && !empty($params)) {
              extract($params);
          }

          $path=file_exists($spath) ? $spath : $this->locate(rtrim($sdir,'/').'/'.ltrim($spath,'/'));


          if ($return) {
              ob_start();
          }
          if ($once) {
              include_once $path;
          } else {
              include $path;
          }
          if ($return) {
              $contents=ob_get_contents();
              ob_end_clean();
              return $contents;
          }
      }


    /**
     * Loads and instantiates libraries. Designed to be called from application controllers.
     *
     * @param	  mixed	    $library		  Name of library (or array of libraries)
     * @param	  array	    $params		   Optional parameters to pass to the library class constructor
     * @param	  string	 $object_name	An optional object name to assign to
     * @return	library object
     */
    public function library($library, $params = null, $object_name = null)
    {
        if (is_array($library)) {
            foreach ($library as $key => $value) {
                if (is_int($key)) {
                    $this->library($value, $params);
                } else {
                    $this->library($key, $params, $value);
                }
            }
            return $this;
        }

        $this->load_file("libraries","$library", true);

        if ($object_name==null) {
            $object_name=toClassName($library);
        }

        $obj = get_instance()->$object_name=load_class($library,$params);
        return $obj;
    }

    /**
     * Loads and instantiates libraries. Designed to be called from application controllers.
     *
     * @param	  mixed	    $library		  Name of library (or array of libraries)
     * @param	  array	    $params		   Optional parameters to pass to the library class constructor
     * @param	  string	 $object_name	An optional object name to assign to
     * @return	library object
     */
    public function libraries($library, $params = null, $object_name = null)
    {
      return $this->library($library,$params,$object_name);
    }


    /**
    * Loads and instantiates models.
    *
    * @param	mixed	$model		Name of model (or array of models)
    * @param	array	$params		Optional parameters to pass to the class constructor
    * @param	string	$object_name		An optional object name to assign to
    * @return	model object
    */
    public function models($model, $params = null, $object_name = '')
    {
      return $this->model($model,$params,$object_name);
    }

    /**
    * Loads and instantiates models.
    *
    * @param	mixed	$model		Name of model (or array of models)
    * @param	array	$params		Optional parameters to pass to the class constructor
    * @param	string	$object_name		An optional object name to assign to
    * @return	model object
    */
    public function model($model, $params = null, $object_name = '')
    {
    if (empty($model))
    {
      return $this;
    }
    elseif (is_array($model))
    {
      foreach ($model as $key => $value)
      {
        is_int($key) ? $this->model($value, $params,$value) : $this->model($key, $params, $value);
      }
      return $this;
    }

    $this->load_file("models","$model", true);


    if ($object_name==null) {
        $object_name=toClassName($model);
    }


    $obj = get_instance()->$object_name=load_class($model,$params);

    return $obj;
    }

 /**
 * Loads "view" files.
 *
 * @param	string	$view	View name
 * @param	array	$vars	An associative array of data
 *				to be extracted for use in the view
 * @param	bool	$return	Whether to return the view output
 *				or leave it to the Output class
 * @return	object|string
 */
public function view($view, $vars = array(), $return = false)
{
    $response=$this->load_file("views","$view", false, $vars,$return);
    if ($return) {
        return $response;
    } else {
        echo $response;
    }
}

/**
* Loads "view" files.
*
* @param	string	$view	View name
* @param	array	$vars	An associative array of data
*				to be extracted for use in the view
* @param	bool	$return	Whether to return the view output
*				or leave it to the Output class
* @return	object|string
*/
public function views($view, $vars=array(), $return = false)
{
  return $this->view($view,$vars,$return);
}




	/**
	* Loads helper functions from file
	*
  * @param	mixed	$helper		Name of helper (or array of helpers)
  *
	* @return	object loader
	*/
	public function helper($helper)
	{
		if (empty($helper))
		{
			return $this;
		}
		elseif (is_array($helper))
		{
			foreach ($helper as $key => $value)
			{
				is_int($key) ? $this->helper($value) : $this->helper($key);
			}
			return $this;
		}

    $this->load_file("helpers","$helper", true);

    return $this;
  }

  /**
	* Loads helper functions from file
	*
	* @param	mixed	$helper		Name of helper (or array)
	* @return	object loader
	*/
  public function helpers($helper)
  {
    return $this->helper($helper);
  }


    /**
  	 * Return a list of all package paths.
  	 *
  	 * @return	array
  	 */
  	public function get_package_paths()
  	{
  		return $this->package_paths;
  	}

    /**
  	 * Add Package Path
  	 *
  	 * Prepends a parent path to the library, model, helper and config
  	 * path arrays.
  	 *
  	 * @param	string	$path		Path to add
  	 * @return	object loader
  	 */
  	public function add_package_path($path)
  	{
      $this->package_paths[]=rtrim($path,'/').'/';
      $this->package_paths=array_unique($this->package_paths);
      return $this;
    }

    /**
     * Remove a path from the library, model, helper and/or config
     * path arrays if it exists.
     *
     * @param	string	$path	Path to remove
     * @return	object
     */
    public function remove_package_path($path)
    {
      $key = array_search($path,$this->package_paths);

      if($key) {
        unset($this->package_paths[$key]);
      }

      return $this;
    }

    /**
    * Validates a plugin config to make sure it is formated correctly
    *
    * The config must contain a minimum of name and version to pass
    *
    * @param    string    $plugin     The name of the plugin
    * @param    array    $config      The configuration of the plugin
    *
    * @return boolean
    */
    public function validate_plugin_config($plugin,$config) {
      if(!is_array($config)) {
        log_message('debug',"$plugin must contain an array with name config");
        return false;
      }
      if(!isset($config['name'])) {
        log_message('debug',"$plugin must contain a name element");
        return false;
      }
      if(!isset($config['version'])) {
        log_message('debug',"$plugin must contain a version element");
        return false;
      }
      return true;
    }

    /**
    * load all enabled plugins
    *
    * @return   object loader
    */
    public function plugins() {
      $this->parse_plugins(APPPATH."plugins");
      return $this;
    }

    /**
    * parses all plugins in the specified folders and subfolders
    *
    * @param  string  $dir    The name of the directory to scan
    *
    * @return object
    */
    public function parse_plugins($dir)
    {
      if(!is_dir($dir)) {return;}
      //parse plugin
      $plugins=browse($dir,array('/is','/sd','/sd'),'plugin.xml');

      //load each enabled plugin
      foreach($plugins as $plugin) {
        $this->plugin($plugin);
      }

      return $this;
    }

    /**
    * loads a specific plugin by path
    *
    * @param  string    $plugin     The filepath of the plugin
    *
    * @return   object
    */
    public function plugin($plugin) {

      //load module xml config
      $str=file_get_contents($plugin);
      $config=xmlstring2array($str);

      $config=isset($config) ? $config : null;
      if(!$this->validate_plugin_config($plugin,$config)) {
        return;
      }
      extract($config);

      if(!isset($enable)) {$enable=false;}

      $_mod=$config;
      $_mod['path']=rtrim(pathinfo($plugin,PATHINFO_DIRNAME),'/');

      if($enable==true) {
        $this->plugins['enabled'][strtolower("$name")]=$_mod;
        $this->plugin_init($_mod);
      } else {
        $this->plugins['disabled'][strtolower("$name")]=$_mod;
      }
    }

    /**
    * initializes a plugin
    *
    * @param  array   $plugin    Array containing information about the plugin
    *
    * <example>
    *    Array
    *    (        [name] => CMS
    *    [description] => Default content management plugin
    *    [author] => Anthony Ogundipe
    *    [authorEmail] => info@africoders.com
    *    [authorUrl] => http://www.africoders.com
    *    [package] => core
    *    [version] => 1
    *    [build] => 1
    *    [enable] => 1
    *    [copyright] => © 2017 All rights reserved.
    *    [path] => /Users/dhtml/Sites/www/afrophp/system/plugins/cms
    *    )
    *  </example>
    *
    * @return object
    */
    public function plugin_init($plugin)
    {
      //add package path
      $this->add_package_path($plugin['path']);

      //load language files of the current module
      $lpath=$plugin['path']."/languages/".config_item('language','en');

      $files=browse($lpath,array('/is','/sd','/sd'),'*.xml');
      foreach($files as $file) {
          $this->lang->load($plugin['name'],$file);
      }

      //load php files from the bool directory
      $bpath=$plugin['path']."/bool/";

      $files=browse($bpath,array('/is','/sd','/sd'),'*.php');
      foreach($files as $file) {
          include ($file);
      }

      if(MODE=='cli') {
        global $console_directives;

        //load all console commands
        $bpath=$plugin['path']."/console/";

        $files=browse($bpath,array('/is','/sd','/sd'),'*.php');
        foreach($files as $file) {
          $console_directives[]=$file;
        }
      }

      //load initialization file
      if(file_exists($plugin['path']."/plugin.php")) {
        include $plugin['path']."/plugin.php";
      }

      return $this;
    }


    /**
    * returns an array of plugins (both enabled and disabled)
    *
    * @return array
    */
    public function get_plugins()
    {
      return $this->plugins;
    }

    /**
    * Finds a plugin from the list of plugins
    *
    * @param  string    $name     The name of the plugin e.g. mage
    *
    * @return array
    */
    public function find_plugin($name)
    {
      $plugins=$this->plugins;

      if(isset($plugins['enabled']["$name"])) {
        $plugin=$plugins['enabled']["$name"];
      } else if(isset($plugins['disabled']["$name"])) {
        $plugin=$plugins['disabled']["$name"];
      } else {
        $plugin=array();
      }

      return $plugin;
    }



}
