<?php
namespace Console;

defined('BASEPATH') or exit('No direct script access allowed');

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

(new afrocana())
->setName("cmd:create")
->setDescription('creates a new command')
->setHelp('This will create a new command in a plugin')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afrocommander($cmd,$input,$output))->_create();
});

(new afrocana())
->setName("cmd:list")
->setDescription('Lists all commands')
->setHelp('This will list all available command locations')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afrocommander($cmd,$input,$output))->_list();
});



class afrocommander {

  public function __construct($command=null,$input=null, $output=null)
  {
    if($command!=null) {
      $this->command=$command;
      $this->input=$input;
      $this->output=$output;
    }
  }


  public function _create()
  {
    $this->output->writeln("Create commands");
  }

  public function _list()
  {
    $this->command->io->listing(array(
    'Element #1 Lorem ipsum dolor sit amet',
    'Element #2 Lorem ipsum dolor sit amet',
    'Element #3 Lorem ipsum dolor sit amet',
  ));

    $this->output->writeln("List commands");
  }

}
