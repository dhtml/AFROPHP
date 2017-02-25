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
use Symfony\Component\Console\Input\InputArgument;


//taking user inputs e.g. bundle name
use Symfony\Component\Console\Question\Question;


//choice questions
use Symfony\Component\Console\Question\ChoiceQuestion;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;

//table helper
use Symfony\Component\Console\Helper\Table;


use Symfony\Component\Console\ConsoleEvents;


class Shell extends Command
{

 public $shell_config_file = APPPATH."config/console/default/conf.php";

 public $shell_config;


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
    $this->shell_config=Shell::load_config($this->shell_config_file);

    $style = new OutputFormatterStyle('red', 'default', array('bold'));
    $output->getFormatter()->setStyle('c', $style);

    $style = new OutputFormatterStyle('blue', 'default', array('bold'));
    $output->getFormatter()->setStyle('p', $style);

    $style = new OutputFormatterStyle('red', 'default', array('bold'));
    $output->getFormatter()->setStyle('h', $style);

    $style = new OutputFormatterStyle('default', 'default', array('bold'));
    $output->getFormatter()->setStyle('b', $style);


    $helper = $this->getHelper('question');

    $this->input=$input;$this->output=$output;

    $output->writeln("<c>Welcome to the AfroPHP Command Line Interface (AFRO CLI).</c>");
    $output->writeln("<c>For more information, type \"help\" or \"bye\" </c> \n");

    while(true) {
    $question = new Question('<p>Afro console$ </p>', '');
    $question->setAutocompleterValues($this->shell_config);

    $response = $helper->ask($input, $output, $question);

    if(!empty($response) && !in_array($response, $this->shell_config))  {
      $this->shell_config[]=$response;

      $this->shell_config=array_unique($this->shell_config);

      //save autocomplete data
      Shell::save_config($this->shell_config_file,$this->shell_config);
    }

    $this->exec_command($response);
    }

    //$this->exec_command("ftp status");
  }

  /**
  * Runs a command e.g ftp init, ftp --help
  * displays output on the console
  *
  * @return exit code
  */
  function exec_command($string) {
    $internal=strtolower(trim($string));


    switch($internal) {
      case 'clear':
      case 'cls':

      $_cmd=$this->toggle_os_command('clear','cls');
      system($_cmd);
      return;

      case 'bye':
      case 'exit':
      case 'quit':
      //$this->output->writeln("Goodbye");
      exit();
      return;
      break;
    }



    $input=$this->input;
    $output=$this->output;

    $args = preg_replace('/\s+/', ' ',$string);
    $args=explode(' ',$args);

    $command=$args[0];
    unset($args[0]);
    $params= implode(' ',$args);


    //stdout($command);
    //stdout($params);
    //exit();

    $returnCode=-1;


    try {
    $cmd = $this->getApplication()->find($command);

    switch (strtolower(trim($params))) {
      case '--help':
      case '--h':
      case '-help':
      case '-h':

      $output->writeln('<h>'.$cmd->getHelp().'</h>');
      $output->writeln('<b>'.$cmd->getDescription().'</b>');
      $output->writeln("\n<b>The parameters are displayed below:</b>");

      $params=$cmd->getDefinition()->getArgument('params')->getDescription();
      $params=trim($params);
      $e=explode("\n",$params);

      $tab=array();

      foreach($e as $line) {
        $l=explode(':',$line);
        $l=array_map('trim',$l);

        $title=$l[0];
        unset($l[0]);
        $def=implode(':',$l);

        $tab[]=array($title,$def);
      }

      $table = new Table($this->output);
      $table
          ->setHeaders(array('Parameter', 'Description'))
          ->setRows($tab)
      ;
      $table->render();

      return;
      break;
    }


    $arguments = array(
        'command' => $command,
        'params'    => $params,
    );



    $cmdInput = new ArrayInput($arguments);
    $returnCode = $cmd->run($cmdInput, $output);
  } catch(CommandNotFoundException $e)
  {
    system($string);
    //$output->writeln($command.": command not found");
  }  catch(InvalidArgumentException $e)
  {
        $output->writeLn($command.": invalid arguments exception");
  } catch(\Exception $err){
        $output->writeLn($command.": unknown error - ".$err->getMessage());
  }

  return $returnCode;
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
