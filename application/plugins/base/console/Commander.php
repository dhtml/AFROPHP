<?php
namespace Console;


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


class Commander extends Command
{
  protected function configure()
  {
    $this
    // the name of the command (the part after "bin/console")
    ->setName('cmd')

    // the short description shown while running "php bin/console list"
    ->setDescription('Show afrophp console.')

    // the full command description shown when running the command with
    // the "--help" option
    ->setHelp('An interactive console...');
  }


  public function execute(InputInterface $input, OutputInterface $output)
  {
     $this->input=$input;
     $this->output=$output;

      $helper = $this->getHelper('question');

      $response='';


      $output->writeln("Welcome to the AfroPHP Command Line Interface (AFRO CLI).");
      $output->writeln("For more information, type \"help\" or \"bye\"  \n");

      $bundles = array('ftp init');

      while($response!='bye') {
      $question = new Question('Afrophp:bin console$ ', '');
      $question->setAutocompleterValues($bundles);

      $response = $helper->ask($input, $output, $question);

      if(!empty($response) && !in_array($response, $bundles))  {
        $bundles[]=$response;
      }



      $raw=$response;
      $e=explode(' ',$response);
      $command=$e[0];
      unset($e[0]);
      $params=(array) $e;

      $params=array_map('trim',$params);


      switch($command) {
      case 'bye':
      case 'exit':
      case 'quit':
      return;
      case 'ls':
      case 'cls':
      case 'clear':
      ob_start();
      system($raw);
      $response=ob_get_contents();
      ob_end_clean();
      break;

      default:

      //search for commands
      $method='console_'.$command;

      if(method_exists($this,$method)) {
        $response=call_user_func_array(array($this, $method), $params);
      } else {
        $response="$command: command not found";
      }

      }


        if(!empty($response)) {
          $output->writeln($response);
        }
      }
  }

  /**
  *
  * ftp init
  * ftp test
  * ftp commit
  */
  function console_ftp($action='init',$opr='') {
    $helper = $this->getHelper('question');

    $input=$this->input;
    $output=$this->output;


    $target=FCPATH;
    $target=FCPATH."tests/";

    $ftp_list_in =APPPATH."config/ftp.in.php";
    $ftp_list_out = APPPATH."config/ftp.out.php";
    $ftp_list_conf = APPPATH."config/ftp.conf.php";

    $ftp_config= array(
      'host'=>'localhost',
      'user'=>'user',
      'pass'=>'test',
      'port'=>'21',
      'dir'=>'/public_html',
      'timeout'=>'90',
    );


    //load config if it actually exists
    if(file_exists($ftp_list_conf)) {
      $ftp_config=include $ftp_list_conf;
    }



    if($action=="test") {
      $result=$this->getFtpConnection($ftp_config);
      return;
    } else  if($action=="init") {
      $question = new Question("Please enter your ftp host? ({$ftp_config['host']}) ", $ftp_config['host']);
      $ftp_config['host'] = $helper->ask($input, $output, $question);

      $question = new Question("Please enter your ftp username? ({$ftp_config['user']}) ", $ftp_config['user']);
      $ftp_config['user'] = $helper->ask($input, $output, $question);


      $question = new Question("Please enter your ftp password? (hidden) ", $ftp_config['pass']);
      $ftp_config['pass'] = $helper->ask($input, $output, $question);


      $question = new Question("Please enter your remote path? ({$ftp_config['dir']}) ", $ftp_config['dir']);
      $ftp_config['dir'] = $helper->ask($input, $output, $question);



      $question = new Question("Please enter your ftp port? ({$ftp_config['port']}) ", $ftp_config['port']);
      $ftp_config['port'] = $helper->ask($input, $output, $question);

      $question = new Question("Please enter your ftp timeout? ({$ftp_config['timeout']}) ", $ftp_config['timeout']);
      $ftp_config['timeout'] = $helper->ask($input, $output, $question);

      $output = '<?' . 'php ' . 'return ' . var_export($ftp_config, true) . ';';

      file_put_contents($ftp_list_conf,$output);

      return "ftp initialization completed";

    } else if($action=="commit") {
    $files=browse($target,array('/is','/sd','/ss'),'*.*');

    $data=array(); $count=0;
    foreach($files as $file) {
      switch($file) {
        case $ftp_list_in:
        case $ftp_list_out:
        case $ftp_list_conf:
        break;
        default:
        $count++;
        $data["$file"]=sha1_file($file);
      }
    }

    $cfiles=$ftp_in=$data; //all files we want to upload

    $output = '<?' . 'php ' . 'return ' . var_export($data, true) . ';';

    file_put_contents($ftp_list_in,$output);

    //generate stops here

    $this->output->writeln("Total files on local server is $count");


    //connect to ftp or return
    if(!$this->getFtpConnection($ftp_config)) {return;}


    //uploaded files
    $ftp_out=array();

    if(file_exists($ftp_list_out)) {
      $ftp_out=include $ftp_list_out;
    }


    $comp=$this->compare_files($ftp_in,$ftp_out);
    stdout($comp);
    die();


    //lets go and start commiting
    $dpath=rtrim($ftp_config['dir'],'/').'/';

    $dirlist=array();

    $rcount=0;
    foreach($cfiles as $file=>$hash) {
      $remotefile= $dpath . str_replace(FCPATH,'',$file);
      $remotedir=pathinfo($remotefile,PATHINFO_DIRNAME);

      //$this->output->writeln($remotefile.' => '.$remotedir);

      //create directory if not exist
      if(!in_array($remotedir, $dirlist) && !$this->ftp_directory_exists($remotedir,false))  {
          $this->output->writeln("Creating $remotedir on remote server");
          $this->ftp_mkdir($remotedir);
      }

      //check if file is binary
      $var=file_get_contents($file);
      $binary = (is_string($var) === true && ctype_print($var) === false);

      if (ftp_put($this->conn_id, $remotefile, $file, $binary ? FTP_BINARY : FTP_ASCII)) {
        $rcount++;
        $this->output->writeln("Uploaded $remotefile on remote server");

        //save uploaded file to config
        $ftp_out["$file"]=sha1_file($file);
        $output = '<?' . 'php ' . 'return ' . var_export($ftp_out, true) . ';';
        file_put_contents($ftp_list_out,$output);
        //finished updating out config


      } else {
        $this->output->writeln("Failed to create $remotefile on remote server");
      }

      $dirlist[]=$remotedir;
    }

    $this->output->writeln("Total files commited to remote server is $rcount");

    return;
    }


    //$f=include $ftp_list_in;

    //stdout($f);




    return "ftp done";
  }

  function getFtpConnection($uri)
  {
      extract($uri);

      //$host='test.localhost.com';


      if(!($conn_id = @ftp_connect($host,$port,$timeout))) {
        $this->output->writeln("Couldn't connect to $host");
        return false;
      }


      // try to login
      if (@ftp_login($conn_id, $user, $pass)) {
          $this->output->writeln("Ftp connected to $host as $user");
      } else {
         $this->output->writeln("Couldn't connect to $host as $user");
      }

      // turn passive mode on
      ftp_pasv($conn_id, true);


      $this->conn_id=$conn_id;

      if(!$this->ftp_directory_exists($dir,false)) {
        $this->output->writeln("Creating $dir on remote server");
        $this->ftp_mkdir($dir);
      }


      return $conn_id;
  }

  /**
  * checks if directory exists
  *
  * @param string  $dir  The name of the directory
  * @param boolean  $reset  Should directory be reset back to origin?
  *
  * @return boolean
  */
  function ftp_directory_exists($dir,$reset=true)
  {
    // Get the current working directory
    $origin = ftp_pwd($this->conn_id);

    // Attempt to change directory, suppress errors
    if (@ftp_chdir($this->conn_id, $dir))
    {
        // If the directory exists, set back to origin
        if($reset) {ftp_chdir($this->conn_id, $origin);}
        return true;
    }

    // Directory does not exist
    return false;
  }



  /**
  * creates directory recursively
  *
  * @param string  $path  The name of the directory
  *
  * @return boolean
  */
  function ftp_mkdir($path)
  {
   $dir=explode("/", $path);
   $path="";
   $ret = true;

   for ($i=0;$i<count($dir);$i++)
   {
       $path.="/".$dir[$i];
       //echo "$path\n";
       if(!@ftp_chdir($this->conn_id,$path)){
         @ftp_chdir($this->conn_id,"/");
         if(!@ftp_mkdir($this->conn_id,$path)){
          $ret=false;
          break;
         }
       }
   }
   return $ret;
  }


  /**
  * compares 2 repositories and returns the files that are different
  *
  *  @param  array  $local   the local repository
  *  @param  array  $local   the local repository
  *
  *
  * 1 - any files removed from local is deleted from remote
  * 2 - any files added or more modified is uploaded to remote
  *
  * returns a response that contains 2 arrays
  */
  function compare_files($local,$remote) {
  $response=array(
  'upload'=>array(),
  'remove'=>array(),
  );

  //detect files out of sync only i.e reupload these ones
  $result = array_keys(array_diff($local, $remote));
  $response['upload']=array_merge($response['upload'],$result);

  //upload these new files
  $result = array_diff(array_keys($local), array_keys($remote));
  $response['upload']=array_merge($response['upload'],$result);

  //remove these files
  $result = array_diff(array_keys($remote), array_keys($local));
  $response['remove']=array_merge($response['remove'],$result);

  return $response;
  }

}
