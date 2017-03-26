<?php
/**
* system/core/theme.php contains a list of functions are used for theming
*/
defined('BASEPATH') or exit('No direct script access allowed');


/**
* Theme class
*/
class Theme
{


  /**
  * assigned variables
  */
  public $theme_vars=array();

  /**
  * compiled search for assigned and defined vars
  */
  public $search_vars=array();

  /**
  * compiled replacement for assigned and defined vars
  */
  public $replace_vars=array();

  /**
  * theme plugins
  *
  */
  public $theme_plugins=array();


  /**
   *  Configuration data from the .info files are stored here
   *
   *  @access public
   *
   *  @var  array
   */
   public $data;

   /**
   *  Information that goes into the head section of the page is stored here
   *
   *  @access public
   *
   *  @var  string
   */
   public $headData='';


  /**
  *  Information that goes into the footer section of the page is stored here
  *
  *  @access public
  *
  *  @var  string
  */
   public $footData='';


   /**
   *  Assets that goes into the head/footer section are stored from from inline to external styles and scripts
   *
   *  @access public
   *
   *  @var  array
   */
   public static $inserts=array(
     'inline_top'=>'',
     'inline_bottom'=>'',

     'css_inline_top'=>'',
     'css_inline_bottom'=>'',

     'js_inline_top'=>'',
     'js_inline_bottom'=>'',


     'css_src_top'=>array(),
     'css_src_bottom'=>array(),

     'js_src_top'=>array(),
     'js_src_bottom'=>array(),
   );

   /**
    *  Class constructor
    *
    *  @access public
    *
    *  @var  object
    */
    public function __construct()
    {

      $this->registerPlugin("function","text", "theme_text_func");

      $this->registerPlugin("block","translate", "theme_translate_block");

      $this->registerPlugin("function","cycle", "theme_cycle_func");

      $this->registerPlugin("function","include", "theme_include_func");

      $this->registerPlugin("block","strip", "theme_strip_block");

      // registering the object (will be by reference)
      //$this->registerObject('afrophp', $this);
      //$this->registerPlugin("block","translate", [$this,"do_translation"]);
    }

    /**
    * theme_init
    *
    * Initializes the theme based on the route settings to determine if it is a frontend or backend theme that is required
    *
    * @return void
    */
    public function theme_init()
    {
      $app=get_instance();


      $route=isset($app->router->routes[$app->router->_current]) ? $app->router->routes[$app->router->_current] : array();

      //stdout($route);
      extract($route);

      if(!isset($route['uri'])) {return;}

      //detect if route is admin
      $admin_path=config_item(trim('admin_path','/'),'admin');

      if($uri==$admin_path) {
          $admin_mode=true;
      }  else if(substr($uri,0,strlen($admin_path.'/'))==$admin_path.'/') {
          $admin_mode=true;
      } else {
          $admin_mode=false;
      }

      define('admin_mode',$admin_mode);


      $theme=config_item(admin_mode?'theme_back':'theme_front');

      define('current_theme',$theme);

      $path=APPPATH."themes/{$theme}/";

      define('theme_path',$path);
      define('theme_url',site_url($path));
      define('current_template',$template);

      define('template_path',theme_path.'templates/'.$template.'/');
      define('template_url',theme_url.'templates/'.$template.'/');

      define('site_name',config_item('site_name'));



      if(!is_cli() || !is_null(current_theme)) {
        //load the Information file from the template
        $this->data=parse_info_format(theme_path.'theme.info');

        //load template information if it exists
        $this->data=array_merge($this->data,parse_info_format(template_path.'template.info'));

        $stylesheets=theme_item('stylesheets');
        $scripts=theme_item('scripts');

        //preload into buffer stylesheets and js
        //load stylesheets to top of theme
        if(isset($stylesheets['all']) && !empty($stylesheets['all'])) {
          $this->preload_assets($stylesheets['all'],'css');
        }

        //load scripts at top specified by theme
        if(isset($scripts['all']) && !empty($scripts['all'])) {
          $this->preload_assets($scripts['all'],'js');
        }


        //load stylesheets to footer of theme
        if(isset($stylesheets['bottom']) && !empty($stylesheets['bottom'])) {
          $this->preload_assets($stylesheets['bottom'],'css','bottom');
        }

        //load scripts at footer specified by theme
        if(isset($scripts['bottom']) && !empty($scripts['bottom'])) {
          $this->preload_assets($scripts['bottom'],'js','bottom');
        }

        //load php files from the bool directory of current theme
        foreach((browse(theme_path.'bool',array('/is','/sd','/ss'),'*.php')) as $file) {
            include ($file);
        }

        //load php files from the bool directory of current template
        foreach((browse(template_path.'bool',array('/is','/sd','/ss'),'*.php')) as $file) {
            include ($file);
        }

        //load theme initializer if it exists i.e theme.php
        if(file_exists(theme_path.'theme.php')) {include theme_path.'theme.php';}

        //load template initialize if it exists i.e template_name.php
        if(file_exists(template_path.'template.php')) {include template_path.'template.php';}
      }
    }

    /**
    * renders a view to the output
    * if the current theme is not set (or we are in cli mode), then the output is rendered directly
    * otherwise the output is sent to the theme for additional processing
    *
    * @param   string    $vpath    The path of the view file
    *
    * @return void
    */
    public function render($vpath)
    {
      $this->finalize_assets();

      $theme=current_theme;


      $ext=pathinfo($vpath,PATHINFO_EXTENSION);

      if($ext!='php' && $ext!='html') {
        if(file_exists("{$vpath}.php")) {$vpath="{$vpath}.php";}
        else if(file_exists("{$vpath}.html")) {$vpath="{$vpath}.html";}
      }


      if(!file_exists($vpath)) {$vpath=null;}

      //if no theme is getting used
      if(is_cli() || is_null($theme) || empty($theme)) {

        if($vpath==null) {
          $view="";
        } else {
         $view=$this->load_file($vpath);
        }

        $this->render_output($view);
        return;
      }


      $this->assign('system_messages',system_render_messages());

      $this->assign('theme_url',theme_url);
      $this->assign('template_url',template_url);

      $this->assign('asset_url',asset_url);

      $this->assign('site_name',config_item('site_name','my site'));
      $this->assign('charset',config_item('charset'));



      $this->assign('theme_path',theme_path);
      $this->assign('template_path',template_path);

      $this->assign('page_lang',config_item('language','en'));
      $this->assign('page_title',set_title());
      $this->assign('headData',$this->headData);
      $this->assign('footData',$this->footData);


      if ($this->getTemplateVars('page_direction') === null)
      {
        $this->assign('page_direction', 'auto');
      }

      if ($this->getTemplateVars('bodyClass') === null)
      {
        $this->assign('bodyClass', '');
      }

      //load view and assign
      $page_content= $vpath==null ? "" : $this->load_file($vpath);


      //attempt to pass things through the current template.html if it exists
      $template_file=template_path.'template.html';

      $this->assign('page_content',$page_content);

      if(file_exists($template_file)) {
        //load template
        $pageBody=$this->load_file($template_file);
      } else if(file_exists(theme_path."page.html")) {
        //load normal page.html
        $pageBody=$this->load_file(theme_path."page.html");
      } else {
        $pageBody=$page_content;
      }

      //assign page body
      $this->assign('pageBody',$pageBody);

      if(file_exists(theme_path."master.html")) {
        $output=$this->load_file(theme_path."master.html");
      } else {
        $output=$pageBody;
      }

      $this->render_output($output);
    }

    /**
    * render_output
    *
    * finally attempt to render the content to the browser
    * it attempts to cache results (except in admin mode and cli mode)
    *
    * @param  string    $response     The html response to render
    *
    * @return void
    */
    public function render_output($response)
    {
      if(!admin_mode && !is_cli()) {

        $key=request_uri;

        $key= empty($key) ? '/' : $key;

        get_instance()->cache->save($key,$response);
      }

      //$format=new htmlformater();
      //$response = $format->HTML($response);


      echo $response;
    }

    /**
    * Returns the current configuration data
    *
    * @return array
    */
    public function config()
    {
      return $this->data;
    }

    /**
    * Finalizes the assets that goes into the head and footer sections
    *
    * @return void
    */
    public function finalize_assets()
    {
      extract(self::$inserts);

      $css_inline_top=$this->preprocess_asset($css_inline_top,'css');
      $css_inline_bottom=$this->preprocess_asset($css_inline_bottom,'css');

      $js_inline_top=$this->preprocess_asset($js_inline_top,'js');
      $js_inline_bottom=$this->preprocess_asset($js_inline_bottom,'js');

      $css_src_top=$this->preprocess_asset($css_src_top,'css');
      $css_src_bottom=$this->preprocess_asset($css_src_bottom,'css');


      $js_src_top=$this->preprocess_asset($js_src_top,'js');
      $js_src_bottom=$this->preprocess_asset($js_src_bottom,'js');

      $this->headData="{$inline_top}{$css_src_top}{$css_inline_top}{$js_src_top}{$js_inline_top}";
      $this->footData="{$inline_bottom}{$css_src_bottom}{$css_inline_bottom}{$js_src_bottom}{$js_inline_bottom}";

    }

    /**
    * prepares assets for adding to the theme
    *
    * @param  mixed    $assets   The assets, string or array
    * @param  string   $type     The type of asset i.e css or html
    *
    * @return string
    */
    public function preprocess_asset($assets,$type='css')
    {
      $response='';


      if(!is_array($assets)) {

        $assets=trim($assets);
        //assets is a string
        if(!empty($assets)) {
          $response= $type=='js' ? "<script>\n" : "<style type=\"text/css\" media=\"all\">\n";
          $response.= $assets;
          $response.= $type=='js' ? "</script>\n" : "</style>\n";
        }
      } else if(!empty($assets)){
        $assets=array_unique($assets);
        switch($type) {
          case "css":
          $response="<style type=\"text/css\" media=\"all\">\n";
            foreach($assets as $link) {
              $link=$this->asset_cache_burster($link,'css');
              $response.="@import url(\"$link\");\n";
            }
            $response.="</style>\n";
          break;
          case "js":
          $response='';

          foreach($assets as $link) {
            $link=$this->asset_cache_burster($link,'js');
            $response.="<script src=\"$link\"></script>\n";
          }
          break;
        }


      }

      return $response;
    }

    /**
    * adds ?ver=version to the end of scripts and stylesheets
    * The script_version and style_version in the cache configuration will determine this behaviour.
    *
    * @param  string    $link     The link to the asset
    * @param  string    $type     The type of asset namely css or js
    *
    * @return string
    */
    public function asset_cache_burster($link,$type)
    {
      $version= $type=='css' ?  config_item('cache_style',0,true) : config_item('cache_script',0,true);

      if($version==0) {return;}
      else if($version==-1) {$version=mt_rand();}
      if(strpos($link,'?')===false) {$link.="?ver=$version";} else {$link.="&ver=$version";}

      return $link;
    }

    /**
    * preloads assets from theme.info into the buffer
    *
    * @param  array   $array        An array containing urls of stylesheets
    * @param  string  $type         Either css or js
    * @param  string  $placement    Top or bottom of the page
    *
    * @return object
    */
    public function preload_assets($array=null,$type,$placement='top')
    {
      if(!is_array($array)||empty($array)) {return "";}

      $placement= $placement=='top' ? $placement : 'bottom';
      $type= $type=='js' ? $type : 'css';




      foreach($array as $link) {
        $link=$this->expand_url($link);
        if($type=='js') {
          addScript($link,$placement,'theme');
        } else {
          addStyle($link,$placement,'theme');
        }
      }
      return $this;
    }


    /**
    * load view and returns the response
    *
    * @param string $file the path of the template
    * @param array $vars the associative array containing view data
    *
    * @return string
    */
    function load_file($file,$vars=array()) {
      if(pathinfo($file,PATHINFO_EXTENSION)!='html') {
        $response=get_instance()->load->view($file,$vars,true);
      } else {
      if(!empty($vars) && is_array($vars)) {
        foreach($vars as $name=>$value) {
          $this->assign($name,$value);
        }
      }
      $response=$this->fetch($file);
      }
      return $response;
    }

    /**
    * Attempts to complete a uri to a full url if it is not done already
    *
    * @param  string   $uri   An incomplete url e.g. css/theme.css
    *
    * @return string
    */
    public function expand_url($uri)
    {
      if(empty($uri)) {return base_url;}
      else if(substr($uri,0,2)=='//') {return $uri;}
      else if(substr($uri,0,4)=='www.') {return 'http://'.$uri;}
      else if(substr($uri,0,5)=='http:') {return $uri;}
      else if(substr($uri,0,6)=='https:') {return $uri;}
      else if(strpos($uri,FCPATH)!==false) {return str_replace(FCPATH,base_url,$uri);}


      $pi=parse_url($uri);
      if(isset($pi['scheme']) && isset($pi['path'])) {return $pi;}

      return theme_url . ltrim($uri,'/');
    }

    /**
    * fetches all available themes
    *
    * @return array
    */
    public function all()
    {
      $theme_info_paths=browse(APPPATH.'themes',array('/is','/sd'),'theme.info');


      $front_path=APPPATH."themes/".config_item('theme_front');
      $back_path=APPPATH."themes/".config_item('theme_back');


      $pos=0;
      $themes=array();
      foreach($theme_info_paths as $theme_info_path) {
        $tag= 'info';

        $data=parse_info_format($theme_info_path);

        $pos++;
        $hash=str_pad($pos, 3, '0', STR_PAD_LEFT);
        $theme=array();
        $theme['path']=pathinfo($theme_info_path,PATHINFO_DIRNAME);
        $theme['key']=$hash;

        if($theme['path']==$front_path) {$status="front";}
        else if($theme['path']==$back_path) {$status="back";}
        else {$status='disabled';$tag='debug';}
        $theme['status']=$status;

        $theme['path'].="/";

        $theme=array_merge($theme,$data);
        $themes[]=$theme;
      }
      return $themes;
    }

    /**
    * finds a theme by name
    *
    * @param string $name The name of the theme
    *
    * @return array
    */
    public function find_by_name($name)
    {
      $result=Array();
      foreach($this->all() as $theme) {
        if(strtolower($theme['name'])==strtolower($name)) {$result[]=$theme;}
      }
      return $result;
    }




    /**
    * registers a block or function
    *
    * @param string $plugin_type The type of plugin i.e. function or block
    * @param string $plugin_name The name of the plugin e.g. cycle
    * @param string $plugin_func The name of the plugin function e.g. theme_circle
    * @param object $plugin_obj The name of the object with the plugin_func
    *
    * @return object
    */
    function registerPlugin($plugin_type,$plugin_name,$plugin_func,$plugin_obj=null)
    {
    $this->theme_plugins[$plugin_name]=array(
    'type'=> $plugin_type=='function' ? 'function' : 'block',
    'func'=>$plugin_func,
    'obj'=>$plugin_obj,
    );

    return $this;
    }

    function assign($name,$value)
    {
    $this->theme_vars[$name]=$value;
    return $this;
    }

    function display($file) {
    $output=$this->fetch($file);
    echo $output;
    }

    function fetch($file) {
    $this->current_dir=pathinfo($file,PATHINFO_DIRNAME);



    //load file from disk
    $source=file_get_contents($file);

    //get search and replace vars
    $this->search=array();
    $this->replace=array();

    //get assigned vars
    foreach($this->theme_vars as $key=>$value)
    {
      if(is_array($value)) {continue;}
      $this->search_vars[]='{$'.$key.'}';
      $this->replace_vars[]=$value;
    }

    //get defined vars
    $k=get_defined_constants(true);
    if(isset($k['user'])) {
      foreach($k['user'] as $key=>$value)
      {
        $this->search_vars[]='{'.$key.'}';
        $this->replace_vars[]=$value;
      }
    }





    $source=$this->exec_for_each($source);

    $source=$this->parse_block($source);

    $source=str_replace(array('{*','*}'),array('<!--','-->'),$source);

    //preg_match_all('/{(.*?)}/', $source, $matches);

    //var_dump($matches);

    return $source;
    }

    /**
    * parses a block of string and returns the response
    *
    * @param string $output The template output
    *
    * @return string
    */
    public function parse_block($output)
    {
      $search=$this->search_vars;
      $replace=$this->replace_vars;


      $output=$this->parse_plugins($output);

      return str_replace($search,$replace,$output);
    }

    /**
    * fetch parameters from contents
    *
    * <code>
    * {cycle values="lagos,ibadan"}
    * </code>
    *
    * @return array
    */
    protected function fetch_params($raw='')
    {
      $xml=str_replace(array('{','}'),array('<','/>'),$raw);
      $array = (array) json_decode(json_encode((array)simplexml_load_string($xml)),1);

      return isset($array['@attributes']) ? $array['@attributes']: array();
    }


    protected function parse_plugins($output)
    {
    foreach($this->theme_plugins as $name=>$opt) {
    //var_dump($opt);

    $object=$opt['obj'];
    $callback=$opt['func'];


    if($opt['type']=='function') {

      while(true) {
      if(!$i=stripos($output,'{'.$name)) {break;}
      if(!$j=stripos($output,'}',$i+1)) {break;}

      //plugin implementation
      $raw=substr($output,$i,$j-$i+1);

      $params=$this->fetch_params($raw);


      $result='';
      if(is_object($object)) {
        $result=call_user_func(array($object, $callback),$params,$this);
      } else if(is_string($callback)) {
        $result=call_user_func($callback,$params,$this);
      } else if(is_object($callback)) {
        $result=call_user_func($callback,$params,$this);
      }

      $output=substr($output,0,$i).$result.substr($output,$i+strlen($raw));
      }


    } else {
    //process block
    while(true) {
    if(!$i=stripos($output,'{'.$name)) {break;}
    if(!$j=stripos($output,'}',$i+1)) {break;}
    if(!$k=stripos($output,'{/'.$name,$j+1)) {break;}

    //plugin implementation
    $start=substr($output,$i,$j-$i+1);
    $content=substr($output,$j+1,$k-$j-1);

    $raw=substr($output,$i,$k-$i+strlen($name)+3);

    $params=$this->fetch_params($start);


      $result='';
      if(is_object($object)) {
        $result=call_user_func(array($object, $callback),$params,$content,$this);
      } else if(is_string($callback)) {
        $result=call_user_func($callback,$params,$content,$this);
      } else if(is_object($callback)) {
        $result=call_user_func($callback,$params,$content,$this);
      }

      $output=substr($output,0,$i).$result.substr($output,$i+strlen($raw));
    }

    }

    }

    return $output;
    }

    function parse_content($content,$name,$value) {
    $search=array();$replace=array();

    if(is_array($value)) {
    foreach($value as $k=>$v) {
    $search[]='{$'.$name.'.'.$k.'}';
    $replace[]=$v;
    }
    } else {
    $search[]='{$'.$name.'}';
    $replace[]=$value;
    }
    $content=str_replace($search,$replace,$content);

    return $content;
    }

    function exec_for_each($source)
    {

    while(true) {
    if(!$i=stripos($source,'{foreach')) {break;}
    if(!$j=stripos($source,'{/foreach}')) {break;}

    $raw=substr($source,$i,$j-$i+10);

    $block=$raw;

    preg_match_all('/{(.*?)}/', $block, $matches);


    //var_dump($this->theme_vars);

    $result='';
    if (!empty($matches[1])) {
        $search=array();
        $replace=array();
        foreach ($matches[1] as $item) {

          if(substr($item,0,8)=='foreach ') {
            $i=strpos($item,'$');
            $j=strpos($item,'as');
            $var=trim(substr($item,$i+1,$j-$i-2));
            $item2=$this->str_replace_once($var,'this->theme_vars[\''.$var.'\']',$item);

            //get alias
            $i=strpos($item,'as');
            $j=strpos($item,'$',$i);
            $alias=trim(substr($item,$j+1));

            $search[]='{'.$item.'}';
            $replace[]=str_replace('foreach ','<?php foreach(',$item2).') { $alias=$'.$alias.';  ob_start();?>';
          } else if($item=='/foreach') {
            $search[]='{'.$item.'}';
            $replace[]='<?php $content=ob_get_contents(); ob_end_clean(); $result.=$this->parse_content($content,"'.$alias.'",$alias); } ?>';
          }
        }
        $block=str_replace($search, $replace, $block);
    }

    eval(' ?>'.$block.'<?php ');

    //var_dump($block);
    //var_dump($result);

    $source=$this->str_replace_once($raw,$result,$source);
    }


    return $source;
    }

    function getTemplateVars($var=null)
    {
      if($var==null) {return $this->theme_vars;}
      else if(isset($this->theme_vars["$var"])) {
        return $this->theme_vars["$var"];
      }
    }

    function str_replace_once($str_pattern, $str_replacement, $string){

        if (strpos($string, $str_pattern) !== false){
            $occurrence = strpos($string, $str_pattern);
            return substr_replace($string, $str_replacement, strpos($string, $str_pattern), strlen($str_pattern));
        }

        return $string;
    }


}



/**
* translate a text from inside the template file using language files found in a plugin
*
* <code>
* {text key="base+greet"}
* </code>
*
* @param  array     $params     array of parameters
* @param  object    $theme     template object
*
* @return   the translated string
*/
function theme_text_func($params, $theme) {
        if (empty($params["key"])) {
            return "";
        } else {
            $key = explode('+',$params["key"]);
            return get_instance()->lang->text($key[0], $key[1]);
        }
}

/**
* translate a text from inside the template file using language files found in a plugin
*
* <code>
* {translate from="en" to="fr" }I am single{/translate}
* </code>
* if from is not specified, auto will be assumed
* if to is not specified, default is the current language being used
*
* @param  array     $params     array of parameters
* @param  string    $content    the content of the block (between the tags)
* @param  object    $theme     template object
* @param  integer   $repeat     The current repeat value. A block function is called twice.
*
* @return   the translated string
*/
function theme_translate_block($params, $content, $theme) {
  if (isset($content)) {
    $to = isset($params["to"]) ? $params["to"] : null;
    $from = isset($params["from"]) ? $params["from"] : null;
    // do some translation with $content
    $translation=get_instance()->lang->translate($content,$to,$from);
    return $translation;
  }
}


/**
* cycles through a string
*
* <code>
* {cycle values="#eeeeee,#dddddd"}
* </code>
*
* @param  array     $params     array of parameters
* @param  object    $theme      theme object
*
* @return   the return string
*/
function theme_cycle_func($params,$theme)
{
static $_values;
static $_pos;

$values=isset($params['values']) ? $params['values'] : array();
$items=explode(',',$values);


$_pos=is_null($_pos) ? 0 : $_pos;

if($_values==$values) {$_pos++;}

$_values=$values;

if(!isset($items[$_pos])) {$_pos=0;}

return isset($items[$_pos]) ? $items[$_pos] : '';
}



/**
* translate a text from inside the template file using language files found in a plugin
*
* <code>
* {strip from="en" to="fr" }I am single{/strip}
* </code>
* if from is not specified, auto will be assumed
* if to is not specified, default is the current language being used
*
* @param  array     $params     array of parameters
* @param  string    $content    the content of the block (between the tags)
* @param  object    $theme     theme object
*
* @return   the string
*/
function theme_strip_block($params, $content, $theme) {
  $content=$theme->parse_block($content);
  return $content;
}


/**
* include an external template file
*
* <code>
* {include file="side_profile.html"}
* </code>
*
* @param  array     $params     array of parameters
* @param  object    $theme     theme object
*
* @return   the string
*/
function theme_include_func($params, $theme) {
  $file=$theme->current_dir.'/'.$params['file'];
  $source=file_get_contents($file);
  return $source;
  //$content=$theme->parse_block($content);
  //return $content;
}

//stdout($this->current_dir);
