<?php
namespace Console;

defined('BASEPATH') or exit('No direct script access allowed');

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

(new afrocana())
->setName("hello:world")
->setDescription('Hello World Example in Afrocana')
->setHelp('Say Hello From Afrocana')
->execute(function(InputInterface $input, OutputInterface $output, $cmd) {
  $output->writeln("Hello World From Afrocana");
});
