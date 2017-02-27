<?php
namespace Console;

defined('BASEPATH') or exit('No direct script access allowed');

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

(new afrocana())
->setName("env:init")
->setDescription('initializes your environment variable')
->setHelp('This will create .environment in your root folder')
->execute(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afroenvironment($cmd,$input,$output))->init();
});

(new afrocana())
->setName("env:hta")
->setDescription('creates htaccess in the root of the installation')
->setHelp('This will create/recreate the .htaccess')
->execute(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afroenvironment($cmd,$input,$output))->hta();
});



class afroenvironment {

  public function __construct($command=null,$input=null, $output=null)
  {
    if($command!=null) {
      $this->command=$command;
      $this->input=$input;
      $this->output=$output;
    }
    file_force_contents(env_init,'');
  }

  function __destruct() {
    //chdir(FCPATH.'bin');
   }


  /**
  */
  public function init()
  {
    if(file_exists(env_data)) {unlink(env_data);}
    $this->output->writeln("Please open the url of this app in your browser");
    while(!file_exists(env_data)) {
      sleep(2);
    }
    $this->output->writeln("Initialization complete");
  }

  public function hta()
  {
    if(!file_exists(env_data)) {$this->init();}
    $env=array_get_contents(env_data);
    create_htaccess($env['rewrite_base']);
    $this->output->writeln("Created ".FCPATH.'.htaccess successfully');
  }


}
