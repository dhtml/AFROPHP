<?php
defined('BASEPATH') or exit('No direct script access allowed');

$this->registerPlugin("function","decorator", "theme_decorator_func");

/**
* theme_decorator_func
*
* This allows you to execute a decorator routine from your template
*
* To load an html file from your current theme into your template
* <code>
* {decorator name="theme+side_bar_profile.html"}
* </code>
*
* To load a php file from your theme into your template
* <code>
* {decorator name="theme+side_bar_profile.php"}
* </code>
* The php file must provide a string called $response at the end of execution to render as output
*
* To load an html file from the directory of a plugin called mage
* <code>
* {decorator name="mage+side_bar_profile.html"}
* </code>
*
* @param  array     $params     array of parameters
* @param  object    $smarty     Smarty template object
*
* @return   the html response
*/
function theme_decorator_func($params, $smarty) {
      if(!isset($params["name"])) {return;};

      $e = explode('+',$params["name"]);
      if(count($e)<2) {return;};
      $plugin = $e[0];
      $file = $e[1];

      $path=_get_path($plugin).$file;


      $key='template_'.strip_file_ext($file);

      //assign parameters
      $smarty->assign($key,$params);

      $response='';
      if(get_file_ext($path)=='php') {
        //include php
        include $path;
      } else {
        //load html via smarty
        $response=$smarty->fetch($path);
      }

      return $response;
}
