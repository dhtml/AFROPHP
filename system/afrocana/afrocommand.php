<?php
namespace Console;

defined('BASEPATH') or exit('No direct script access allowed');

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Exception\RuntimeException;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;

use Symfony\Component\Console\Style\SymfonyStyle;


/**
* template for creating afrocana command
*/
class afrocommand extends Command
{

  public function __construct($afrocana)
  {
    $this->afrocana=$afrocana;
    parent::__construct();
  }

  /**
   * Interacts with the user.
   *
   * This method is executed before the InputDefinition is validated.
   * This means that this is the only place where the command can
   * interactively ask for values of missing required arguments.
   *
   * @param InputInterface  $input  An InputInterface instance
   * @param OutputInterface $output An OutputInterface instance
   */
   protected function interact(InputInterface $input, OutputInterface $output)
   {
   }

  protected function configure()
  {

      //add commandline arguments
      if(!empty($this->afrocana->arguments)) {
        foreach($this->afrocana->arguments as $arg) {
          list($name, $mode, $description, $default) = $arg;
          $this->addArgument($name, $mode, $description, $default);
        }
      }

      $this->setName($this->afrocana->name)
       ->setHelp($this->afrocana->help)
       ->setDescription($this->afrocana->description)
       ->setHidden($this->afrocana->hidden)
          ;
  }

  public function write($var) {$this->output->write($var);}
  public function writeln($var) {$this->output->writeln($var);}

  public function execute(InputInterface $input, OutputInterface $output)
  {


    $this->io = new SymfonyStyle($input, $output);

    $style = new OutputFormatterStyle('red', 'default', array('bold'));
    $output->getFormatter()->setStyle('c', $style);

    $style = new OutputFormatterStyle('blue', 'default', array('bold'));
    $output->getFormatter()->setStyle('p', $style);

    $style = new OutputFormatterStyle('red', 'default', array('bold'));
    $output->getFormatter()->setStyle('h', $style);

    $style = new OutputFormatterStyle('default', 'default', array('bold'));
    $output->getFormatter()->setStyle('b', $style);


    $this->input=$input;
    $this->output=$output;


    if(is_callable($this->afrocana->fx) || function_exists($this->afrocana->fx)) {
      call_user_func_array($this->afrocana->fx, array($input,$output,$this));
    } else if(is_string($this->afrocana->fx)) {
      $output->writeln("The console command function {$this->fx} is missing");
    } else {
      $output->writeln("The console command function is missing");
    }
    return;
  }


  /**
  * writes out error text
  *
  */
  public function errorText($command,$code=0)
  {
    switch($code) {
      case 0:
      return "$command: command not found";
      break;
      case 1:
      return "$command: parameters are not correct";
      break;
      case 2:
      return "$command: no parameters found";
      break;
      default:
      return "$command: unknown error ocurred";
      break;
    }
  }


}
