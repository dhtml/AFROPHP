<?php
/**
* system/core/common.php contains a list of functions that are required by the framework to perform its tasks
*/
defined('BASEPATH') or exit('No direct script access allowed');




/**
* array_multisort_field
*
* Sorts a multidimensional array by a particular key
*
* @param    array    $data      The array to be sorted
* @param    string   $parent    The name of the parent key
*
* @return array
*/
function array_multisort_field($data,$parent=null) {
  $parent_ids = array();
  foreach ($data as $item) {
      $parent_ids[]  = $item["$parent"];
  }


  array_multisort($parent_ids, SORT_ASC, $data);
  return $data;
}




/**
* get_instance
*
* Returns an instance of the application
*
* @return object
*/
function &get_instance()
{
    static $app;
    if (is_null($app)) {
        $app = Afrophp::instance();
    }
    return $app;
}

/**
* load_class
*
* Loads a new instance of a class
*
* @param	string   clsName   The filename/class name to convert
* @param	mixed    params    Optional parameters to pass to the class constructor
*
* @return object
*/
function &load_class($clsName, $params=null)
{
    $reflectionClass = new \ReflectionClass(toClassName($clsName));
    $obj=$reflectionClass->newInstanceArgs((array)$params);
    return $obj;
}

/**
* toClassName
*
* Converts a pathname to class name
*
* @param	name   The filename/class name to convert
*
* @return string
*/
function toClassName($name)
{
    return pathinfo($name, PATHINFO_FILENAME);
}

/**
 * get_config
 *
 * Load xml configuration files and stores the data statically
 *
 * @param	  array     $replace  An associative array containing directives to load
 * @param	  string    $path     The location of the xml config to load
 *
 * @return	array
 */
function &get_config(array $replace = array(), $file_path=null)
{
    static $config;
    if ($file_path!=null) {
        $found = false;
        if (file_exists($file_path)) {
            $found = true;

            $replace= xmlstring2array($file_path);
        }
        if (! is_array($replace)) {
            show_error('Your config file does not appear to be formatted correctly.', 200, 'Configuration Issue');
        }
    }
  // Are any values being dynamically added or replaced?
  foreach ($replace as $key => $val) {
      if(is_array($val)&&empty($val)) {$val='';}
      if($val=='null') {$val=null;}
      $config[$key] = $val;
  }
    return $config;
}



/**
 * config_item
 *
 * Returns the specified config item
 *
 * @param	string $item      The config item you are looking for e.g. uri_protocol
 *                          * will return the current values
 * @param	string $default   The default value
 * @param	string $eval      Evaluates the function when set to true
 *
 * @return	mixed
 */
function config_item($item, $default=null, $eval=false)
{
    static $_config;
    if (empty($_config)) {
        // references cannot be directly assigned to static variables, so we use an array
        $_config[0] =& get_config();
    }

    if($item=='*') {
      return $_config[0];
    }

    $response=isset($_config[0][$item]) ? $_config[0][$item] : $default;

    if($eval && !empty($response)) {
      @eval('$response= '.$response.';');
    }


    return $response;
}



/**
 * theme_item
 *
 * Returns the specified theme item
 *
 * @param	string $item      The config item you are looking for e.g. scripts
 * @param	string $default   The default value
 *
 * @return	mixed
 */
function theme_item($item, $default=null)
{
    static $_config;
    if (empty($_config)) {
        $_config[0] = get_instance()->theme->config();
    }
    return isset($_config[0][$item]) ? $_config[0][$item] : $default;
}

/**
* _download
*
* Download remote file and returns the content
*
* @param	string		$url	  The full url to download from
* @param	string		$local	The full local path to save to
*
* @return bool
*/
function _download($url,$local) {
$ch = curl_init($url);
$fp = fopen($local, "w");

curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);

curl_exec($ch);
curl_close($ch);
fclose($fp);

return file_exists($local) ? true : false;
}

/**
* remote_url_get
*
* Fetches an external file with curl
*
* @param  string  $url    The url to fetch
*
* @return string
*/
function remote_url_get($url) {
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
}


/**
* strip_file_ext
*
* removes extension from filename
*
* @param	string		$filename		The name of the file
*
* @return	the filename without extension
*/
function strip_file_ext($filename)
{
	return preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename);;
}

/**
* get_file_ext
*
* removes extension from filename
*
* @param	string		$filename		The name of the file
*
* @return	the filename extension
*/
function get_file_ext($filename)
{
  return pathinfo($filename, PATHINFO_EXTENSION);
}


/**
* _http_post
*
* post a remote url and returns the response
*
* @param  string  $url    The target url
* @param  array   $field  (optional) the post parameters
*
* @return string
*/
function _http_post($url,$fields=Array()) {

//url-ify the data for the POST
$fields_string='';
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');

//open connection
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

//execute post
$result = curl_exec($ch);

//close connection
curl_close($ch);

return $result;
}



/**
* _http_get
*
* gets a remote url and returns the response
*
* @param  string  $url    The target url
* @param  array   $field  (optional) the get parameters
*
* @return string
*/
function _http_get($url,$fields=Array()) {

//url-ify the data for the POST
if(!empty($fields)) {
        $query=new httpquery($url);
		foreach($fields as $key=>$value) {
			$query->set($key,$value);
		}
		$url=$query->rebuild();
}

//open connection
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

//execute post
$result = curl_exec($ch);

//close connection
curl_close($ch);

return $result;
}

/**
 * stdout
 *
 * standard output message processing
 *
 * @param   mixed    $info        The variable to be displayed on screen/console
 * @param   bool  $exit        Should execution be ceased after output?
 *
 * @param   void
 */
function stdout($info,$exit=false)
{
  $bt = debug_backtrace();
  $caller = array_shift($bt);

  $summary="";

  if(isset($caller['file']) && isset($caller['line'])) {
    $summary=$caller['file'].':'.$caller['line']."\n";
  }


    if (PHP_SAPI === 'cli') {
        print_r($info);
        echo "\n";
    } else {
      print '<pre style="padding: 1em; margin: 1em 0;">';
      echo "$summary";
      if(func_num_args() < 2) {
        print_r($info);
      } else {
        print_r($info);
        //print_r(func_get_args());
      }
      print '</pre>';
     }

    if($exit) {exit();}
}

/**
 * dhtmlconsole
 *
 * dhtmlconsole legacy function
 *
 * @param   mixed   $msg        (optional) The variable to be displayed in dhtmlconsole
 *
 * @param   void
 */
function dhtmlconsole($msg = '') {
    static $pos = 0;
    $pos++;

    if (!is_string($msg)) {$msg = json_encode($msg);
    }
    header('X-DHTML-CONSOLE-MSG' . $pos . ': ' . $msg);
}

/**
* show_error
*
* Shows error message from code
*
* @param  string  $message         The message to display
*
* @param  integer  $status_code    The exit status_code
*
* @param  string  $heading         The heading of the error
*
* @param  string  $type     The type of error
*                           1 - General error
*                           2 - Database error
*                           3 - 404 error
*
*
* @return void
*/
function show_error($message, $status_code = 500, $heading='Error', $type=1)
{
  $bt = debug_backtrace();
  $caller = array_shift($bt);

  $summary="";

  if(isset($caller['file']) && isset($caller['line'])) {

    $errfunc=$caller['function'];
    $errline=$caller['line'];
    $errfile=$caller['file'];


    if($caller['function']=='show_error' && is_cli()) {
      $summary.="Message: $message\n";
    } else  if($caller['function']=='show_error' && !is_cli()) {
        $summary.="<p>Message: $message</p>\n";
    } else if(is_cli()) {
      $summary.="Message: $message\n";
      $summary.="Filename: $errfile\n";
      $summary.="Function: $errfunc\n";
      $summary.="Line number: $errline\n\n";
    } else {
      $summary.="<p>Message: $message</p>\n";
      $summary.="<p>Filename: $errfile</p>\n";
      $summary.="<p>Function: $errfunc</p>\n";
      $summary.="<p>Line number: $errline</p><br/>\n";
    }
  }

    $status_code = abs($status_code);
    if ($status_code < 100) {
        $exit_status = $status_code + 9; // 9 is EXIT__AUTO_MIN
    $status_code = 500;
    } else {
        $exit_status = 1; // EXIT_ERROR
    }

  if (config_item('exception_show_error',false,true)) {
    $report= is_cli() ? "\n\nBacktrace:\n" : "<br/><p>Backtrace:</p>\n";

    foreach ($bt as $error) {
      if (isset($error['file']) && isset($error['line']) && isset($error['function']) ) {

        if(is_cli()) {
          $report.="File: {$error['file']} \n";
          $report.="Line: {$error['line']} \n";
          $report.="Function: {$error['function']} \n";
          } else {
          $report.="<p style=\"margin-left:10px\">\n";
          $report.="File: {$error['file']} <br />\n";
          $report.="Line: {$error['line']} <br />\n";
          $report.="Function: {$error['function']} <br />\n";
          $report.="</p>";
        }

      }
    }
  }


    $data = array(
          'heading' => $heading,
          'message' => "$summary $report",
        );

    $view='error_general';
    switch ($type) {
    case 2:$view='error_db';break;
    case 3:$view='error_404';break;
  }


    if (PHP_SAPI === 'cli') {
        $view="errors/cli/$view";
    } else {
        $view="errors/html/$view";
    }


    get_instance()->load->view("$view", $data);

    exit($exit_status);
}


/**
 * show_404
 *
 * 404 Page Handler
 *
 * @param  string  $message      The message to display
 *
 * @param  string  $heading   The heading of the error
 *
 * @param   bool  log_error  set to true to log error
 *
 * @return void
 */
function show_404($message='The page you requested was not found.', $heading='404 Page Not Found', $log_error = true)
{

    get_instance()->events->trigger('404');


    $data = array(
          'heading' => $heading,
          'message' => $message
  );

    $view="error_404";
    if (PHP_SAPI === 'cli') {
        $view="errors/cli/$view";
    } else {
        $view="errors/html/$view";
    }

    get_instance()->load->view("$view", $data);


    exit(4); // EXIT_UNKNOWN_FILE
}

 /**
 * set_status_header
 *
 * Set HTTP Status Header
 *
 * @param	int	the status code
 * @param	string   the status text
 *
 * @return	void
 */
function set_status_header($code = 200, $text = '')
{
    if (is_cli()) {
        return;
    }

    if (empty($code) or ! is_numeric($code)) {
        show_error('Status codes must be numeric', 500);
    }

    if (empty($text)) {
        is_int($code) or $code = (int) $code;
        $stati = array(
      100    => 'Continue',
      101    => 'Switching Protocols',

      200    => 'OK',
      201    => 'Created',
      202    => 'Accepted',
      203    => 'Non-Authoritative Information',
      204    => 'No Content',
      205    => 'Reset Content',
      206    => 'Partial Content',

      300    => 'Multiple Choices',
      301    => 'Moved Permanently',
      302    => 'Found',
      303    => 'See Other',
      304    => 'Not Modified',
      305    => 'Use Proxy',
      307    => 'Temporary Redirect',

      400    => 'Bad Request',
      401    => 'Unauthorized',
      402    => 'Payment Required',
      403    => 'Forbidden',
      404    => 'Not Found',
      405    => 'Method Not Allowed',
      406    => 'Not Acceptable',
      407    => 'Proxy Authentication Required',
      408    => 'Request Timeout',
      409    => 'Conflict',
      410    => 'Gone',
      411    => 'Length Required',
      412    => 'Precondition Failed',
      413    => 'Request Entity Too Large',
      414    => 'Request-URI Too Long',
      415    => 'Unsupported Media Type',
      416    => 'Requested Range Not Satisfiable',
      417    => 'Expectation Failed',
      422    => 'Unprocessable Entity',
      426    => 'Upgrade Required',
      428    => 'Precondition Required',
      429    => 'Too Many Requests',
      431    => 'Request Header Fields Too Large',

      500    => 'Internal Server Error',
      501    => 'Not Implemented',
      502    => 'Bad Gateway',
      503    => 'Service Unavailable',
      504    => 'Gateway Timeout',
      505    => 'HTTP Version Not Supported',
      511    => 'Network Authentication Required',
    );

        if (isset($stati[$code])) {
            $text = $stati[$code];
        } else {
            show_error('No status text available. Please check your status code number or supply your own message text.', 500);
        }
    }

    if (strpos(PHP_SAPI, 'cgi') === 0) {
        header('Status: '.$code.' '.$text, true);
    } else {
        $server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
        header($server_protocol.' '.$code.' '.$text, true, $code);
    }
}

/**
* secondsToTime
*
* converts time to a more readable format
*
* <code>
*  echo secondsToTime(1234561);
* </code>
*
* @param    int     $seconds    THe number of seconds
*
* outputs: 14 days 6 hours 56 minutes 1 second
*
* @return   string    The time in a readable format
*/
function secondsToTime($seconds)
{
    $seconds=round($seconds, 2);
    $days = floor($seconds / 86400);
    $seconds -= ($days * 86400);

    $hours = floor($seconds / 3600);
    $seconds -= ($hours * 3600);

    $minutes = floor($seconds / 60);
    $seconds -= ($minutes * 60);

    $values = array(
        'day'    => $days,
        'hour'   => $hours,
        'minute' => $minutes,
        'second' => $seconds
    );

    $parts = array();

    foreach ($values as $text => $value) {
        if ($value > 0) {
            $parts[] = $value . ' ' . $text . ($value > 1 ? 's' : '');
        }
    }

    return implode(' ', $parts);
}


/**
 * log_message
 *
 * Error Logging Interface
 *
 * We use this as a simple mechanism to access the logging
 * class and send messages to be logged.
 *
 * @param	$level  string	the error level: 'error', 'debug' or 'info'
 * @param $message  	string	the error message
 * @param $mail	  bool	set to true to send the message by email
 *
 * @return	void
 */
function log_message($level, $message=null, $mail=false)
{
    $lt=config_item('log_threshold');

    $logdir=APPPATH."logs";
    if (!is_dir($logdir)) {
        mkdir($logdir, 0755);
    }
    if ($message==null) {
        $message=$level;
        $level="info";
    }

    //if log is disabled
    if ($lt==0) {
        return;
    }

    //config level
    $level=trim(strtolower($level));
    switch ($level) {
      case 'error':
      if ($lt<1) {
          return;
      }
      break;
      case 'debug':
      if ($lt<2) {
          return;
      }
      break;
      default:
      if ($lt<3) {
          return;
      }
      $level="info";
      break;
    }


    $s='['.date("d-M-Y H:i:s e").']' . ' ' . $level . ': '.$message."\n";


    error_log($s, 3, $logdir."/errors.log");

    if ($mail) {
        mail_message($level, $message);
    }
}

 /**
 * mail_message
 *
 * Error Logging via email Interface
 *
 * We use this as a simple mechanism to access the logging
 * class and send messages to be logged.
 *
 * @param	string	the error level: 'error', 'debug' or 'info'
 * @param	string	(optional)the error message
 *
 * @return	integer  The number of mails sent
 */
function mail_message($level, $message=null)
{
    $count=0;
    if ($message==null) {
        $message=$level;
        $level="info";
    }

    $mail_body='['.date("d-M-Y H:i:s e").']' . ' ' . $level . ': '.$message."\n";

    if (function_exists('config_item')) {

        $er	= array(
        	'sender_name'=>config_item('exception_sender_name'),
        	'sender_email'=>config_item('exception_sender_email'),
        	'recipients'=> config_item('exception_recipients',array(),true),
        	'subject'=>config_item('exception_subject','"Error Type: {level}"'),
        	'enabled'=>config_item('exception_mailer_enabled',false,true),
        );



        if (!isset($er['enabled']) || $er['enabled']==false) {
            return 0;
        }
        $subject=isset($er['subject']) ? $er['subject'] : "Error Type: {level}";
        $recipients = isset($er['recipients']) ? $er['recipients'] : array('email@example.com');
        $subject=str_replace('{level}', $level, $subject);
        $sender_name = isset($er['sender_name']) ? $er['sender_name'] : 'Admin';
        $sender_email = isset($er['sender_email']) ? $er['sender_email'] : 'admin@website.com';

        $mailer=get_instance()->load->library('email');

        //$mailer->

        $mailer->from($sender_email, $sender_name);

        $mailer->subject($subject);
        $mailer->message($mail_body);


        $count=-1;
        foreach ($recipients as $recipient) {
            $count++;
            $mailer->to($recipient);
            $mailer->send();
        }
    }

    return $count;
}



 /**
 * bytes2string
 *
 * Converts size in bytes to string
 *
 * @param  int  $size     The size as an integer e.g. 1024
 *
 * @return 	string
 */
function bytes2string($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}


 /**
 * is_cli
 *
 * Test to see if a request was made from the command line.
 *
 * @return 	bool
 */
function is_cli()
{
    return (PHP_SAPI === 'cli' or defined('STDIN'));
}



/**
* browse
*
* This allows you to read files from a folder with subdirectories
*
* @param string  $dir   The starting directory
*
* @param  array  $flags   Some set of flags to guide the operation. The flags are array values
*                         /is - include subdirectories
*                         /sd - skip dots: skip files starting with dot (.) inside result
*                         /ss - skip subdirectories inside result
*                         /sf - skip files inside result
*
* The example below will list only files recursively, and not list dotted files and subdirectories
* <code>
* browse('/wamp/www',array('/is','/sd','/sd'))
* </code>
*
* @param string  $pattern   The pattern matching the file e.g. * or *.* for all files, *.txt for text files etc
*
* @param array   $results   An array of results used internally
*
* The example below will list only php files recursively, and not list dotted files and subdirectories
* <code>
* browse('/wamp/www',array('/is','/sd','/ss'),'*.php')
* </code>
*
* @return array   An array containing the file matches
*/
function browse($dir='.', $flags=array(), $pattern='*', &$results = array())
{
if(!file_exists($dir)) {return $results;}

    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        $pi=pathinfo($path);


        //stdout($pi);

        //skip files starting with dot
        if (in_array('/sd', $flags) && substr($pi['basename'], 0, 1)=='.') {
            continue;
        }

        if (!is_dir($path)) {

          //skip files in the result
          if (!in_array('/sf', $flags) && fnmatch(strtolower($pattern), strtolower($pi['basename']))) {
              $results[] = $path;
          }
        } elseif ($value != "." && $value != "..") {

            //include subdirectories in the search
            if (in_array('/is', $flags)) {
                browse($path, $flags, $pattern, $results);
            }

            //skip subdirectories in the result
            if (!in_array('/ss', $flags) && fnmatch(strtolower($pattern), strtolower($pi['basename']))) {
                $results[] = $path;
            }
        }
    }

    return $results;
}



/**
* xmlstring2array
*
* It converts an xml string into array
*
* @param  string  $string    The xml string to convert (or name of file)
*
* The xml string can follow the patterns below:
* pattern 1:
* <code>
* <?xml version="1.0" encoding="UTF-8"?>
* <prefix name="kinfet" label="Kinks and Fetishes" language_tag="en" language_label="English">
*    <key name="index_wiki_heading">
*       <value>Kinks Wiki</value>
*    </key>
*
*    <key name="kinfet_widget_title">
*       <value>ADD A KINK/FETISH</value>
*    </key>
* </prefix>
* </code>
*
* pattern 2:
* <?xml version="1.0" encoding="UTF-8"?>
* <code>
* <plugin>
*     <name>Commander</name>
*     <key>bootstrap</key>
*     <enable>1</enable>
*     <copyright>© 2017 All rights reserved.</copyright>
* </plugin>
* </code>
*
* It returns a response like this:
* Array
* (
*     [name] => Commander
*     [key] => bootstrap
*     [description] => Adds commandline optimization to your framework.
*     [version] => 1
*     [copyright] => © 2017 All rights reserved.
* )
*
* @return array
*/
function xmlstring2array($string)
{
    if (is_file($string)) {
        $string=file_get_contents($string);
    }
    $xml   = simplexml_load_string($string, 'SimpleXMLElement', LIBXML_NOCDATA);

    $array = json_decode(json_encode($xml), true);

    //only one item
    if (isset($array['key']['@attributes']['name'])) {
        return array($array['key']['@attributes']['name']=>$array['key']['value']);
    } elseif (isset($array['key']) && is_array($array['key'])) {
        //if key is found and it is multiple
        $result=array();
        foreach ($array['key'] as $arr) {
            $name=$arr['@attributes']['name'];
            $result[$name]=$arr['value'];
        }
        $array=$result;
    }
    return $array;
}



/**
* array2xmlstring
*
* It converts an array to xml string
*
* @param  array  $array    The array to convert
*
* The array must follow the patterns below:
* Array
* (
*     [name] => Commander
*     [key] => bootstrap
*     [description] => Adds commandline optimization to your framework.
*     [version] => 1
*     [copyright] => © 2017 All rights reserved.
* )
*
* @param  string  $root   The root element of the xml output e.g. root
*
* @param  bool  $xml   used internally
*
* The xml string output will follow the pattern below:
* <code>
* <?xml version="1.0"?>
* <root>
*     <name>Commander</name>
*     <key>bootstrap</key>
*     <enable>1</enable>
*     <copyright>© 2017 All rights reserved.</copyright>
* </root>
* </code>
*
* @return string
*/
function array2xmlstring($array, $root='root', $xml = false)
{
    if ($xml === false) {
        $xml = new SimpleXMLElement('<'.$root.'/>');
    }
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            array2xmlstring($value, $root, $xml->addChild($key));
        } else {
            $xml->addChild($key, $value);
        }
    }
    return $xml->asXML();
}




/**
* array2xml
*
* It converts an array to xml string
*
* @param  array  $array    The array to convert
*
* The array must follow the patterns below:
* Array
* (
*     [name] => Commander
*     [key] => bootstrap
*     [description] => Adds commandline optimization to your framework.
*     [version] => 1
*     [copyright] => © 2017 All rights reserved.
* )
*
* @param  string  $root   The root element of the xml output e.g. root
*
* The xml string output will follow the pattern below:
* <code>
* <?xml version="1.0"?>
* <resources>
*   <key name="cache_clear">
*      <value>Your cache has been cleared successfully</value>
*   </key>
*   <key name="cache_list">
*      <value>You have 20 items in your cache</value>
*   </key>
* </resources>
* </code>
*
* @return string
*/
function array2xml($array, $root='root')
{
    $domtree = new DOMDocument('1.0', 'UTF-8');
    $xmlRoot = $domtree->createElement($root);
    $xmlRoot = $domtree->appendChild($xmlRoot);

    foreach ($array as $key=>$value) {
        $item=$xmlRoot->appendChild($v = $domtree->createElement('key', ''));
        $v->appendChild($domtree->createAttribute('name'));
        $v->setAttribute('name', $key);

        $item->appendChild($v = $domtree->createElement('value', $value));
    }

    return $domtree->saveXML();
}




/**
*  parse_info_format
*
* parses drupal style info formats
*
* @param    string    $data       The raw content of the information (or file name)
*
* @return array
*/
function parse_info_format($data)
{
  if(is_file($data)) {
    $data=file_get_contents($data);
  }
    $info = array();

    if (preg_match_all('
    @^\s*                           # Start at the beginning of a line, ignoring leading whitespace
    ((?:
      [^=;\[\]]|                    # Key names cannot contain equal signs, semi-colons or square brackets,
      \[[^\[\]]*\]                  # unless they are balanced and not nested
    )+?)
    \s*=\s*                         # Key/value pairs are separated by equal signs (ignoring white-space)
    (?:
      ("(?:[^"]|(?<=\\\\)")*")|     # Double-quoted string, which may contain slash-escaped quotes/slashes
      (\'(?:[^\']|(?<=\\\\)\')*\')| # Single-quoted string, which may contain slash-escaped quotes/slashes
      ([^\r\n]*?)                   # Non-quoted string
    )\s*$                           # Stop at the next end of a line, ignoring trailing whitespace
    @msx', $data, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            // Fetch the key and value string.
      $i = 0;
            foreach (array('key', 'value1', 'value2', 'value3') as $var) {
                $$var = isset($match[++$i]) ? $match[$i] : '';
            }
            $value = stripslashes(substr($value1, 1, -1)) . stripslashes(substr($value2, 1, -1)) . $value3;

      // Parse array syntax.
      $keys = preg_split('/\]?\[/', rtrim($key, ']'));
            $last = array_pop($keys);
            $parent = &$info;

      // Create nested arrays.
      foreach ($keys as $key) {
          if ($key == '') {
              $key = count($parent);
          }
          if (!isset($parent[$key]) || !is_array($parent[$key])) {
              $parent[$key] = array();
          }
          $parent = &$parent[$key];
      }

      // Handle PHP constants.
      if (preg_match('/^\w+$/i', $value) && defined($value)) {
          $value = constant($value);
      }

      // Insert actual value.
      if ($last == '') {
          $last = count($parent);
      }
            $parent[$last] = $value;
        }
    }

    return $info;
}


/**
* get_path
*
* Retrieves the file path of a resource
*
* @param  string    $type     The type of extension e.g. theme,plugin
* @param  string    $name     The url of the extension e.g. theme,plugin
*
* @return the fully qualified file path
*/
function get_path($type, $name=null)
{
    $path="";
    if (is_null($name) && defined('current_theme')) {
        $name=current_theme;
    }

    switch ($type) {
    case 'theme':
    $path = FCPATH."themes/" . $name;
    break;
    case 'plugin':
    $plugin = get_instance()->loader->find_plugin($name);

    $path=isset($plugin['path']) ? $plugin['path'] : $path;
    break;
  }

    return $path;
}

/**
* _get_path
*
* gets the absolute path of a plugin by name or template
*
* @param string  $plugin  The name of the plugin.
*                         It can take theme as a special parameter to get the current theme path
*
* @return string
*/
function _get_path($plugin='theme')
{
  switch($plugin) {
    case 'theme':
    $path= defined('theme_path') ? theme_path : '';
    break;
    default:
    $path= get_path('plugin', $plugin);
  }

  return $path;
}


/**
* get_url
*
* Retrieves the url of a resource
* If the extension is not in a folder/subfolder containing the index.php, then a blank reponse is returned
*
* @param  string    $type     The type of extension e.g. theme,plugin
* @param  string    $name     The url of the extension e.g. theme,plugin
*
* @return the fully qualified url
*/
function get_url($type, $name=null)
{
    $path=get_path($type, $name);
    $path=str_replace(FCPATH, base_url, $path);
    if (substr($path, 0, 1)=='/') {
        $path='';
    }

    return $path;
}

/**
 * current_url
 *
 * Returns the full URL of the current page
 *
 * @return	string
 */
function current_url()
{
    return current_url;
}


/**
 * base_url
 *
 * returns the base_url of the website
 *
 * @return	string
 */
function base_url()
{
    return base_url;
}



/**
 * uri_string
 *
 * Returns the URI segments (without query)
 *
 * @return	string
 */
function uri_string()
{
    return request_uri;
}

/**
 * url_string
 *
 * Returns the URI segments (with query if any)
 *
 * @return	string
 */
function url_string()
{
    return request_url;
}



/**
 * auto_link
 *
 * Automatically links URL and Email addresses.
 * Note: There's a bit of extra code here to deal with
 * URLs or emails that end in a period. We'll strip these
 * off and add them after the link.
 *
 * @param	string	$str   the string to process
 * @param	string	$type  the type: email, url, or both
 * @param	bool	 $popup  whether to create pop-up links
 *
 * @return	string
 */
function auto_link($str, $type = 'both', $popup = false)
{
    // Find and replace any URLs.
  if ($type !== 'email' && preg_match_all('#(\w*://|www\.)[^\s()<>;]+\w#i', $str, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
      // Set our target HTML if using popup links.
    $target = ($popup) ? ' target="_blank"' : '';

    // We process the links in reverse order (last -> first) so that
    // the returned string offsets from preg_match_all() are not
    // moved as we add more HTML.
    foreach (array_reverse($matches) as $match) {
        // $match[0] is the matched string/link
      // $match[1] is either a protocol prefix or 'www.'
      //
      // With PREG_OFFSET_CAPTURE, both of the above is an array,
      // where the actual value is held in [0] and its offset at the [1] index.
      $a = '<a href="'.(strpos($match[1][0], '/') ? '' : 'http://').$match[0][0].'"'.$target.'>'.$match[0][0].'</a>';
        $str = substr_replace($str, $a, $match[0][1], strlen($match[0][0]));
    }
  }

  // Find and replace any emails.
  if ($type !== 'url' && preg_match_all('#([\w\.\-\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+[^[:punct:]\s])#i', $str, $matches, PREG_OFFSET_CAPTURE)) {
      foreach (array_reverse($matches[0]) as $match) {
          if (filter_var($match[0], FILTER_VALIDATE_EMAIL) !== false) {
              $str = substr_replace($str, safe_mailto($match[0]), $match[1], strlen($match[0]));
          }
      }
  }

    return $str;
}



/**
 *  url_title
 *
 * Create URL Title
 *
 * Takes a "title" string as input and creates a
 * human-friendly URL string with a "separator" string
 * as the word separator.
 *
 * @param	string	$str		Input string
 * @param	string	$separator	Word separator
 *			(usually '-' or '_')
 * @param	bool	$lowercase	Whether to transform the output string to lowercase
 * @return	string
 *
 */
function url_title($str, $separator = '-', $lowercase = false)
{
    if ($separator === 'dash') {
        $separator = '-';
    } elseif ($separator === 'underscore') {
        $separator = '_';
    }

    $q_separator = preg_quote($separator, '#');

    $trans = array(
    '&.+?;'            => '',
    '[^\w\d _-]'        => '',
    '\s+'            => $separator,
    '('.$q_separator.')+'    => $separator
  );

    $str = strip_tags($str);
    foreach ($trans as $key => $val) {
        $str = preg_replace('#'.$key.'#i'.(UTF8_ENABLED ? 'u' : ''), $val, $str);
    }

    if ($lowercase === true) {
        $str = strtolower($str);
    }

    return trim(trim($str, $separator));
}


/**
 * strip_query_string
 *
 * @param   string    $uri    The uri e.g. cache/clear?v=1&u=2
 *
 * @return	string
 */
function strip_query_string($uri)
{
    $u=explode('?', $uri);
    return $u[0];
}

/**
 * site_url
 *
 * Site URL
 *
 * Forms a full url out of a partial url
 *
 * @param	string	$uri   The partial or complete url
 *
 * @return	string the full url
 */
function site_url($uri = '')
{
    if (empty($uri)) {
        return base_url;
    } elseif (substr($uri, 0, 2)=='//') {
        return $uri;
    } elseif (substr($uri, 0, 4)=='www.') {
        return 'http://'.$uri;
    } elseif (substr($uri, 0, 5)=='http:') {
        return $uri;
    } elseif (substr($uri, 0, 6)=='https:') {
        return $uri;
    } elseif (strpos($uri, FCPATH)!==false) {
        return str_replace(FCPATH, base_url, $uri);
    }

    $uri=trim($uri,'/');

    if($uri=='admin') {
      $uri=config_item('admin_path');
    } else if (substr($uri,0,6)=='admin/') {
      $uri=config_item('admin_path').substr($uri,5);
    }


    $pi=parse_url($uri);
    if (isset($pi['scheme']) && isset($pi['path'])) {
        return $pi;
    }

    $return=base_url . ltrim($uri, '/');

    return $return;
}

/**
 * redirect
 *
 * Header Redirect
 *
 *
 * @param	string	$uri	URL
 * @param	string	$method	Redirect method
 *			'auto', 'location' or 'refresh'
 * @param	int	$code	HTTP Response status code
 *
 * @return	void
 */
function redirect($uri = '', $method = 'auto', $code = null)
{
    if (! preg_match('#^(\w+:)?//#i', $uri)) {
        $uri = site_url($uri);
    }

    if ($method === 'auto' && isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false) {
        $method = 'refresh';
    } elseif ($method !== 'refresh' && (empty($code) or ! is_numeric($code))) {
        if (isset($_SERVER['SERVER_PROTOCOL'], $_SERVER['REQUEST_METHOD']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.1') {
            $code = ($_SERVER['REQUEST_METHOD'] !== 'GET')
        ? 303
        : 307;
        } else {
            $code = 302;
        }
    }

    switch ($method) {
    case 'refresh':
      header('Refresh:0;url='.$uri);
      break;
    default:
      header('Location: '.$uri, true, $code);
      break;
  }
    exit;
}


/**
* set_title
*
* sets or retrieves the title of the current page
*
* @param  string  $title    The title of the page to set
*                           If title of null, nothing will be set
*
* @return the title of the page
*/
function set_title($title=null)
{
    static $_title;

    if (!is_null($title)) {
        $_title = check_plain($title);
    }

    return $_title;
}

/**
* site_title
*
*
* sets or retrieves the title of the current page
*
* @param  string  $title    The title of the page to set
*                           If title of null, nothing will be set
*
* @return the title of the page
*/
function site_title($title=null)
{
    return set_title($title);
}

/**
* check_plain
*
* Forces the text to escape special characters
*
* @param  string    $text     The text to work on
*
* @return string
*/
function check_plain($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
* addScript
*
* Adds a script to the theme
*
* @param  string    $string     The name of the file or the source code
*
* @param  string    $placement  The placement of the script
*                               top - the top of the page
*                               bottom  - the bottom of the page
*
* @param  string    $scope      The scope of the script
*                               inline - treats as inline
*                               plugin - the current plugin folder
*                               theme  - the current theme folder
*                               anyname  - the name of a plugin e.g. forums
*
* <code>
* addScript("alert('This is a serious matter');",null,'inline');
* addScript("alert('Testing the mike');",'bottom','inline');
* addScript("alert('Testing the top');",'top','inline');
* addScript("js/bootstrap.min.js");
* addScript("js/bootstrap.min.js",'bottom','plugin');
* addScript("js/test.js",'top','theme');
* addScript("js/forum.js",'top','forums');
* </code>
*
* @return void
*/
function addScript($string, $placement='top', $scope='plugin')
{
    $placement= $placement=='top' ? $placement : 'bottom';

    if ($scope=='inline') {
        Theme::$inserts['js_inline_'.$placement].="$string\n";
    } else {
        if (is_absolute_url($string)) {
            $target=$string;
        } else {
            switch ($scope) {
    case "plugin":
    $key = array_search(__FUNCTION__, array_column(debug_backtrace(), 'function'));
    $file=debug_backtrace()[$key]['file']; //the file that called the function
    $dir=pathinfo($file, PATHINFO_DIRNAME);
    $dir=str_replace('/controllers', '', $dir);
    $dir=str_replace(FCPATH, base_url, $dir);
    break;
    case "theme":
    $dir=trim(theme_url, '/');
    break;
    default:
    $plugin=get_instance()->loader->find_plugin($scope);
    $dir=isset($plugin['path']) ? $plugin['path'] : '';
    $dir=str_replace(FCPATH, base_url, $dir);
    break;
    }
            $dir=ltrim($dir, '/');
            $target="{$dir}/$string";
        }

        Theme::$inserts['js_src_'.$placement][]=$target;
    }

  //var_dump($dir);
  //stdout($script);
}


/**
* addStyle
*
* Adds a stylesheet to the theme
*
* @param  string    $string     The name of the file or the source code
*
* @param  string    $placement  The placement of the stylesheet
*                               top - the top of the page
*                               bottom  - the bottom of the page
*
* @param  string    $scope      The scope of the stylesheet
*                               inline - treats as inline
*                               plugin - the current plugin folder
*                               theme  - the current theme folder
*                               anyname  - the name of a plugin e.g. forums
*
* <code>
* addStyle(".test {color:red;background:gray;}",null,'inline');
* addStyle(".test2 {color:red;background:green;}",'bottom','inline');
* addStyle("css/test.css");
* addStyle("css/test.css","bottom");
* </code>
*
*
* @return void
*/
function addStyle($string, $placement='top', $scope='plugin')
{
    $placement= $placement==null ? 'top' : $placement;
    $placement= $placement=='top' ? $placement : 'bottom';

    if ($scope=='inline') {
        Theme::$inserts['css_inline_'.$placement].="$string\n";
    } else {
        if (is_absolute_url($string)) {
            $target=$string;
        } else {
            switch ($scope) {
      case "plugin":
      $key = array_search(__FUNCTION__, array_column(debug_backtrace(), 'function'));
      $file=debug_backtrace()[$key]['file']; //the file that called the function
      $dir=pathinfo($file, PATHINFO_DIRNAME);
      $dir=str_replace('/controllers', '', $dir);
      $dir=str_replace(FCPATH, base_url, $dir);
      break;
      case "theme":
      $dir=trim(theme_url, '/');
      break;
      default:
      $plugin=get_instance()->loader->find_plugin($scope);
      $dir=isset($plugin['path']) ? $plugin['path'] : '';
      $dir=str_replace(FCPATH, base_url, $dir);
      break;
      }
            $dir=ltrim($dir, '/');
            $target="{$dir}/$string";
        }

        Theme::$inserts['css_src_'.$placement][]=$target;
    }
}


/**
* addTag
*
* Adds a tag to the theme
*
* @param  string    $string     The tag to add to the page
*
* @param  string    $placement  The placement of the script
*                               top - the top of the page
*                               bottom  - the bottom of the page
*
*
* <code>
* addTag('<meta name="keywords" values="tony,ayo,jide">');
* </code>
*
* @return void
*/
function addTag($string, $placement='top')
{
    $placement= $placement==null ? 'top' : $placement;
    $placement= $placement=='top' ? $placement : 'bottom';

    Theme::$inserts['inline_'.$placement].="$string\n";
}

/**
* is_absolute_url
*
* checks if a url is absolute
*
* @param  string  $url  The target url
*
* @return bool
*/
function is_absolute_url($url)
{
    $pattern = "/^(?:ftp|https?|feed):\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
    (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
    (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
    (?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

    return (bool) preg_match($pattern, $url);
}


/**
* bind
*
* binds an event
*
* <code>
* bind('menu', 'core_output_actions4');
*
* bind('menu', function() {
*      echo "Menu Init Stub 3<br/>";
* });
* </code>
*
* @param    string    $name         The name of the event
* @param    mixed     $callback     The event callback
*
* @return object
*/
function bind($name, $callback)
{
    return get_instance()->events->bind($name,$callback);
}





/**
* ip_address
*
* Get the current ip address
*
* @return string
*/
function ip_address() {

	//Get IP address - if proxy lets get the REAL IP address
	if (!empty($_SERVER['REMOTE_ADDR']) AND !empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = '0.0.0.0';
	}

	//Clean the IP and return it
	return sanitize_text($ip, 2);
}


/**
* current_domain
*
* Get the current domain
*
* @return string
*/
function current_domain() {

	// Get the Site Name: www.site.com -also protects from XSS/CSFR attacks
	$regex = '/((([a-z0-9\-]{1,70}\.){1,5}[a-z]{2,4})|localhost)/i';

	//Match the name
	preg_match($regex,(!empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST']), $match);

	//MUST HAVE A HOST!
	if(empty($match[0])) {
		show_error('Sorry, host not found');
	}

	return $match[0];
}

/**
 * sanitize_text
 *
 * Cleans text of all bad characters
 *
 * @param string	$text	text to clean
 *
 * @param bool	$level	Set to TRUE to only enable file safe chars
 *
 * @return void
 */
function sanitize_text($text, $level=0){
	if(!$level) {
		//Delete anything that isn't a letter, number, or common symbol - then HTML encode the rest.
		return trim(htmlentities(preg_replace("/([^a-z0-9!@#$%^&*()_\-+\]\[{}\s\n<>:\\/\.,\?;'\"]+)/i", '', $text), ENT_QUOTES, 'UTF-8'));
	} else {
		//Make the text file/title/emailname safe
		return preg_replace("/([^a-z0-9_\-\.]+)/i", '_', trim($text));
	}
}


if (! function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null) {
        $array = array();
        foreach ($input as $value) {
            if ( !array_key_exists($columnKey, $value)) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            }
            else {
                if ( !array_key_exists($indexKey, $value)) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if ( ! is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }
}



//added as at 1.0

/**
 * Checks that a directory exists and is writable. If the directory does
 * not exist, the function will try to create it and/or change the
 * CHMOD settings on it.
 *
 * @param string $dir	directory you want to check
 * @param string $chmod	he CHMOD value you want to make it
 * @return unknown
 */
function directory_usable($dir, $chmod='0777') {

	//If it doesn't exist - make it!
	if(!is_dir($dir)) {
		if(!@mkdir($dir, $chmod, true)) {
			show_error('Could not create the directory: <b>'. $dir. '</b>');
			return;
		}
	}

	//Make it writable
	if(!is_writable($dir)) {
			show_error("<b>$dir</b> is not writable.");
			return;
	}

	return true;
}



/**
 * A function to recursively delete files and folders
 * @thanks: dev at grind [[DOT]] lv
 *
 * @param string	$dir	The path of the directory you want deleted
 * @param boolean	$remove	Remove Files (false) or Folder and Files (true)
 * @return boolean
 */
function destroy_directory($dir='', $remove=true) {

	//Try to open the directory handle
	if(!$dh = opendir($dir)) {
		trigger_error('<b>'. $dir. '</b> cannot be opened or does not exist', E_USER_WARNING);
		return;
	}

	//While there are files and directories in this directory
	while (false !== ($obj = readdir($dh))) {

		//Skip the object if it is the linux current (.) or parent (..) directory
		if($obj=='.' || $obj=='..') continue;

		$obj = $dir. $obj;

		//If the object is a directory
		if(is_dir($obj)) {

			//If we could NOT delete this directory
			if(!destroy_directory($obj, $remove)) {
				return;
			}

			//Else it must be a file
		} else {
			unlink($obj) or trigger_error('Could not remove file <b>'. $obj. '</b>', E_USER_WARNING);
		}

	}

	//Close the handle
	closedir($dh);

	if ($remove){
		rmdir($dir) or trigger_error('Could not remove directory <b>'. $dir. '</b>');
	}

	return true;
}


/**
 * Gzip/Compress Output
 * Original function came from wordpress.org
 * @return void
 */
function gzip_compression() {

	//If no encoding was given - then it must not be able to accept gzip pages
	if(!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) { return false; }

	//If zlib is not ALREADY compressing the page - and ob_gzhandler is set
	if (( ini_get('zlib.output_compression') == 'On'
	|| ini_get('zlib.output_compression_level') > 0 )
	|| ini_get('output_handler') == 'ob_gzhandler' ) {
		return false;
	}

	//Else if zlib is loaded start the compression.
	if ( (extension_loaded( 'zlib' ))
	&& (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ) {
		ob_start('ob_gzhandler');
	}
	/*
	 print $_SERVER['HTTP_ACCEPT_ENCODING']. '<br />'.
	 'extension_loaded("zlib") = '. extension_loaded( 'zlib' ). '<br />'.
	 'ini_get("zlib.output_compression") = '. ini_get('zlib.output_compression'). '<br />'.
	 'ini_get("output_handler") = '. ini_get('output_handler'). '<br />';
	 */
}


/**
* @param string $filename <p>file name including folder.
* example :: /path/to/file/filename.ext or filename.ext</p>
* @param string $data <p> The data to write.
* </p>
* @param int $flags same flags used for file_put_contents.
* more info: http://php.net/manual/en/function.file-put-contents.php
* @return bool <b>TRUE</b> file created succesfully <br> <b>FALSE</b> failed to create file.
*/
function file_force_contents($filename, $data, $flags = 0){
    if(!is_dir(dirname($filename)))
        mkdir(dirname($filename).'/', 0777, TRUE);
    return file_put_contents($filename, $data,$flags);
}
