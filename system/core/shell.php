<?php
namespace Console;
defined('BASEPATH') or exit('No direct script access allowed');


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\InvalidArgumentException;

use Symfony\Component\Console\Input\ArrayInput;

//taking user inputs e.g. bundle name
use Symfony\Component\Console\Question\Question;


//choice questions
use Symfony\Component\Console\Question\ChoiceQuestion;


//table helper
use Symfony\Component\Console\Helper\Table;


use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Console\ConsoleEvents;


class Shell extends Command
{
  protected function configure()
  {
    $this
    // the name of the command (the part after "bin/console")
    ->setName('shell')

    // the short description shown while running "php bin/console list"
    ->setDescription('Launches AfroPHP console.')

    // the full command description shown when running the command with
    // the "--help" option
    ->setHelp('An interactive console used to execute commands of AfroPHP');
  }



  public function execute(InputInterface $input, OutputInterface $output)
  {
    $this->input=$input;$this->output=$output;

    $dispatcher = new EventDispatcher();


    $this->exec_command("ftp --help");

    return;
    try {
    $command = $this->getApplication()->find('greet');

    $arguments = array(
        'command' => 'ftp',
        'params'    => '--help',
    );

    $greetInput = new ArrayInput($arguments);
    $returnCode = $command->run($greetInput, $output);
    $output->writeLn($returnCode);

  } catch(CommandNotFoundException $e)
  {
    $output->writeln("command not found");
  }  catch(InvalidArgumentException $e)
  {
        $output->writeLn("Invalid arguments exception");
  }


  }

  /**
  * Runs a command e.g ftp init, ftp --help
  *
  */
  function exec_command($string) {
    $input=$this->input;
    $output=$this->output;

    $args = preg_replace('/\s+/', ' ',$string);
    $args=explode(' ',$args);

    $command=$args[0];
    unset($args[0]);
    $params= implode(' ',$args);


    stdout($command);
    stdout($params);
    exit();



    try {
    $command = $this->getApplication()->find('greet');

    $arguments = array(
        'command' => 'ftp',
        'params'    => '--help',
    );

    $greetInput = new ArrayInput($arguments);
    $returnCode = $command->run($greetInput, $output);
    $output->writeLn($returnCode);

  } catch(CommandNotFoundException $e)
  {
    $output->writeln("command not found");
  }  catch(InvalidArgumentException $e)
  {
        $output->writeLn("Invalid arguments exception");
  }



  }


  /**
  * retrieve the parameters of the command
  *
  * when commands are re-routed by the shell
  * they are passed as a string with space separated tokens
  * in that case, they need to be decrypted
  *
  * @return an array of arguments
  */
  public static function getParams(InputInterface $input)
  {
    $args = $input->getArgument('params');
    if(is_string($args)) {
      $args = preg_replace('/\s+/', ' ',$args);
      $args=explode(' ',$args);
    }
    return $args;
  }

  /**
  * writes out error text
  *
  */
  public static function errorText($command,$code=0)
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


  /**
  * saves config data into an array
  *
  * @param string $file    The name of the file
  * @param array  $array   The array data
  *
  * returns the array
  */
  public static function save_config($file,$array=array())
  {
    $output = '<?' . 'php ' . 'return ' . var_export($array, true) . ';';
    file_force_contents($file,$output);
    return (array) $array;
  }


public static function wipe_config($file) {
  self::save_config($file);
}


    /**
    * loads config data into an array
    *
    * @param string $file   The name of the file
    *
    * returns the array
    */
    public static function load_config($file)
    {
      $result=array();
      if(file_exists($file)) {
        $result=include $file;
      }
      return (array) $result;
    }

    public static function dump_files($files,$stop=false)
    {
        foreach ($files as $key => $value) {
          $files[$key] = str_replace(FCPATH,'',$value);
        }


      stdout($files,$stop);
    }


}
