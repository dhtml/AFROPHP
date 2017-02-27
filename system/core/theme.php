<?php
/**
* system/core/theme.php contains a list of functions are used for theming
* the class is an extension of smarty 3 template engine
*/
defined('BASEPATH') or exit('No direct script access allowed');

/**
* Theme class
*/
class Theme extends Smarty
{


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
      directory_usable(APPPATH.'templates_c');

      //set directories
      $this->setCompileDir(APPPATH.'templates_c');

      parent::__construct();


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


      $theme=config_item(admin_mode?'back_theme':'front_theme');

      define('current_theme',$theme);

      $path=APPPATH."themes/{$theme}/";

      define('theme_path',$path);
      define('theme_url',site_url($path));
      define('current_template',$template);

      define('template_path',theme_path.'templates/'.$template.'/');
      define('template_url',theme_url.'templates/'.$template.'/');


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


      if(!file_exists($vpath)) {$vpath=null;}

      //if no theme is getting used
      if(is_cli() || is_null($theme) || empty($theme)) {

        if($vpath==null) {
          $view="";
        } else {
         $view=$this->fetch($vpath);
        }

        $this->render_output($view);
        return;
      }



      $this->assign('theme_url',theme_url);
      $this->assign('template_url',template_url);

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
      $page_content= $vpath==null ? "" : $this->fetch($vpath);


      //attempt to pass things through the current template.html if it exists
      $template_file=template_path.'template.html';

      $this->assign('page_content',$page_content);

      if(file_exists($template_file)) {
        //load template
        $pageBody=$this->fetch($template_file);
      } else if(file_exists(theme_path."page.html")) {
        //load normal page.html
        $pageBody=$this->fetch(theme_path."page.html");
      } else {
        $pageBody=$page_content;
      }

      //assign page body
      $this->assign('pageBody',$pageBody);

      if(file_exists(theme_path."master.html")) {
        $output=$this->fetch(theme_path."master.html");
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
      $version= $type=='css' ?  config_item('style_version',0,true) : config_item('script_version',0,true);

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
    * fetches a smarty template and returns the content
    * it also sets the current directory to that view path
    *
    * @param string $template  the template path
    * @param string $cache_id  the cache id
    * @param string $compile_id  the compile id
    * @param string $parent  the parent
    *
    * @return string
    */
    function fetch($template = NULL, $cache_id = NULL, $compile_id = NULL, $parent = NULL) {
    $this->setTemplateDir(pathinfo($template,PATHINFO_DIRNAME));
    return parent::fetch($template,$cache_id,$compile_id,$parent);
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

}
