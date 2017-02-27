<?php
namespace Console;

defined('BASEPATH') or exit('No direct script access allowed');

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

(new afrocana())
->setName("composer")
->setDescription('Accepts composer commands for afrophp')
->setHelp('This command targets your third party folder')
->addArgument(
        'params',
        InputArgument::IS_ARRAY | InputArgument::REQUIRED,
        'This allows you to run regular composer commands e.g. ./afrocana composer require package'
    )
->execute(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afrocomposer($cmd,$input,$output))->exec();
});




class afrocomposer {

  public function __construct($command=null,$input=null, $output=null)
  {
    if($command!=null) {
      $this->command=$command;
      $this->input=$input;
      $this->output=$output;
    }
    chdir(FCPATH.'3rdparty');
  }

  function __destruct() {
    chdir(FCPATH.'bin');
   }


  /**
  */
  public function exec()
  {
    $args = implode(' ',$this->input->getArgument('params'));
    system("composer $args");
  }


}
