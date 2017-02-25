<?php
namespace Console;
defined('BASEPATH') or exit('No direct script access allowed');


use Symfony\Component\Console\Command\Command;


class Afrocommand extends Command
{

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

}
