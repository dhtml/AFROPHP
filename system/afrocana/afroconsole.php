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



(new afrocana())
->setName("history:list")
->setDescription('Lists commands history')
->setHelp('The list of commands used recently in afrocana console')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afroconsole($cmd,$input,$output))->history_list();
});

(new afrocana())
->setName("history:clear")
->setDescription('Clears commands history')
->setHelp('Clears the list of commands used recently in afrocana console')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afroconsole($cmd,$input,$output))->history_clear();
});


(new afrocana())
->setName("console")
->setDescription('Starts the afrocana command line interactive interface')
->setHelp('This interface shortens you command, you can do stuffs like ftp:status')
->setHidden(true)
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afroconsole($cmd,$input,$output))->interactive();
});



class afroconsole {
  public $shell_config_file = APPPATH."config/console/default/conf.php";

  public $shell_config;

  public function __construct($command=null,$input=null, $output=null)
  {
    if($command!=null) {
      $this->command=$command;
      $this->input=$input;
      $this->output=$output;

      //load config if it actually exists
      $this->shell_config=array_get_contents($this->shell_config_file);
    }
  }

  /**
  * List command history
  */
  public function history_list()
  {
    if(empty($this->shell_config)) {
      $this->output->writeln("<info>No command history</info>");
    } else {
      $this->command->io->title("Commands history");
      $this->command->io->listing($this->shell_config);
    }
  }


  /**
  * List command history
  */
  public function history_clear()
  {
    if(empty($this->shell_config)) {
      $this->output->writeln("<info>No command history</info>");
    } else {
      array_put_contents($this->shell_config_file);
      $this->output->writeln("<info>Command history cleared</info>");
    }
  }

  public function interactive()
  {
    $helper = $this->command->getHelper('question');


    $this->output->writeln("<c>Welcome to the Afrocana Command Line Interface (AFROPHP CLI).</c>");
    $this->output->writeln("<c>For more information, type \"list\" or \"help\", to quite type \"exit\" </c> \n");


    while(true) {
    $question = new Question('<p>Afrocana$ </p>', '');
    $question->setAutocompleterValues($this->shell_config);

    $response = $helper->ask($this->input, $this->output, $question);


    $this->exec_command($response);
    }

  }

  //stores the command history
  function save_history($response)
  {
    if(!empty($response) && !in_array($response, $this->shell_config))  {
      $this->shell_config[]=$response;

      $this->shell_config=array_unique($this->shell_config);

      //save autocomplete data
      array_put_contents($this->shell_config_file,$this->shell_config);
    }
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


      $this->save_history($string);

      $_cmd=toggle_os_command('clear','cls');
      system($_cmd);
      return;

      case 'list':
      case 'help':
      $this->save_history($string);

      $_cmd=afro_console_caller." ".$internal;
      system('php '.$_cmd);
      return;
      break;

      case 'bye':
      case 'exit':
      case 'quit':
      $this->save_history($string);

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
    $cmd = $this->command->getApplication()->find($command);

    $_cmd=afro_console_caller." ".$string;
    system('php '.$_cmd);

    $this->save_history($string);

    } catch(CommandNotFoundException $e)
  {
    //system($string);
    //$this->save_history($string);
    $output->writeln($command.": command not found");
  }  catch(InvalidArgumentException $e)
  {
        $output->writeLn($command.": invalid arguments exception");
  } catch(\Exception $err){
        $output->writeLn($command.": unknown error - ".$err->getMessage());
  }

  return $returnCode;
}


}
