<?php
namespace Console;

defined('BASEPATH') or exit('No direct script access allowed');

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class afrocana
{
  public $console;
  public $name='';
  public $description='';
  public $help='';
  public $fx='';
  public $hidden=false;
  public $arguments=array();

public function __construct() {
  //$this->console=$console;
}

public function setName($str) {$this->name=$str;return $this;}
public function setDescription($str) {$this->description=$str;return $this;}
public function setHelp($str) {$this->help=$str;return $this;}
public function setHidden($hidden) {$this->hidden=$hidden;return $this;}


/**
* Adds an argument.
*
* @param string $name        The argument name
* @param int    $mode        The argument mode: InputArgument::REQUIRED or InputArgument::OPTIONAL
* @param string $description A description text
* @param mixed  $default     The default value (for InputArgument::OPTIONAL mode only)
*
* @return $this
*/
public function addArgument($name, $mode = null, $description = '', $default = null)
{
    $this->arguments[]=array($name, $mode, $description, $default);
    return $this;
}

public function execute($fx) {
  $this->fx=$fx;
  global $console;

  $newcmd=new afrocommand($this);
  $console->add($newcmd);
}


}
