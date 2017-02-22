<?php
/**
*  AFROPHP Legacy class
*/
defined('BASEPATH') or exit('No direct script access allowed');

define('afro_version','1.0.0');

class Afrophp  extends \System\Base\Singleton
{

    /**
    * bootstrap
    *
    * Loads the bootstrap functionalities of AFROPHP
    *
    * @return void
    */
    public function bootstrap()
    {
      global $argc, $argv;

        //prepare loader
        $this->load = $this->loader = \System\Core\loader::instance();

        //load config
        include __DIR__."/config.php";
        $this->config=new Config();


        //configure some php settings
        ini_set('display_errors', config_item('display_errors', 0));

        error_reporting(config_item('error_reporting', E_ALL,true));
        date_default_timezone_set(config_item('default_timezone', 'UTC'));


        //get environment variables
        if (PHP_SAPI !== 'cli') {
          //html mode
            define('cli', false);
          //normal non-cli mode


            $protocol = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
            $host=$_SERVER['HTTP_HOST'];
            $base_url = $protocol."://".$host;
            $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);

            $base_root=str_replace('/index.php', '/', $_SERVER['PHP_SELF']);

            //stdout($base_root,true);

            //if (strlen($base_root)>1) {


                if (config_item('enable_query_strings',false,true)) {
                    //query string mode enabled
                    $q= config_item('controller_trigger','q');
                    if(substr($_SERVER['QUERY_STRING'], 0, 2)== $q.'=') {
                      $request_uri=substr($_SERVER['QUERY_STRING'], 2);
                      $request_uri=ltrim($request_uri, '/');
                    } else {
                      $request_uri="";
                    }
                } else {
                    //pretty url mode
                    $uri_protocol=config_item('uri_protocol', 'REQUEST_URI');
                    $request_uri= str_replace($base_root, '', $_SERVER[$uri_protocol]);
                }
        } else {
            //cli mode
            define('cli', true);
            unset($argv[0]);
            $base_url="http://localhost";
            $protocol="http";
            $host="localhost";
            $base_root="/";
            $request_uri= implode('/', $argv);
        }

        $current_url=$base_url.$request_uri;

        //the url of the front controller e.g. http://localhost/afrophp.com/
        define('base_url', $base_url);

        //e.g. http or https
        define('protocol', $protocol);

        //e.g. localhost, afrophp.com
        define('host', $host);

        //e.g. /africoders.net/ or /
        define('base_root', $base_root);

        // e.g. cache/clear (cannot contain ?)
        define('request_uri', strip_query_string($request_uri));

        // e.g. cache/clear?v=10 (can contain query string)
        define('request_url', $request_uri);

        //the full url e.g. http://localhost/afrophp.com/cache/clear?v=1
        define('current_url', $current_url);
    }




    /**
    * Renders output from cache when possible
    *
    * It does not work in cli mode
    *
    * @return void
    */
    private function render_cache()
    {
        $this->cache = new  \System\Core\cache();

        $key=request_uri;

        $key= empty($key) ? '/' : $key;
        //$key= request_uri =='' ? '/' : request_uri;


        if(!is_cli()) {
          if($this->cache->has($key)) {
            $result=$this->cache->get($key,true);
            echo "Cache!";
            echo $result;
            exit();
          }
        }
    }


  /**
  * run
  *
  * @param  string  $mode   The running mode, cli or normal (default)
  *
  * @return   void
  */
    public function run($mode='normal')
    {
        define('MODE', strtolower($mode));


        //instantiate loader
        $this->bootstrap();


        //cli debug
        if (MODE=='cli' && PHP_SAPI !== 'cli') {
            echo 'You must run '.$_SERVER['PHP_SELF'] .' as a CLI application';
            exit(1);
        }

        //load bootstrap files
        include __DIR__."/cache.php";

        //attempts to render output from cache here
        $this->render_cache();

        //load mvc structrue
        include __DIR__."/model.php";
        include __DIR__."/my_model.php";
        include __DIR__."/controller.php";

        include __DIR__."/security.php";
        include __DIR__."/input.php";
        include __DIR__."/route.php";
        include __DIR__."/router.php";
        include __DIR__."/events.php";
        include __DIR__."/menu.php";
        include __DIR__."/navigation.php";
        include __DIR__."/lang.php";
        include __DIR__."/theme.php";

        if(MODE=='cli') {
          include __DIR__."/console.php";
        }

        //initiallize system wide objects
        $this->navigation =  \System\Core\navigation::instance();

        $this->router =  \System\Core\router::instance();

        $this->security =  \System\Core\security::instance();

        $this->input =  new \System\Core\Input();

        $this->events =  \System\Core\events::instance();

        $this->lang =  \System\Core\lang::instance();

        $this->theme =  new theme();

        $this->load->plugins();

        $this->events->trigger('ready');

        $this->router->match();

        $this->events->trigger('match');

        $this->theme->theme_init();

        $this->events->trigger('menu');

        $this->events->trigger('theme');

        $this->router->execute();

        $this->events->trigger('execute');

        if(config_item('profiling',false,true)) {
          $this->profiling();
        }
    }

    /**
    * If profile is enabled in the configuration (base.xml)
    * The profile of the application will appear at the bottom of the page
    *
    * @return void
    */
    public function profiling()
    {
      $data=array(
        'Benchmark Time'=>afro_benchmark . ' seconds',
        'Memory usage'=>bytes2string(memory_get_usage(true)),
        'Files'=>get_included_files(),
        'Constants'=>get_defined_constants(true)['user'],
        'Current Objects'=>array_keys(get_object_vars($this)),
        'Config Values'=>config_item('*'),
      );

      stdout($data);
    }

}
