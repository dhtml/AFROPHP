<?php
namespace Console;

defined('BASEPATH') or exit('No direct script access allowed');

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\EventDispatcher\EventDispatcher;

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Exception\RuntimeException;

use Symfony\Component\Console\Event\ConsoleExceptionEvent;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Component\Console\Helper\Table;

//taking user inputs e.g. bundle name
use Symfony\Component\Console\Question\Question;


//choice questions
use Symfony\Component\Console\Question\ChoiceQuestion;



/**
* afrocana class
*
*/
class afrocana extends Command
{
    public $fx='';
    public $arguments=array();

    /**
    * initializes everywhere
    */
    public static function __initialize()
    {
      stdout("Ready");
      exit();
    }

    public function __construct($initialize=false)
    {
      if($initialize) {
        global $console_directives, $console;
        define('CONSOLE_COLOR','blue');
        //stdout($console_directives);

        $dispatcher = new EventDispatcher();
        $console = new Application(NAME,VERSION);

        $console->setAutoExit(false);

        //error_reporting(0);
        //error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

        error_reporting(E_ERROR | E_WARNING | E_PARSE);


        global $argv;
        //the name of the initiator command e.g. ./afrocana
        define('afro_console_caller',$argv[0]);


        $console->setDefaultCommand('console');

        if(is_array($console_directives) && !empty($console_directives)) {
        foreach($console_directives as $file) {
          include_once $file;
        }
        }

        //Create a new OutputFormatter
        $formatter = new OutputFormatter();
        //Change info annotation color by blue
        $formatter->setStyle('info', new OutputFormatterStyle(CONSOLE_COLOR));
        //Construct output interface with new formatter
        $output = new ConsoleOutput(OutputInterface::VERBOSITY_NORMAL, null, $formatter);


        $dispatcher->addListener(ConsoleEvents::EXCEPTION, function (ConsoleExceptionEvent $event) {
            $output = $event->getOutput();

            $command = $event->getCommand();

            //$output->writeln(sprintf('Oops, exception thrown while running command <info>%s</info>', $command->getName()));

            // get the current exit code (the exception code or the exit code set by a ConsoleEvents::TERMINATE event)
            $exitCode = $event->getExitCode();

            $exception=$event->getException();

            $output->writeln(''.$command->getName().": ".$exception->getMessage().'');
            exit();

            // change the exception to another one
            //$event->setException(new \LogicException('Caught exception', $exitCode, $event->getException()));
        });


        $dispatcher->addListener(ConsoleEvents::COMMAND, function (ConsoleCommandEvent $event) {
            // get the input instance
            $input = $event->getInput();
            //stdout($input);


            // get the output instance
            $output = $event->getOutput();

            // get the command to be executed
            $command = $event->getCommand();

            // write something about the command
            //$output->writeln(sprintf('Before running command <info>%s</info>', $command->getName()));

            // get the application
            $application = $command->getApplication();
        });
        $console->setDispatcher($dispatcher);
        $console->setCatchExceptions(false);
        $console->run(null,$output);
        exit();
      }
    }


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

    /**
    * execute the command
    *
    */
    public function exec($fx)
    {
        $this->fx=$fx;
        global $console;

        parent::__construct();
        $console->add($this);
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
        if (!empty($this->arguments)) {
            foreach ($this->arguments as $arg) {
                list($name, $mode, $description, $default) = $arg;
                parent::addArgument($name, $mode, $description, $default);
            }
        }
    }

    public function write($var)
    {
        $this->output->write($var);
    }
    public function writeln($var)
    {
        $this->output->writeln($var);
    }

    /**
    * prints a table
    *
    * @param array $headers The headers of the table
    * @param array $data The data of the table
    *
    * @return void
    */
    public function table($headers,$data)
    {
      $table = new Table($this->output);
      $table
          ->setHeaders($headers)
          ->setRows($data)
      ;
      $table->render();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $style = new OutputFormatterStyle('red', 'default', array('bold'));
        $output->getFormatter()->setStyle('c', $style);

        $style = new OutputFormatterStyle('blue', 'default', array('bold'));
        $output->getFormatter()->setStyle('p', $style);

        $style = new OutputFormatterStyle('red', 'default', array('bold'));
        $output->getFormatter()->setStyle('h', $style);

        $output->getFormatter()->setStyle('debug', $style);

        $style = new OutputFormatterStyle('default', 'default', array('bold'));
        $output->getFormatter()->setStyle('b', $style);


        $this->input=$input;
        $this->output=$output;


        if (is_callable($this->fx) || function_exists($this->fx)) {
            call_user_func_array($this->fx, array($input,$output,$this));
        } elseif (is_string($this->fx)) {
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
  public function errorText($command, $code=0)
  {
      switch ($code) {
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



/**
* afroconsole class
*
*/
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


new afrocana(true);
