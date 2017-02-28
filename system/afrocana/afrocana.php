<?php
namespace Console;

defined('BASEPATH') or exit('No direct script access allowed');

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class afrocana extends Command
{
    public $fx='';
    public $arguments=array();

    public function __construct()
    {
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
