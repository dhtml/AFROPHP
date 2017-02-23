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
       ->setDescription('Greet someone')

       ->addArgument(
        'params',
           InputArgument::IS_ARRAY,
          "
          ftp init - will initialize the ftp connection\n
          ftp commit - will commit the changes to your server
          ftp test - will test your ftp connection 
          "
          )
          // the "--help" option
          ->setHelp('FTP Console for AfroPHP');
  }


  public function execute(InputInterface $input, OutputInterface $output)
  {
    $args = Shell::getParams($input);

    var_dump($args);

    $output->writeln('greeting person');
    return;
    /*
    $args = $input->getArgument('params'); $params='';
    if (count($args) > 0) {
      $params = ' '.implode(', ', $args);
    }
    $output->writeln($params);
    exit();
    */

    $name = $input->getArgument('params');
          if ($name) {
              $text = 'Hello '.$name;
          } else {
              $text = 'Hello';
          }

          /*

          if ($input->getOption('yell')) {
              $text = strtoupper($text);
          }
          */

          $output->writeln($text);
    }
}
