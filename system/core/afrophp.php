<?php
/**
*  AFROPHP Legacy class
*/
define('NAME','AfroPHP Content Management Framework');
define('VERSION','1.0.0');

define('afro_version',VERSION);

/**
* Check to make sure minimum requirement is met
*/
if (version_compare(PHP_VERSION, '5.3.0') <= 0) {
  if (PHP_SAPI == 'cli') {
       echo 'AFROPHP supports PHP 5.3 and above.' .
           'Please read http://afrophp.com/user_guide/';
   } else {
       echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
   <p>AFROPHP supports PHP 5.3 and above. Please read
   <a target="_blank" href="http://afrophp.com/user_guide/">
   AFROPHP User Guide</a>.
</div>
HTML;
   }
   exit(1);
}




//defined('BASEPATH') OR exit('No direct script access allowed');
if(!isset($system_path)) {
  $system_path = 'system';
}

if(!isset($application_folder)) {
  $application_folder = 'application';
}



	if (($_temp = realpath($system_path)) !== FALSE)
	{
		$system_path = $_temp.DIRECTORY_SEPARATOR;
	}
  else 	if (($_temp = realpath('../'.$system_path)) !== FALSE) {
    $system_path = $_temp.DIRECTORY_SEPARATOR;
  }
	else
	{
		// Ensure there's a trailing slash
		$system_path = strtr(
			rtrim($system_path, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		).DIRECTORY_SEPARATOR;
	}




	// Is the system path correct?
	if ( ! is_dir($system_path))
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
		exit(3); // EXIT_CONFIG
	}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
	// The name of THIS file
	define('SELF', basename($_SERVER['PHP_SELF']));

  // Path to the loading directory
  if(php_sapi_name() == "cli") {
    define('LPATH',str_replace('\\','/',dirname(dirname(dirname(__FILE__)))).'/');
  } else {
    define('LPATH',str_replace('\\','/',dirname($_SERVER['SCRIPT_FILENAME'])).'/');
  }


  // Set the current directory correctly for CLI requests
	if (defined('STDIN'))
	{
		chdir(LPATH);
	}

	// Path to the system directory
	define('BASEPATH',str_replace('\\','/', $system_path));

	// Path to the front controller (this file) directory
	define('FCPATH', LPATH);


	define('APPPATH', FCPATH.strtr(
    trim($application_folder, '/\\'),
    '/\\',
    DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
  ).'/');


//load composer automatically in cli mode
if(php_sapi_name() == "cli") {
  require BASEPATH."vendor/autoload.php";
}


//define start time
define('time_start',microtime(true));



require BASEPATH."base/prototype.php";
require BASEPATH."core/common.php";


/**
* afrophp class
*
*/
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
        $this->config=new \System\Core\config();

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

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
          $ajax_submission=true;
        } else {
          $ajax_submission=false;
        }

        //detect if we are running in ajax mode or not
        define('ajax_submission',$ajax_submission);

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

        // e.g. cache/clear?v=10 (can contain query string)
        define('asset_url', base_url . trim(config_item('asset_path','assets'),'/').'/');

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
            //echo "Cache!";
            echo $result;
            exit();
          }
        }
    }


  /**
  * run
  *
  *
  * @return   void
  */
    public function run()
    {
      if(!defined('MODE')) {
        define('MODE', 'normal');
      }


        //instantiate loader
        $this->bootstrap();


        //cli debug
        if (MODE=='cli' && PHP_SAPI !== 'cli') {
            echo 'You must run '.$_SERVER['PHP_SELF'] .' as a CLI application';
            exit(1);
        }

        //attempts to render output from cache here
        $this->render_cache();

        //if no cache, continue loading mvc application
        $this->router= load_class(__DIR__."/router.php");
        $this->events= load_class(__DIR__."/events.php");




        //load plugins
        $this->load->plugins();

        //load theme engine

        $this->events->trigger('ready');

        $this->router->match();

        $this->events->trigger('match');

        $this->theme =  new theme();

        $this->theme->theme_init();

        $this->events->trigger('menu');

        $this->events->trigger('theme');

        if(MODE=='test') {return;}

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
      if(!defined('afro_benchmark')) {define('afro_benchmark',0);}

      $const=get_defined_constants(true);
      $data=array(
        'Benchmark'=>afro_benchmark . ' seconds',
        'Memory'=>bytes2string(memory_get_usage(true)),
        'Includes'=>get_included_files(),
        'Constants'=>$const['user'],
        'Objects'=>array_keys(get_object_vars($this)),
        'Config'=>config_item('*'),
        'Server'=>$_SERVER,
      );

      stdout($data);
    }

}


//run the application
Afrophp::instance()->run();
