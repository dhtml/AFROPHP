<?php
defined('BASEPATH') or exit('No direct script access allowed');

require BASEPATH.'/base/symphony/vendor/autoload.php';

use Symfony\Component\Console\Application;


global $console_directives;

//stdout($console_directives);

$console = new Application();


//execute the shell
include __DIR__."/shell.php";

$command = new Console\Shell();

$console->add($command);
$console->setDefaultCommand($command->getName());


//execute each directive file
foreach($console_directives as $file) {
  $classes = get_declared_classes();
  include $file;

  $diff = array_diff(get_declared_classes(), $classes);

  //get the name of the class, and if there are namespaces, well, gets those ones too
  $class = reset($diff);

  $str='$'."console->add(new ". $class . "());";
  eval($str);
}

$console->run();

exit();
