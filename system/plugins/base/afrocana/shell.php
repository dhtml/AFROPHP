<?php
namespace Console;

defined('BASEPATH') or exit('No direct script access allowed');

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

(new afrocana())
->setName("shell")
->setDescription('Accepts operating commands from afrophp')
->setHelp('This command targets your root folder')
->addArgument(
        'params',
        InputArgument::IS_ARRAY | InputArgument::REQUIRED,
        'This allows you to run os commands e.g. ./afrocana shell ls'
    )
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afroshell($cmd,$input,$output))->exec();
});




class afroshell {

  public function __construct($command=null,$input=null, $output=null)
  {
    if($command!=null) {
      $this->command=$command;
      $this->input=$input;
      $this->output=$output;
    }
    chdir(FCPATH);
  }

  function __destruct() {
    chdir(FCPATH.'bin');
   }


  /**
  */
  public function exec()
  {
    $args = implode(' ',$this->input->getArgument('params'));
    system("$args");
  }


}
