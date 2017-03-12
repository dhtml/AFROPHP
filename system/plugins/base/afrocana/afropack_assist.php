<?php
namespace Console;

defined('BASEPATH') or exit('No direct script access allowed');

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;

use Symfony\Component\Console\Helper\Table;

class afropack {

  public $conn_id=false;

  public $ftp_list_in =APPPATH."config/console/ftp/in.php";
  public $ftp_list_out = APPPATH."config/console/ftp/out.php";
  public $ftp_list_conf = APPPATH."config/console/ftp/conf.php";

  public $ftp_config= array();

  public $ftp_out=array();
  public $ftp_in=array();

  public $ftp_default_config = array(
    'host'=>'localhost',
    'user'=>'user',
    'pass'=>'test',
    'port'=>'21',
    'dir'=>'/public_html',
    'local_dir'=>'/',
    'timeout'=>'90',
  );


  public function __construct($command=null,$input=null, $output=null)
  {
    if($command!=null) {
      $this->command=$command;
      $this->input=$input;
      $this->output=$output;

      //load config if it actually exists
      $this->ftp_config=array_get_contents($this->ftp_list_conf);

      get_instance()->load->helper('inflect');



      //if there is no ftp config
      if(empty($this->ftp_config)) {
        $this->ftp_config=$this->ftp_default_config;
      }
    }
  }


  function __destruct() {
      if($this->conn_id) {
        ftp_close($this->conn_id);
        $this->output->writeln("<info>Ftp connection closed</info>");
      }
  }

  public function __get($key)
  {
    if(isset($this->command->$key)) {
      return $this->command->$key;
    } else {
      show_error("There is no property called: {$key}()");
    }
  }


  /**
  * __call
  *
  * reroute method calls to the framework
  *
  * @return mixed
  */
  public function __call($name, $arguments)
  {
    if(method_exists($this->command,$name)) {$result=call_user_func_array(array($this->command, $name),$arguments);}
    else {
      show_error("There is no method called: {$name}()");
    }

    return $result;
  }


  /**
  * executes a command in the shell and exits
  *
  * <code>
  * php afrocana exec ls
  * </code>
  *
  * @return void
  */
  public function shell_exec()
  {
    chdir(FCPATH);
    $args = implode(' ',$this->input->getArgument('params'));
    system("$args");

    chdir(FCPATH.'bin');
  }



/**
* forget item from cache
*
* @param string $name The key of the item
*
* @return void
*/
  public function cache_forget($name)
  {
    $cache=get_instance()->cache;
    if($cache->forget($name)) {
      $this->output->writeln("<info>$name: forgotten by cache</info>");
    } else {
      $this->output->writeln("<debug>$name: not found in cache</debug>");
    }
  }

  /**
  * forget all items from cache
  *
  */
    public function cache_clear()
    {
      $cache=get_instance()->cache;
      if($cache->flush()) {
        $this->output->writeln("<info>Application cache flushed</info>");
      } else {
        $this->output->writeln("<debug>Application cache empty</debug>");
      }
    }

public function logs_show()
{
$logfile=APPPATH."logs/errors.log";
if(!file_exists($logfile)) {file_force_contents($logfile,"");}

$data=file_get_contents($logfile);
if(strlen($data)<2) {
    $this->output->writeln("<info>Application log is empty</info>");
} else {
    $this->output->writeln($data);
}

}

public function logs_clear()
{
$logfile=APPPATH."logs/errors.log";
file_force_contents($logfile,"");
$this->output->writeln("<info>Application logs flushed</info>");
}


    /**
    * initializes environment
    */
    public function env_init()
    {
      file_force_contents(env_init,'');
      if(file_exists(env_data)) {unlink(env_data);}
      $this->output->writeln("Please open the url of this app in your browser");
      while(!file_exists(env_data)) {
        sleep(2);
      }
      $this->output->writeln("Initialization complete");
    }

    /**
    * create htaccess file
    */
    public function env_hta()
    {
      if(file_exists(FCPATH.'.htaccess')) {
        $this->output->writeln("<debug>Please delete ".FCPATH.'.htaccess for this to work</debug>');
        return;
      }

      if(!file_exists(env_data)) {$this->env_init();}
      $env=array_get_contents(env_data);

      $bool=create_htaccess($env['rewrite_base']);

      if($bool) {
        $this->output->writeln("<info>Created ".FCPATH.'.htaccess successfully</info>');
      } else {
        $this->output->writeln("<debug>Unable to created ".FCPATH.'.htaccess</debug>');
      }
    }

    /**
    * allows running of composer commands for afrophp
    */
    public function composer()
    {
      chdir(BASEPATH.'3rdparty');
      $args = implode(' ',$this->input->getArgument('params'));
      system("composer $args");
      chdir(FCPATH.'bin');
    }

    /**
    * Send a test email
    *
    */
    public function test_email($to) {
      $that=get_instance();
      $that->load->library('email');

      $that->email->from('test@afrophp.com');
      $that->email->to($to);

      $that->email->subject('Email Test');
      $that->email->message('Testing the email class.');

      $this->output->write("<info>Attempting to send test mail to $to...</info>");

      if(!$that->email->send()) {
        $error=$that->email->ErrorInfo;
        $this->output->writeln("<debug>$error</debug>");
      } else {
        $this->output->writeln("<info>success</info>");
      }
    }

  /**
  * list commands
  *
  */
  public function list_commands()
  {
    global $console_directives;

    $commands=$console_directives;

    $commands=short_path($commands);

    $this->command->io->listing($commands);

    //$this->output->writeln("List commands");
  }

public function list_routes()
{
  $tag='info';

  $routes=get_instance()->router->routes;

  $tab=Array();
  foreach($routes as $route)
  {
    extract($route);
    if($uri=='') {$uri='/';}
    $tab[]=$this->tag_wrap(array($uri,$controller,$method,$view,$template,$cache),$tag);
  }

   $this->command->table($this->tag_wrap(array('Uri','Controller','Method','View','Template','Cache'),$tag),$tab);
}

/**
* show routing information for uri
*
*/
public function route_show($uri)
{
  $routes=get_instance()->router->routes;
  $found=false;
  if($uri=='/') {$uri='';}
  foreach($routes as $route) {
    if($route['uri']==$uri) {$found=true;break;}
  }

  if(!$found) {
    $this->output->writeln("<debug>$uri: no route found for this uri</debug>");
    return;
  }



  $tab=array();


  $tag= 'info';

  foreach($route as $key=>$value)
  {
    if($key=='dir') {
      $value=short_path($value);
    } else if($key=='uri' && $value=='') {
      $value='/';
    }

    $tab[]=$this->tag_wrap(array($key,$value),$tag);
  }


  $this->command->table($this->tag_wrap(array('Item','Value'),$tag),$tab);
}


/**
* Make controller
*
*/
  public function make_controller($name,$controller)
  {
    if(!$plugin=$this->find_plugin_by_name($name)) {return;}

    $file=$plugin['path'].'/controllers/'.$controller.".php";

    if(file_exists($file)) {
      $this->output->writeln("<debug>Controller `$controller` already exist in plugin `$name`</debug>");
      return;
    }

    $output='<?php
defined(\'BASEPATH\') or exit(\'No direct script access allowed\');

class '.$controller.' extends Controller {
    public function index()
    {

    }
}';

    $path=$plugin['path'];

    file_force_contents($file,$output);

    $this->output->writeln("<info>Controller `$controller` created in plugin `$name`</info>");
  }


  /**
  * Make model
  *
  */
    public function make_model($name,$model)
    {
      if(!$plugin=$this->find_plugin_by_name($name)) {return;}

      $file=$plugin['path'].'/models/'.$model.".php";

      if(file_exists($file)) {
        $this->output->writeln("<debug>Model `$model` already exist in plugin `$name`</debug>");
        return;
      }

      $output='<?php
  defined(\'BASEPATH\') or exit(\'No direct script access allowed\');

  class '.$model.' extends Model {
      public function __construct()
      {
        parent::__construct();
      }
  }';

      $path=$plugin['path'];

      file_force_contents($file,$output);

      $this->output->writeln("<info>Model `$model` created in plugin `$name`</info>");
}



  /**
  * Make view
  *
  */
    public function make_view($name,$param)
    {
      if(!$plugin=$this->find_plugin_by_name($name)) {return;}

      $file=$plugin['path'].'/views/'.$param;

      if(file_exists($file)) {
        $this->output->writeln("<debug>View `$param` already exist in plugin `$name`</debug>");
        return;
      }

      $output='';

      $path=$plugin['path'];

      file_force_contents($file,$output);

      $this->output->writeln("<info>View `$param` created in plugin `$name`</info>");
}


/**
* Make bool
*
*/
  public function make_bool($name,$param)
  {
    if(!$plugin=$this->find_plugin_by_name($name)) {return;}

    $file=$plugin['path'].'/bool/'.$param;

    if(file_exists($file)) {
      $this->output->writeln("<debug>Bool `$param` already exist in plugin `$name`</debug>");
      return;
    }

    $output='<?php
defined(\'BASEPATH\') or exit(\'No direct script access allowed\');

';

    $path=$plugin['path'];

    file_force_contents($file,$output);

    $this->output->writeln("<info>Bool `$param` created in plugin `$name`</info>");
}


/**
* Make language
*
* <code>
* php afrocana make:language helloworld en spades
* </code>
*
* @param string $plugin the name of plugin e.g. helloworld
* @param string $code the code of language e.g. en
* @param string $basename the name of file e.g. spades
*
*
* @return void
*/
  public function make_language($plugin,$code,$basename)
  {
    if(!$plug=$this->find_plugin_by_name($plugin)) {return;}

    $file=$plug['path'].'/languages/'.$code.'/'.$basename.".xml";

    $filename=short_path($file);

    if(file_exists($file)) {
      $this->output->writeln("<debug>`$filename` already exist</debug>");
      return;
    }

    $output='<?xml version="1.0"?>
    <resources>
      <key name="greet">
         <value>Hello</value>
      </key>
    </resources>
';

    file_force_contents($file,$output);

    $this->output->writeln("<info>Created `$filename`</info>");
}

/**
* Make library
*
*/
  public function make_library($name,$param)
  {
    if(!$plugin=$this->find_plugin_by_name($name)) {return;}

    $file=$plugin['path'].'/libraries/'.$param.".php";

    if(file_exists($file)) {
      $this->output->writeln("<debug>Library `$param` already exist in plugin `$name`</debug>");
      return;
    }

    $output='<?php
defined(\'BASEPATH\') or exit(\'No direct script access allowed\');

class '.$param.' {


}';

    $path=$plugin['path'];

    file_force_contents($file,$output);

    $this->output->writeln("<info>Library `$param` created in plugin `$name`</info>");
}



  /**
  * Make command
  *
  */
  public function make_command($name,$param)
  {
    if(!$plugin=$this->find_plugin_by_name($name)) {return;}

    $file=$plugin['path'].'/afrocana/'.$param.".php";

    if(file_exists($file)) {
      $this->output->writeln("<debug>Command `$param` already exist in plugin `$name`</debug>");
      return;
    }

    $output='<?php
namespace Console;

defined(\'BASEPATH\') or exit(\'No direct script access allowed\');

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

';

    $path=$plugin['path'];

    file_force_contents($file,$output);

    $this->output->writeln("<info>Command `$param` created in plugin `$name`</info>");
}

  /**
  * Select a plugin
  *
  */
  public function plugin_selector()
  {
    global $keys;
    $plugins=get_instance()->loader->get_plugins();

    $plugins=array_merge($plugins['enabled'],$plugins['disabled']);


    $tab=array();

    $keys=array();

    $_temp=array();

    $autoc=array();

    foreach($plugins as $plugin)
    {
      if(!isset($plugin['name'])) {$continue;}

      $tag= (isset($plugin['enable']) && ($plugin['enable']==1)) ? 'info' : 'debug';

      $name=$plugin['name'];
      $key=$plugin['key'];

      $autoc[]=$key;

      $_key=(int) $key;
      $_temp[$_key]=$plugin;

      $keys[]=$key;
      $version= isset($plugin['version']) ? $plugin['version'] : 1;
      $enable= (isset($plugin['enable']) && ($plugin['enable']==1)) ? 'Enabled' : 'Disabled';
      $path=short_path($plugin['path']);

      $tab[]=$this->tag_wrap(array($key,$name,$path),$tag);
    }

    $this->command->table(array('S/n','Name','Path'),$tab);

    $helper = $this->command->getHelper('question');


    $question = new Question("<info>Please enter the s/n of target plugin?</info> ", '');
    $question->setAutocompleterValues($autoc);
    $question->setValidator(function ($answer) {
      global $keys;
       if (!in_array($answer,$keys)) {
           throw new \RuntimeException(
               'The plugin serial you entered is not valid'
           );
       }
       return $answer;
   });
   $question->setMaxAttempts(4);


    $serial = (int) $helper->ask($this->input, $this->output, $question);

    $plugin= isset($_temp[$serial]) ? $_temp[$serial] : false;
    if(!$plugin) {return false;}

    return $plugin;
  }

  /**
  * list menu items
  *
  */
  public function list_menus()
  {
    $menus=get_instance()->navigation->compile();

    $tab=array();


    $tag= 'info';

    foreach($menus as $menu)
    {
      $key=$menu['key'];
      $title=$menu['title'];
      $uri=$menu['uri']=='' ? '/' : $menu['uri'];
      $priority=$menu['priority'];

      $tab[]=$this->tag_wrap(array($uri,$title,$key,$priority),$tag);
    }

    $this->command->table($this->tag_wrap(array('Uri','Title','Key','Priority'),$tag),$tab);
  }


  /**
  * show menu items
  *
  */
  public function menu_show($uri)
  {
    $menus=get_instance()->navigation->compile();

    $_uri= $uri=='/' ? '' : $_uri;

    $found=false;

    foreach($menus as $menu)
    {
      if($menu['uri']==$_uri) {
        $found=true;
        stdout($menu);
      }
    }

    if(!$found) {
      $this->output->writeln("<info>$uri: no menu found matching this URI</info>");
    }
  }

/**
* list all available themes
*/
public function list_themes()
{

$themes=get_instance()->theme->all();

  $pos=0;
  foreach($themes as $theme) {
    $tag = $theme['status']=='disabled' ? 'debug' : 'info';

    $tab[]=$this->tag_wrap(array($theme['key'],$theme['name'],$theme['version'],$theme['status'],short_path($theme['path'])),$tag);
  }

  $this->command->table(array('S/n','Name','Version','Status','Path'),$tab);



}

  /**
  * list commands
  *
  */
  public function plugin_list()
  {
    $plugins=get_instance()->loader->get_plugins();


    $plugins=array_merge($plugins['enabled'],$plugins['disabled']);


    $tab=array();

    foreach($plugins as $plugin)
    {
      if(!isset($plugin['name'])) {$continue;}

      $tag= (isset($plugin['enable']) && ($plugin['enable']==1)) ? 'info' : 'debug';

      $name=$plugin['name'];
      $key=$plugin['key'];
      $version= isset($plugin['version']) ? $plugin['version'] : 1;
      $enable= (isset($plugin['enable']) && ($plugin['enable']==1)) ? 'Enabled' : 'Disabled';
      $path=short_path($plugin['path']);

      $tab[]=$this->tag_wrap(array($key,$name,$version,$enable,$path),$tag);
    }

    $this->command->table(array('S/n','Name','Version','Status','Path'),$tab);
  }

//show info of plugin
 public function plugin_show($name)
 {
   if(!$plugin=$this->find_plugin_by_name($name)) {return;}

    $this->_dump_plugin($plugin,true);
 }

 //show info of theme
  public function theme_show($name)
  {
    if(!$theme=$this->find_theme_by_name($name)) {return;}

    $this->_dump_theme($theme,true);
  }


    /**
    * Make template
    *
    */
    public function make_template($name,$template)
    {
      if(!$theme=$this->find_theme_by_name($name)) {return;}

      $path=$theme['path'].'templates/'.$template.'/';

      $_path=short_path($path);

      if(file_exists($path)) {
        $this->output->writeln("<debug>`$_path` already</debug>");
        return;
      }

      file_force_contents($path."template.html",'{$page_content}');
      file_force_contents($path."template.info","template_name = $template
template_description = The is the $template template of $name
");

      $this->output->writeln("<info>Template `$template` created for theme `$name`</info>");
  }



  /**
  * Make theme
  *
  */
  public function make_theme($name)
  {
    $path=APPPATH."themes/".$name.'/';


    $_path=short_path($path);


    if(file_exists($path)) {
      $this->output->writeln("<debug>`$_path` already</debug>");
      return;
    }

    file_force_contents($path."page.html",'{$page_content}');
    file_force_contents($path."theme.info","name = $name
description = $name is an afrophp theme
");

file_force_contents($path."master.html",'<!doctype html>
<html lang="{$page_lang}" dir="{$page_direction}">
<head>
  <title>{$page_title}</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="shortcut icon" href="{$theme_url}favicon.ico" type="image/vnd.microsoft.icon" />
  {$headData}
</head>
<body class="{$bodyClass}">
  {$pageBody}
  {$footData}
</body>
</html>');

    $this->output->writeln("<info>Theme `$name` created at `$_path`</info>");
}

 //show info of plugin
  public function make_plugin($name)
  {
    $path=APPPATH . "plugins/$name/";
    if(file_exists($path)) {
      $this->output->writeln("$name: plugin already exists!");
      return;
    }

    $config=array(
    'name'=>$name,
    'version'=>(int) VERSION,
    'build'=>1,
    'enable'=>0,
    );

    $root='<?php
defined(\'BASEPATH\') or exit(\'No direct script access allowed\');

';

  xml_put_contents($path.'plugin.xml',$config);

  file_force_contents($path.'plugin.php',$root);

  $this->output->writeln("<info>$name: plugin created</info>");
}



 /**
 * Enables a plugin
 *
 */
 public function plugin_enable($name)
 {
   if(!$plugin=$this->find_plugin_by_name($name)) {return;}

   extract($plugin);
   $config_file=$path."/plugin.xml";

   $config=xml_get_contents($config_file);
   $config['enable']=1;
   xml_put_contents($config_file,$config,'plugin');
   $this->output->writeln("<info>$name: enabled</info>");
 }


  /**
  * Disable a plugin
  *
  */
  public function plugin_disable($name)
  {
    if(!$plugin=$this->find_plugin_by_name($name)) {return;}

    extract($plugin);
    $config_file=$path."/plugin.xml";

    $config=xml_get_contents($config_file);
    $config['enable']=0;
    xml_put_contents($config_file,$config,'plugin');
    $this->output->writeln("<info>$name: disabled</info>");
  }

  /**
  * Dumps a theme
  *
  * @param array $theme The configuration of the theme
  * @param boolean $show_files Should files be shown or not?
  *
  * @return void
  */
public function _dump_theme($theme,$show_files=false)
{
return $this->_dump_plugin($theme,$show_files);
}

/**
* Dumps a plugin
*
* @param array $plugin The configuration of the plugin
* @param boolean $show_files Should files be shown or not?
*
* @return void
*/
public function _dump_plugin($plugin,$show_files=false)
{
  if(empty($plugin) || !is_array($plugin)) {return;}
  unset($plugin['key']);

  $tab=array();


  $tag= (isset($plugin['enable']) && ($plugin['enable']==1)) ? 'info' : 'c';

  foreach($plugin as $key=>$value)
  {
    if($key=='path') {
      $value=short_path($value);
    } else {
    }
    $tab[]=$this->tag_wrap(array($key,$value),$tag);
  }

if($show_files) {
  $files=browse($plugin['path'],array('/is','/sd','/ss'),'*');
  $files=short_path($files,$plugin['path'].'/');

  sort($files);

  $_files=array();
  $_folders=array();

  foreach($files as $file)
  {
    if(strpos($file,'/')===false) {$_files[]=$file;} else {$_folders[]=$file;}
  }
  $files=array_merge($_files,$_folders);


  $files=implode("\n",$files);

  $files=short_path($files);

  $tab[]=$this->tag_wrap(array('*files',$files),$tag);
 }


  $this->command->table($this->tag_wrap(array('Item','Value'),$tag),$tab);
}

/**
* selects a particular plugin by name
*/
public function find_plugin_by_name($name)
{
  $helper = $this->command->getHelper('question');

  $plugins=get_instance()->loader->find_plugin($name,true);

  if(empty($plugins)) {
    $this->output->writeln("$name: plugin not found");
    return false;
  }

  if(count($plugins)==1) {
    $plugin=$plugins[0];
  } else {
    //select a particular plugin
    $options=array();
    $pos=0;
    foreach($plugins as $plugin) {
      $options[$pos]=short_path($plugin['path'],FCPATH);
      $pos++;
    }

    $question = new ChoiceQuestion("\nConflict: select specific location",$options,0);
    $question->setErrorMessage('Option %s is invalid.');
    $opt = $helper->ask($this->input, $this->output, $question);
    $response=array_search($opt, $options);

    $plugin=$plugins[$response];
  }

  return $plugin;
}

/**
* selects a particular theme by name
*/
public function find_theme_by_name($name)
{
  $helper = $this->command->getHelper('question');

  $themes= get_instance()->theme->find_by_name($name);

  if(empty($themes)) {
    $this->output->writeln("$name: theme not found");
    return false;
  }

  if(count($themes)==1) {
    $theme=$themes[0];
  } else {
    //select a particular theme
    $options=array();
    $pos=0;
    foreach($themes as $theme) {
      $options[$pos]=rtrim(short_path($theme['path'],FCPATH),'/');
      $pos++;
    }

    $question = new ChoiceQuestion("\nConflict: select specific location",$options,0);
    $question->setErrorMessage('Option %s is invalid.');
    $opt = $helper->ask($this->input, $this->output, $question);
    $response=array_search($opt, $options);

    $theme=$themes[$response];
  }

  return $theme;
}


 //show info of plugin
  public function plugin_config($name)
  {
    $helper = $this->command->getHelper('question');

    $plugin=$this->find_plugin_by_name($name);

    if(empty($plugin)) {
      $this->output->writeln("<debug>$name: plugin not found by name</debug>");
      return;
    }


    $tab=array();

    $tag= (isset($plugin['enable']) && ($plugin['enable']==1)) ? 'info' : 'debug';

    foreach($plugin as $key=>$value)
    {
      if($key=='path') {$value=short_path($value);}
      $tab[]=$this->tag_wrap(array($key,$value),$tag);
    }

    $this->command->table(array('Item','Value'),$tab);


    $question = new ConfirmationQuestion(
        'Continue configuring this plugin? (default true) ',
        true,
        '/^(y|j)/i'
    );

    if (!$helper->ask($this->input, $this->output, $question)) {
            return;
    }

    $this->configure_selected_plugin($plugin);
  }

  public function configure_selected_plugin($plugin)
  {
    $helper = $this->command->getHelper('question');
    $output=$this->output;
    $input=$this->input;

    extract($plugin);

         $options=array(
         '0'=>'Close menu',
         '1'=>'Enable plugin',
         '2'=>'Disable plugin',
         '3'=>'Create plugin model',
         '4'=>'Create regular view',
         '5'=>'Create smarty view',
         '6'=>'Create controller',
         '7'=>'Create cli command',
       );


       $config_file=$path."/plugin.xml";

         $question = new ChoiceQuestion("\nConfigure plugin $name",$options,0);
         $question->setErrorMessage('Option %s is invalid.');
         $opt = $helper->ask($this->input, $this->output, $question);

         $response=array_search($opt, $options);


         switch($response) {
         case "0":
         return;
         break;
         case "1":
         $config=xml_get_contents($config_file);
         $config['enable']=1;
         $this->output->writeln("<info>$name: enabled</info>");
         xml_put_contents($config_file,$config,'plugin');
         break;
         case "2":
         $config=xml_get_contents($config_file);
         $config['enable']=0;
         $this->output->writeln("<info>$name: disabled</info>");
         xml_put_contents($config_file,$config,'plugin');
         break;
         case "3":
         $this->output->write('<info>Wiping connection data...</info>');
         //array_put_contents($this->ftp_list_conf);
         //$this->output->writeln('<info>done</info>');
         break;
         }


  }

  /**
  * wraps elements of an array with a tag
  *
  * @param array $data an array of values
  * @param string $tag a tag
  *
  * @return array
  */
  public function tag_wrap($data,$tag)
  {
  $result=array();
  foreach($data as $item)
  {
    $result[]='<'.$tag.'>'.$item.'</'.$tag.'>';
  }
  return $result;
  }




     /**
     * A good example is shown below:
     * ftp chmod application/cache 0755
     *
     */
     public function ftp_chmod($file='index.php',$mode='0755')
     {
       $this->getFtpConnection();
       if(!$this->conn_id) {return;}

     $target= $this->remote_dir().$file;


     $this->output->write("<info>Changing $target to $mode...</info>");



     if (@ftp_chmod($this->conn_id, $mode, $file) !== false) {
      $this->output->writeln("<info>done</info>");
     } else {
       $this->output->writeln("<info>failed</info>");
     }


     }


     /**
     * initialize ftp client
     *
     */
     public function ftp_init()
     {
         $helper = $this->getHelper('question');


         $this->output->writeln("<info>initializing ftp client</info>");



         $question = new Question("Please enter your ftp host? ({$this->ftp_config['host']}) ", $this->ftp_config['host']);
         $this->ftp_config['host'] = $helper->ask($this->input, $this->output, $question);

         $question = new Question("Please enter your ftp username? ({$this->ftp_config['user']}) ", $this->ftp_config['user']);
         $this->ftp_config['user'] = $helper->ask($this->input, $this->output, $question);


         $question = new Question("Please enter your ftp password? (hidden) ", $this->ftp_config['pass']);
         $question->setHidden(true);


         $this->ftp_config['pass'] = $helper->ask($this->input, $this->output, $question);


         //$this=>getFtpConnection(true);

         $question = new Question("Please enter your ftp port? ({$this->ftp_config['port']}) ", $this->ftp_config['port']);
         $this->ftp_config['port'] = $helper->ask($this->input, $this->output, $question);

         $question = new Question("Please enter your ftp timeout? ({$this->ftp_config['timeout']}) ", $this->ftp_config['timeout']);
         $this->ftp_config['timeout'] = $helper->ask($this->input, $this->output, $question);


         $question = new Question("Please enter your remote path? ({$this->ftp_config['dir']}) ", $this->ftp_config['dir']);
         $this->ftp_config['dir'] = $helper->ask($this->input, $this->output, $question);


         $question = new Question("Please enter your local path? ({$this->ftp_config['local_dir']}) ", $this->ftp_config['local_dir']);
         $this->ftp_config['local_dir'] = $helper->ask($this->input, $this->output, $question);

         array_put_contents($this->ftp_list_conf,$this->ftp_config);


         $this->output->writeln("<info>ftp initialization completed</info>");
     }

     /**
     * initialize ftp client
     *
     */
     public function ftp_test()
     {
        $this->output->writeln("<info>Attempting ftp connection</info>");

        if($this->getFtpConnection(true)) {
          $this->output->writeln("<info>ftp connection was successful</info>");
        }
     }


     /**
     * Commit ftp client
     *
     * @param boolean $synced  Should the local and remote cache be synched
     *
     */
     public function ftp_commit($synced=false)
     {
       $base=browse($this->local_dir(),array('/sd','/ss'));

       $files=browse($this->local_dir(),array('/is','/sd','/ss'));

       $files=$this->filter_files($files);

       $files=array_merge($base,$files);

       //$files=browse($this->local_dir(),array('/is','/sd','/ss'),'*.*');

       //populate/repopulate the ftp_in file
       $this->ftp_in=array(); $count=0;
       foreach($files as $file) {
           $count++;
           $this->ftp_in["$file"]=sha1_file($file);
       }

       array_put_contents($this->ftp_list_in,$this->ftp_in);

       if($synced) {
         array_put_contents($this->ftp_list_out,$this->ftp_in);
         return;
       }


     //get out files
     $this->ftp_out=array_get_contents($this->ftp_list_out);

       //lets compare with online
     $comp=$this->compare_files($this->ftp_in,$this->ftp_out);

     //clear duplicates
       $comp['upload']=array_unique($comp['upload']);
       $comp['remove']=array_unique($comp['remove']);


     if(empty($comp['upload']) && empty($comp['remove'])) {
       $this->output->writeln("<info>No changes detected</info>");
       return;
     }


     $upload_files=pluralize_if(count($comp['upload']),"file");
     $remove_files=pluralize_if(count($comp['remove']),"file");

     $rf= count($comp['remove'])==0 ? "":"-{$remove_files}";
     $this->output->writeln("<info>+{$upload_files} $rf</info>");

     //secure ftp connection
     $this->getFtpConnection();
     if(!$this->conn_id) {return;}


     $this->mass_upload($comp['upload']);
     $this->mass_delete($comp['remove']);
     }

     /**
     * mass upload files
     *
     */
     public function mass_upload($cfiles)
     {
       if(count($cfiles)==0) {return;}

       $this->output->writeln("<info>Preparing to upload ".pluralize_if(count($cfiles),'file')."</info>");

       //lets go and start commiting
       $dpath=rtrim($this->ftp_config['dir'],'/').'/'; //destination path online


       $dirlist=array();

       $rcount=0;
       foreach($cfiles as $file) {
         $hash=sha1_file($file);
         $remotefile= $dpath . str_replace(FCPATH,'',$file);
         $remotedir=pathinfo($remotefile,PATHINFO_DIRNAME);

         //$this->output->writeln($remotefile.' => '.$remotedir);
         //continue;

         //create directory if not exist
         if(!in_array($remotedir, $dirlist) && !@$this->ftp_directory_exists($remotedir,false))  {
             $this->output->writeln("<info>Creating $remotedir on remote server</info>");
             $this->ftp_mkdir($remotedir);
         }

         //check if file is binary
         $var=file_get_contents($file);
         $binary = (is_string($var) === true && ctype_print($var) === false);

         if (@ftp_put($this->conn_id, $remotefile, $file, $binary ? FTP_BINARY : FTP_ASCII)) {
           $rcount++;
           $this->output->writeln("<info>{$rcount}. Uploaded $remotefile on remote server</info>");

           $this->ftp_out["$file"]=sha1_file($file);

           //save uploaded file to config
           array_put_contents($this->ftp_list_out,$this->ftp_out);
         } else {
           $this->output->writeln("<info>Failed to create $remotefile on remote server</info>");
         }

         $dirlist[]=$remotedir;
       }

     }


     /**
     * mass delete files
     *
     */
     public function mass_delete($cfiles)
     {
       if(count($cfiles)==0) {return;}
       $this->output->writeln("<info>Preparing to remove ".pluralize_if(count($cfiles),'file')."</info>");


       //lets go and start commiting
       $dpath=rtrim($this->ftp_config['dir'],'/').'/'; //destination path online



       $dirlist=array();

       $rcount=0;
       foreach($cfiles as $file) {
         $remotefile= $dpath . str_replace(FCPATH,'',$file);
         $remotedir=pathinfo($remotefile,PATHINFO_DIRNAME);

         //$this->output->writeln($remotefile.' => '.$remotedir);
         //continue;
         // try to delete $file
         if (@ftp_delete($this->conn_id, $remotefile)) {
           $rcount++;
           $this->output->writeln("<info>{$rcount}. Removed $remotefile on remote server</info>");
         } else {
           $this->output->writeln("<info>Failed to remove $remotefile on remote server</info>");
         }

         //remove file from config
         if(isset($this->ftp_out["$file"])) {unset($this->ftp_out["$file"]);

         //save uploaded file to config
         array_put_contents($this->ftp_list_out,$this->ftp_out);
         $dirlist[]=$remotedir;
       }

       //remove directories if they are empty
       $dirlist=array_unique($dirlist);
       foreach($dirlist as $dir) {
         if (@ftp_rmdir($this->conn_id, $dir)) {
          $this->output->writeln("<info>Successfully deleted $dir</info>\n");
        }
       }

     }

     }


     /**
     * Commit ftp client
     *
     */
     public function ftp_pull()
     {
       $this->output->writeln("<info>Pulling data offline</info>");

       if(!file_exists($this->local_dir())) {
         mkdir($this->local_dir(),0777,true);
       }


     //$this->ftp_sync("/sandbox/tests");

     $localdir=$this->local_dir();
     $remotedir=$this->ftp_config['dir'];
     $subpath=str_replace(FCPATH,'',$localdir);

     if($subpath!='') {$remotedir=rtrim($remotedir,'/').'/'.ltrim($subpath,'/');}

     $this->_remotedir=$remotedir;
     $this->_localdir=$localdir;

     $this->getFtpConnection();
     if(!$this->conn_id) {return;}


     @chdir ($localdir);

     $this->ftp_sync($remotedir);
     }

     /**
     * sync an entire ftp path locally
     *
     * @dir the starting remote directory
     */
     function ftp_sync ($dir) {
         global $conn_id;

         if ($dir != ".") {
             if (ftp_chdir($this->conn_id, $dir) == false) {
                 $this->output->writeln("<info>Change Dir Failed: $dir</info>");
                 return;
             }

             if (!(is_dir($dir)))
                   @mkdir($dir,0777,true);
                   @chdir ($dir);
               }

         $contents = ftp_nlist($this->conn_id, ".");
         foreach ($contents as $file) {

             if ($file == '.' || $file == '..')
             {
               continue;
             }
             if (@ftp_chdir($this->conn_id, $file)) {
                 @ftp_chdir ($this->conn_id, "..");

                 $this->ftp_sync ($file);
             }
             else {
               //grab file
               $localfile=$this->_localdir. trim($dir,'/') . '/' . $file;

               $shortfile=str_replace(FCPATH,'',$localfile);

                 if(ftp_get($this->conn_id, $file, $file, FTP_BINARY)) {
                   $this->output->writeln("<info>Saved $shortfile</info>");

                   //update data coming from online
                   $this->ftp_out["$localfile"]=sha1_file($file);
                   //save uploaded file to config
                   array_put_contents($this->ftp_list_out,$this->ftp_out);

                 } else {
                   $this->output->writeln("<info>Error $localfile</info>");
                 }
             }
         }

         ftp_chdir ($this->conn_id, "..");
         chdir ("..");

     }


     /**
     * Reset data
     *
     */
     public function ftp_reset()
     {
       $helper = $this->getHelper('question');

       $options=array(
       '1'=>'Reset sync data to current',
       '2'=>'Wipe sync sync data',
       '3'=>'Wipe connection data',
       '0'=>'Quit ftp reset menu');


       $question = new ChoiceQuestion("Afro FTP Reset Options:",$options,0);
       $question->setErrorMessage('Option %s is invalid.');
       $opt = $helper->ask($this->input, $this->output, $question);

       $response=array_search($opt, $options);

       switch($response) {
       case "0":
       return;
       break;
       case "1":
       $this->output->write('<info>Reseting sync data...</info>');
       $this->ftp_commit(true);
       $this->output->writeln('<info>done</info>');
       break;
       case "2":
       $this->output->write('<info>Wiping sync data...</info>');

       array_put_contents($this->ftp_list_in);
       array_put_contents($this->ftp_list_out);

       $this->output->writeln('<info>done</info>');
       break;
       case "3":
       $this->output->write('<info>Wiping connection data...</info>');
       array_put_contents($this->ftp_list_conf);
       $this->output->writeln('<info>done</info>');
       break;
       }

       return;
     }



     /**
     * Show status
     *
     */
     public function ftp_status()
     {
       $data=$this->ftp_config;

       $data['pass']=str_repeat('*',strlen($data['pass']));

       $data['*local_path'] = $this->local_dir();

       $this->output->writeln("<info>Ftp Config Status:</info>");


       $tab=array();

       foreach($data as $key=>$value) {
         $tab[]=array($key,$value);
       }

       $table = new Table($this->output);
       $table
           ->setHeaders(array('Name', 'Value'))
           ->setRows($tab)
       ;
       $table->render();

       //$this->output->writeln("Pulling data offline");
     }


     /**
     * retrieves the local directory
     * you local directory should be something like:
     * /
     * system
     * system/core
     *
     */
     public function local_dir()
     {
       $ldir=$this->ftp_config['local_dir'];

       switch($ldir) {
         case FCPATH:
         case '/':
         case '':
         $ldir=FCPATH;
         break;
         default:
         $ldir=FCPATH.ltrim($ldir,'/');
         break;
       }

       return $ldir;
     }

     /**
     * retrieves the remote directory
     */
     public function remote_dir()
     {
       return '/'.trim($this->ftp_config['dir'],'/').'/';
     }

     //filter files
     public function filter_files($files) {
       $result=array();


       foreach ($files as $file) {
         if($this->ftp_ignore($file)) {continue;}
         $result[]=$file;
       }
       return $result;
     }


     /**
     * Should this file be excluded?
     *
     * @param string $file The name of a file
     *
     * @return boolean
     */
     public function ftp_ignore($file)
     {

     //list of files to ignore
     $invalid_file_list=array('.ds_store','.log','.zip');

     //list of folders to accept
     $valid_folder_list=array(APPPATH,FCPATH.'bin',FCPATH.'3rdparty',BASEPATH);

     //list of folders to ignore
     $invalid_folder_list=array(APPPATH.'config/console',APPPATH.'templates_c',APPPATH.'cache',APPPATH.'logs');

     //exclude the ftp config directory
     $dir=pathinfo($file,PATHINFO_DIRNAME);

     //ignore folders/subfolders that match
     foreach ($invalid_folder_list as $item) {
       if(strpos($file,$item)!==false) {return true;}
     }


     //ignore files/extensions that match
     foreach ($invalid_file_list as $item) {
       if(strpos($file,$item)!==false) {return true;}
     }

     //if any matches, then return false, as it is valid
     foreach ($valid_folder_list as $item) {
       if(strpos($file,$item)!==false) {return false;}
     }


     return true; //file is truly invalid
     }



     /**
     * gets ftp connection using current configuration
     */
       function getFtpConnection($testmode=false)
       {
           extract($this->ftp_config);

           //$host='test.localhost.com';


           if(!($conn_id = @ftp_connect($host,$port,$timeout))) {
             $this->output->writeln("<info>Couldn't connect to $host</info>");
             return false;
           }


           // try to login
           if (@ftp_login($conn_id, $user, $pass)) {
               $this->conn_id=$conn_id;
               $this->output->writeln("<info>FTP connected to $host</info>");
               if($testmode) {return true;}
           } else {
              $this->output->writeln("<info>Couldn't connect to $host as $user</info>");
              return false;
           }

           // turn passive mode on
           ftp_pasv($conn_id, true);



           if(!$this->ftp_directory_exists($dir,false)) {
             $this->output->writeln("<info>Creating $dir on remote server</info>");
             $this->ftp_mkdir($dir);
           }


           return $conn_id;
       }

       /**
       * checks if directory exists
       *
       * @param string  $dir  The name of the directory
       * @param boolean  $reset  Should directory be reset back to origin?
       *
       * @return boolean
       */
       function ftp_directory_exists($dir,$reset=true)
       {
         if(!$this->conn_id) {return false;}
         // Get the current working directory
         $origin = @ftp_pwd($this->conn_id);

         // Attempt to change directory, suppress errors
         if (@ftp_chdir($this->conn_id, $dir))
         {
             // If the directory exists, set back to origin
             if($reset) {@ftp_chdir($this->conn_id, $origin);}
             return true;
         }

         // Directory does not exist
         return false;
       }



       /**
       * creates directory recursively
       *
       * @param string  $path  The name of the directory
       *
       * @return boolean
       */
       function ftp_mkdir($path)
       {
        $dir=explode("/", $path);
        $path="";
        $ret = true;

        for ($i=0;$i<count($dir);$i++)
        {
            $path.="/".$dir[$i];
            //echo "$path\n";
            if(!@ftp_chdir($this->conn_id,$path)){
              @ftp_chdir($this->conn_id,"/");
              if(!@ftp_mkdir($this->conn_id,$path)){
               $ret=false;
               break;
              }
            }
        }
        return $ret;
       }


       /**
       * compares 2 repositories and returns the files that are different
       *
       *  @param  array  $local   the local repository
       *  @param  array  $local   the local repository
       *
       *
       * 1 - any files removed from local is deleted from remote
       * 2 - any files added or more modified is uploaded to remote
       *
       * returns a response that contains 2 arrays
       */
       function compare_files($local,$remote) {
       $response=array(
       'upload'=>array(),
       'remove'=>array(),
       );

       //detect files out of sync only i.e reupload these ones
       $result = array_keys(array_diff($local, $remote));
       $response['upload']=array_merge($response['upload'],$result);

       //upload these new files
       $result = array_diff(array_keys($local), array_keys($remote));
       $response['upload']=array_merge($response['upload'],$result);

       //remove these files
       $result = array_diff(array_keys($remote), array_keys($local));
       $response['remove']=array_merge($response['remove'],$result);

       return $response;
       }


}
