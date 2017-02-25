<?php
namespace Console;
defined('BASEPATH') or exit('No direct script access allowed');

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;


//taking user inputs e.g. bundle name
use Symfony\Component\Console\Question\Question;


//choice questions
use Symfony\Component\Console\Question\ChoiceQuestion;


//table helper
use Symfony\Component\Console\Helper\Table;

use Symfony\Component\Console\Command\Command;


class Afrocommand extends Command
{

public $args;

public function __construct() {
  parent::__construct();
}

/**
* create parameters argument and sets description
*
*/
function setArguments($description) {
  $this->addArgument('params',InputArgument::IS_ARRAY,$description);
  return $this;
}

/**
* executes the command
*
*
*/
public function execute(InputInterface $input, OutputInterface $output)
{
  $this->input=$input;$this->output=$output;
  $args = Shell::getParams($this->input);
  $this->args=$args;
}

/**
* returns a particular parameters
*
*/
public function param($key,$default=null)
{
return isset($this->args[$key]) ? $this->args[$key] : $default;
}


/**
* Toggles a string based on os
*
* @param  string $os The result in mac/linus or non-windows
* @param  string $win The result in a windows os
*
* return string
*/
function toggle_os_command($os,$win)
{
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      return $win;
  } else {
      return $os;
  }
}

/**
* writes out error text
*
*/
public function errorText($command,$code=0)
{
return Shell::errorText($command,$code);
}

/**
* saves config data into an array
*
* @param string $file    The name of the file
* @param array  $array   The array data
*
* returns the array
*/
public function save_config($file,$array=array())
{
return Shell::save_config($file,$array);
}


public function wipe_config($file) {
  return Shell::wipe_config($file);
}
/**
* loads config data into an array
*
* @param string $file   The name of the file
*
* returns the array
*/
public function load_config($file)
{
return Shell::load_config($file);
}


public static function dump_files($files,$stop=false)
{
  return Shell::dump_files($files,$stop);
}


}
