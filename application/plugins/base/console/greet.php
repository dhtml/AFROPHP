<?php
namespace Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GreetingsConsole extends Command
{
  protected function configure()
  {
      $this->setName('greet')
       ->setDescription('Greet someone from afrophp')

       ->addArgument(
        'params',
           InputArgument::IS_ARRAY,
          "
          greet tony - greets a person\n
          greet tony ayo jide - greets more than one person\n
          "
          )
          // the "--help" option
          ->setHelp('Greet a person from AfroPHP');
  }


  public function execute(InputInterface $input, OutputInterface $output)
  {
    $args = Shell::getParams($input);
    $person=implode(' ',$args);

    $output->writeln('greetings '.$person);
    return;
  }
}
