<?php
namespace Console;

defined('BASEPATH') or exit('No direct script access allowed');

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

(new afrocana())
->setName("app:greet")
->setDescription('Greet someone from afrophp')
->setHelp('Greet a person from AfroPHP')
->addArgument('name', InputArgument::REQUIRED, 'Who do you want to greet?')
->addArgument('last_name', InputArgument::OPTIONAL, 'Your last name?')
->exec('\Console\greetme');

function greetme(InputInterface $input, OutputInterface $output, $cmd)
{
    $text = 'Hi '.$input->getArgument('name');

    $lastName = $input->getArgument('last_name');
    if ($lastName) {
        $text .= ' '.$lastName;
    }

    $output->writeln($text.'!');
}
