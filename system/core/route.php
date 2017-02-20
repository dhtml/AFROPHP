<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Route
{


  /**
  * creates a new route and registers it
  *
  * Accepts an arbitrary number of parameters (up to 6) or an associative
  * array in the first parameter containing all the values.
  *
  * @param	mixed	    $view		 name of the view or an array containing parameters
  * @param  string    $uri              The uri of the route
  * @param  string    $controller       The controller of the route
  * @param  string    $method           The method of the controller
  * @param  string    $template         (optional) The directory of the plugin (optional)
  * @param  boolean   $cache            (optional) Should the out be cached if cache is enabled?
  *
  * Examples:
  *
  * $this->router->addRoute(new Route('admin.default', 'admin', 'CMS', 'admin'));
  *
  *  $this->router->addRoute(new Route(array(
  *        'view'=>'admin.pages',
  *        'uri'=>'admin/pages',
  *        'controller'=>'CMS',
  *        'method'=>'pages'
  *      )));
  *
  *
  * @return void
  */
  public function __construct($view,$uri='',$controller='',$method='',$template='default',$cache=true)
  {

    if (is_array($view))
    {
      // always leave 'view' in last place, as the loop will break otherwise, due to $$item
      foreach (array('uri', 'controller', 'method', 'template', 'cache', 'view') as $item)
      {
        if (isset($view[$item]))
        {
          $$item = $view[$item];
        }
      }
    }


    //evaluate the directory of the caller
    $key = array_search(__FUNCTION__, array_column(debug_backtrace(), 'function'));
    $file=debug_backtrace()[$key]['file']; //the file that called the function
    $dir=pathinfo($file,PATHINFO_DIRNAME);

    $uri=trim($uri,'/');

    $admin_path=config_item(trim('admin_path','/'),'admin');

    if($uri=='admin') {$uri=$admin_path;}
    else if(substr($uri,0,6)=='admin/') {$uri=$admin_path.substr($uri,5);}

    //stdout($admin_path);

    $this->view=$view;
    $this->uri= $uri;
    $this->controller=$controller;
    $this->method=$method;
    $this->dir=$dir;
    $this->template=$template;
    $this->cache=$cache;
  }


}
