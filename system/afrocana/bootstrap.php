<?php
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


global $console_directives, $console;

define('CONSOLE_COLOR','blue');
//stdout($console_directives);

$dispatcher = new EventDispatcher();
$console = new Application(NAME,VERSION);

$console->setAutoExit(false);

//error_reporting(0);


include __DIR__."/afrocana.php";

include __DIR__."/afroconsole.php";

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
