<?php
defined('BASEPATH') or exit('No direct script access allowed');

require BASEPATH.'/base/symphony/autoload.php';

use Symfony\Component\Console\Application;


$console_directives=Array();

/**
* run symphone commands from plugins in case there is a 404 error
*/
function run_symphony_console()
{
global $console_directives;

$console = new Application();

//execute each directive file
foreach($console_directives as $file) {
  include $file;
  $fname=pathinfo($file,PATHINFO_FILENAME);
  $str='$'."console->add(new Console\\". $fname . "());";
  eval($str);
}

$console->run();

exit();
}
