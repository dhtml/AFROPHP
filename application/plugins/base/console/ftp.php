<?php
namespace Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Console\Command\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

//taking user inputs e.g. bundle name
use Symfony\Component\Console\Question\Question;


//choice questions
use Symfony\Component\Console\Question\ChoiceQuestion;


//table helper
use Symfony\Component\Console\Helper\Table;


class FTPConsole extends Command
{

  //public $target=FCPATH;
  public $target=FCPATH."tests/";

  public $ftp_list_in =APPPATH."config/ftp/in.php";
  public $ftp_list_out = APPPATH."config/ftp/out.php";
  public $ftp_list_conf = APPPATH."config/ftp/conf.php";

  public $ftp_config= array(
    'host'=>'localhost',
    'user'=>'user', 
    'pass'=>'test',
    'port'=>'21',
    'dir'=>'/public_html',
    'timeout'=>'90',
  );


  protected function configure()
  {
      $this->setName('ftp')
       ->setDescription('Default ftp client of AFROPHP')

       ->addArgument(
        'params',
           InputArgument::IS_ARRAY,
           "Params can be any of the following:\n
           init - will initialize the ftp connection\n
           commit - will commit the changes to your server
           test - will test your ftp connection
           "
          )
          ->setHelp('FTP Console for AfroPHP');

      // ->addArgument('params', InputArgument::OPTIONAL, 'Who do you want to greet?')
       //->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
   ;
  }


  public function execute(InputInterface $input, OutputInterface $output)
  {
    $this->input=$input;$this->output=$output;
    $args = Shell::getParams($this->input);

    $action= isset($args['0']) ? $args['0'] : '';

    if(empty($action)) {$this->output->writeln(Shell::errorText('ftp',2));return 0;}

    //load config if it actually exists
    if(file_exists($this->ftp_list_conf)) {
      $this->ftp_config=include $this->ftp_list_conf;
    }


    switch($action) {
      case 'init':
      $this->ftp_init();
      break;
      case 'test':
      $this->ftp_test();
      break;
      case 'commit':
      $this->ftp_commit();
      break;
      default:

      break;
    }

    return 1;
}

/**
* initialize ftp client
*
*/
public function ftp_init()
{
    $helper = $this->getHelper('question');


    $this->output->writeln("initializing ftp client");



    $question = new Question("Please enter your ftp host? ({$this->ftp_config['host']}) ", $this->ftp_config['host']);
    $this->ftp_config['host'] = $helper->ask($this->input, $this->output, $question);

    $question = new Question("Please enter your ftp username? ({$this->ftp_config['user']}) ", $this->ftp_config['user']);
    $this->ftp_config['user'] = $helper->ask($this->input, $this->output, $question);


    $question = new Question("Please enter your ftp password? (hidden) ", $this->ftp_config['pass']);
    $this->ftp_config['pass'] = $helper->ask($this->input, $this->output, $question);


    $question = new Question("Please enter your remote path? ({$this->ftp_config['dir']}) ", $this->ftp_config['dir']);
    $this->ftp_config['dir'] = $helper->ask($this->input, $this->output, $question);



    $question = new Question("Please enter your ftp port? ({$this->ftp_config['port']}) ", $this->ftp_config['port']);
    $this->ftp_config['port'] = $helper->ask($this->input, $this->output, $question);

    $question = new Question("Please enter your ftp timeout? ({$this->ftp_config['timeout']}) ", $this->ftp_config['timeout']);
    $this->ftp_config['timeout'] = $helper->ask($this->input, $this->output, $question);

    $output = '<?' . 'php ' . 'return ' . var_export($this->ftp_config, true) . ';';

    file_force_contents($this->ftp_list_conf,$output);

    $this->output->writeln("ftp initialization completed");
}

/**
* initialize ftp client
*
*/
public function ftp_test()
{
  $this->output->writeln("Testing ftp client");

}


/**
* Commit ftp client
*
*/
public function ftp_commit()
{
  $this->output->writeln("Commiting ftp client changes");

}


/**
* Commit ftp client
*
*/
public function ftp_pull()
{
  $this->output->writeln("Pulling data offline");

}

}
