<?php
namespace System\Core;

defined('BASEPATH') or exit('No direct script access allowed');

class Router extends \System\Base\Singleton
{

  /**
  * List of registered routes
  *
  * @var	array
  */
  public $routes;


    /**
    * The currently matched route name
    *
    * @var	string
    */
    public $_current='';

    /**
    * The current matched route parameters
    *
    * @var	string
    */
    public $_params=array();

    /**
    * name of route
    *
    * @var	string
    */
    public static $NAME='name';

    /**
    * uri of route
    *
    * @var	string
    */
    public static $URI='uri';

    /**
    * controller of route
    *
    * @var	string
    */
    public static $CTRL='controller';

    /**
    * controller of route
    *
    * @var	string
    */
    public static $CONTROLLER='controller';

    /**
    * method name of route
    *
    * @var	string
    */
    public static $METHOD='method';

    /**
    * addRoute
    *
    * Adds a new route
    *
    * @param  $route    A route object
    *
    * <code>
    * addRoute(new Route('core_create', 'module/create', 'Core_Module_Ctrl', 'module_create'));
    * </code>
    *
    * @return   object
    */
    public function addRoute($route)
    {
        $name=$route->controller.'::'.$route->view.'::'.$route->method;
        $route->name=$name;
        $this->routes[$name]=(array) $route;
        return $this;
    }

    /**
    * deleteRoute
    *
    * Deletes a route by name
    *
    * @param  String  $name    The name of the route
    *
    * <code>
    * deleteRoute('core_create');
    * </code>
    *
    * @return   object
    */
    public function deleteRoute($name)
    {
        if(isset($this->routes[$name])) {unset($this->routes[$name]);}
        return $this;
    }

    /**
    * findRoute
    *
    * @param    $search      The value of the parameter being searched for e.g. blog
    * @param    column       The key of the parameter being searched for e.g. name,uri,controller,method
    *
    * <code>
    * findRoute('blog/home',Router::$URI)
    * </code>
    *
    * @return   The name of the route if found (else null if nothing was found)
    */
    public function findRoute($search,$column)
    {
      $key = array_search($search, array_column($this->routes, $column));
      if($key==false) {return null;}
      return array_keys($this->routes)[$key];
    }

    /**
    * execute
    *
    * executes the selected route or show 404
    *
    * @return void
    */
    public function execute()
    {
      define('time_stop',microtime(true));
      $benchmark=time_stop-time_start;
      define('afro_benchmark',round($benchmark,2));


      //if system cannot match route, then run cli mode
      if((request_uri=='' && MODE=='cli') or ($this->_current==null && MODE=='cli')) {
        include BASEPATH."core/afrocana.php";
      }

      if($this->_current==null) {
        show_404();
        return;
      }


      $this->theme->assign('afro_benchmark',afro_benchmark);
      $this->theme->assign('afro_version',afro_version);



      $route=$this->routes[$this->_current];
      //stdout($route);
      extract($route);


      //load all controllers of the plugin being routed
      $files=browse($route['dir'].'/controllers',array('/is','/sd','/sd'),'*.php');
      foreach($files as $file) {
        include $file;
      }

      //if controller is *, then method must be a static function
      if($controller=='*' && function_exists($method)) {
        call_user_func($method);
        exit();
      } else if($controller=='*') {
        show_404();
      }

      $ref = new \ReflectionMethod($controller, $method);
      $ref->invokeArgs(new $controller(), $this->_params);

      $vpath="{$dir}/views/{$view}.html";

      $this->theme->render($vpath);
    }


    /**
    * match route with the current url request
    *
    * @return object
    */
    public function match()
    {
      $uri=request_uri;

      //echo $uri;
      //die();


      //find name of route if there is a direct match
      $this->_current=$this->findRoute($uri,Router::$URI);
      if($this->_current) {return $this;}

      $u=explode('/',$uri);

      foreach($this->routes as $route)
      {

        //direct match
        if($route['uri']==$uri) {
          $this->_current=$route['name'];
          break;
        }

        //search by pattern
        $_name=$route['name'];
        $_u=explode('/',$route['uri']);
        if(count($u)!=count($_u) || strpos($route['uri'],':')===false) {continue;}

        if($this->match_pattern($_u,$u)) {
          $this->_current=$_name;
          break;
        }
      }

      return $this;
    }


    /**
    * match_pattern
    *
    * checks if a route uri e.g. blog/post/:id matches a pattern e.g blog/post/1
    *
    * @param  string  $target   the route uri e.g.    blog/post/:id
    * @param  string  $request  the uri request e.g.  blog/post/1
    *
    * @return boolean
    */
    public function match_pattern($target,$request)
    {
      $this->_params=Array();

      foreach($target as $key=>$value) {
        $item=$request[$key];
        if($item==$value) {continue;}
        else if(substr($value,0,1)==':') {
          $this->_params[substr($value,1)]=$item;
          continue;
        } else {
          return false;
        }
      }

      return true;
    }


}
