<?php
/**
*  AFROPHP Legacy class
*/
defined('BASEPATH') or exit('No direct script access allowed');

define('NAME','AfroPHP Content Management Framework');
define('VERSION','1.0.0');

define('afro_version',VERSION);

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
        $this->load = $this->loader = new \System\Core\loader();

        //load config
        include __DIR__."/config.php";
        $this->config=new Config();

        //define environment
        define('env_init',APPPATH.'config/console/default/env.init.php');
        define('env_data',APPPATH.'config/console/default/env.php');

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


            define('REWRITE_BASE',$base_root);

            //$rewrite_base = rewrite_slash(str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
            //$r=explode('index.php',$rewrite_base);
            //$rewrite_base=$r[0];


            //define('REWRITE_BASE','/'.trim($rewrite_base,'/').'/');


            //create htaccess if it does not exist
            if(!file_exists(FCPATH.'.htaccess') && config_item('autohtaccess',true)) {
              create_htaccess(REWRITE_BASE);
            }

            //stdout(REWRITE_BASE,true);

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
            $args=$argv;

            unset($args[0]);
            $base_url="http://localhost";
            $protocol="http";
            $host="localhost";
            $base_root="/";
            $request_uri= implode('/', $args);
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

        if(!cli && file_exists(env_init)) {
          $env=array(
            'base_url'=>base_url,
            'request_uri'=>request_uri,
            'request_url'=>request_url,
            'current_url'=>current_url,
            'rewrite_base'=>REWRITE_BASE,
          );
          array_put_contents(env_data,$env);
          unlink(env_init);
        }
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
        include BASEPATH."3rdparty/vendor/autoload.php";
        include __DIR__."/model.php";
        include __DIR__."/controller.php";
        include __DIR__."/theme.php";

        $this->router= load_class(__DIR__."/router.php");
        $this->events= load_class(__DIR__."/events.php");

        //load theme engine
        $this->theme =  new theme();

        //load plugins
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
        'Benchmark'=>afro_benchmark . ' seconds',
        'Memory'=>bytes2string(memory_get_usage(true)),
        'Includes'=>get_included_files(),
        'Constants'=>get_defined_constants(true)['user'],
        'Objects'=>array_keys(get_object_vars($this)),
        'Config'=>config_item('*'),
        'Server'=>$_SERVER,
      );

      stdout($data);
    }

}
