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



  public $conn_id=false;

  public $ftp_list_in =APPPATH."config/console/ftp/in.php";
  public $ftp_list_out = APPPATH."config/console/ftp/out.php";
  public $ftp_list_conf = APPPATH."config/console/ftp/conf.php";

  public $ftp_config= array();


  public $ftp_default_config = array(
    'host'=>'localhost',
    'user'=>'user',
    'pass'=>'test',
    'port'=>'21',
    'dir'=>'/public_html',
    'local_dir'=>'/',
    'timeout'=>'90',
  );


  function __destruct() {
    if($this->conn_id) {
      ftp_close($this->conn_id);
      $this->output->writeln("Ftp connection closed");
    }
   }

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
           chmod application/cache 0755
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
    $this->args=$args;

    $action= isset($args['0']) ? $args['0'] : '';

    if(empty($action)) {$this->output->writeln(Shell::errorText('ftp',2));return 0;}

    //load config if it actually exists
    $this->ftp_config=Shell::load_config($this->ftp_list_conf);

    get_instance()->load->helper('inflect');


    //if there is no ftp config
    if(empty($this->ftp_config) && $action!='init') {
      $this->ftp_config=$this->ftp_default_config;
      $this->ftp_init();
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
      case 'pull':
      $this->ftp_pull();
      break;
      case 'status':
      $this->ftp_status();
      break;
      case 'reset':
      $this->ftp_reset();
      break;
      case 'chmod':
      $this->ftp_chmod($this->param("1","index.php"),$this->param("2","0755"));
      break;
      default:
      stdout($this->getDefinition());
      die('x');
      $this->output->writeln(Shell::errorText('ftp',2));
      return 1;
      break;
    }

    return 1;
}

/**
* A good example is shown below:
* ftp chmod application/cache 0755
*
*/
public function ftp_chmod($file='index.php',$mode='0755')
{
  $this->getFtpConnection();
  if(!$this->conn_id) {return;}

$target= $this->remote_dir().$file;


$this->output->write("Changing $target to $mode...");



if (@ftp_chmod($this->conn_id, $mode, $file) !== false) {
 $this->output->writeln("done");
} else {
  $this->output->writeln("failed");
}


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
    $question->setHidden(true);


    $this->ftp_config['pass'] = $helper->ask($this->input, $this->output, $question);


    //$this=>getFtpConnection(true);

    $question = new Question("Please enter your ftp port? ({$this->ftp_config['port']}) ", $this->ftp_config['port']);
    $this->ftp_config['port'] = $helper->ask($this->input, $this->output, $question);

    $question = new Question("Please enter your ftp timeout? ({$this->ftp_config['timeout']}) ", $this->ftp_config['timeout']);
    $this->ftp_config['timeout'] = $helper->ask($this->input, $this->output, $question);


    $question = new Question("Please enter your remote path? ({$this->ftp_config['dir']}) ", $this->ftp_config['dir']);
    $this->ftp_config['dir'] = $helper->ask($this->input, $this->output, $question);


    $question = new Question("Please enter your local path? ({$this->ftp_config['local_dir']}) ", $this->ftp_config['local_dir']);
    $this->ftp_config['local_dir'] = $helper->ask($this->input, $this->output, $question);

    Shell::save_config($this->ftp_list_conf,$this->ftp_config);


    $this->output->writeln("ftp initialization completed");
}

/**
* initialize ftp client
*
*/
public function ftp_test()
{
   $this->output->writeln("Attempting ftp connection");

   if($this->getFtpConnection(true)) {
     $this->output->writeln("ftp connection was successful");
   }
}


/**
* Commit ftp client
*
* @param boolean $synced  Should the local and remote cache be synched
*
*/
public function ftp_commit($synced=false)
{
  $base=browse($this->local_dir(),array('/sd','/ss'));

  $files=browse($this->local_dir(),array('/is','/sd','/ss'));

  $files=$this->filter_files($files);

  $files=array_merge($base,$files);

  //$files=browse($this->local_dir(),array('/is','/sd','/ss'),'*.*');

  //populate/repopulate the ftp_in file
  $this->ftp_in=array(); $count=0;
  foreach($files as $file) {
      $count++;
      $this->ftp_in["$file"]=sha1_file($file);
  }

  Shell::save_config($this->ftp_list_in,$this->ftp_in);

  if($synced) {
    Shell::save_config($this->ftp_list_out,$this->ftp_in);
    return;
  }


//get out files
$this->ftp_out=Shell::load_config($this->ftp_list_out);

//stdout($this->ftp_in);
//stdout($this->ftp_out);
  //lets compare with online
$comp=$this->compare_files($this->ftp_in,$this->ftp_out);
//Shell::dump_files($comp,true);

//exit();

//clear duplicates
  $comp['upload']=array_unique($comp['upload']);
  $comp['remove']=array_unique($comp['remove']);


if(empty($comp['upload']) && empty($comp['remove'])) {
  $this->output->writeln("No changes detected");
  return;
}


$upload_files=pluralize_if(count($comp['upload']),"file");
$remove_files=pluralize_if(count($comp['remove']),"file");

$this->output->writeln("+{$upload_files} -{$remove_files}");

//secure ftp connection
$this->getFtpConnection();
if(!$this->conn_id) {return;}


$this->mass_upload($comp['upload']);
$this->mass_delete($comp['remove']);
}

/**
* mass upload files
*
*/
public function mass_upload($cfiles)
{
  if(count($cfiles)==0) {return;}

  $this->output->writeln("Preparing to upload ".pluralize_if(count($cfiles),'file'));

  //lets go and start commiting
  $dpath=rtrim($this->ftp_config['dir'],'/').'/'; //destination path online


  $dirlist=array();

  $rcount=0;
  foreach($cfiles as $file) {
    $hash=sha1_file($file);
    $remotefile= $dpath . str_replace(FCPATH,'',$file);
    $remotedir=pathinfo($remotefile,PATHINFO_DIRNAME);

    //$this->output->writeln($remotefile.' => '.$remotedir);
    //continue;

    //create directory if not exist
    if(!in_array($remotedir, $dirlist) && !@$this->ftp_directory_exists($remotedir,false))  {
        $this->output->writeln("Creating $remotedir on remote server");
        $this->ftp_mkdir($remotedir);
    }

    //check if file is binary
    $var=file_get_contents($file);
    $binary = (is_string($var) === true && ctype_print($var) === false);

    if (@ftp_put($this->conn_id, $remotefile, $file, $binary ? FTP_BINARY : FTP_ASCII)) {
      $rcount++;
      $this->output->writeln("{$rcount}. Uploaded $remotefile on remote server");

      $this->ftp_out["$file"]=sha1_file($file);

      //save uploaded file to config
      Shell::save_config($this->ftp_list_out,$this->ftp_out);
    } else {
      $this->output->writeln("Failed to create $remotefile on remote server");
    }

    $dirlist[]=$remotedir;
  }

}


/**
* mass delete files
*
*/
public function mass_delete($cfiles)
{
  if(count($cfiles)==0) {return;}
  $this->output->writeln("Preparing to remove ".pluralize_if(count($cfiles),'file'));


  //lets go and start commiting
  $dpath=rtrim($this->ftp_config['dir'],'/').'/'; //destination path online



  $dirlist=array();

  $rcount=0;
  foreach($cfiles as $file) {
    $remotefile= $dpath . str_replace(FCPATH,'',$file);
    $remotedir=pathinfo($remotefile,PATHINFO_DIRNAME);

    //$this->output->writeln($remotefile.' => '.$remotedir);
    //continue;
    // try to delete $file
    if (@ftp_delete($this->conn_id, $remotefile)) {
      $rcount++;
      $this->output->writeln("{$rcount}. Removed $remotefile on remote server");
    } else {
      $this->output->writeln("Failed to remove $remotefile on remote server");
    }

    //remove file from config
    if(isset($this->ftp_out["$file"])) {unset($this->ftp_out["$file"]);

    //save uploaded file to config
    Shell::save_config($this->ftp_list_out,$this->ftp_out);
    $dirlist[]=$remotedir;
  }

  //remove directories if they are empty
  $dirlist=array_unique($dirlist);
  foreach($dirlist as $dir) {
    if (@ftp_rmdir($this->conn_id, $dir)) {
     $this->output->writeln("Successfully deleted $dir\n");
   }
  }

}

}


/**
* Commit ftp client
*
*/
public function ftp_pull()
{
  $this->output->writeln("Pulling data offline");

  if(!file_exists($this->local_dir())) {
    mkdir($this->local_dir(),0777,true);
  }


//$this->ftp_sync("/sandbox/tests");

$localdir=$this->local_dir();
$remotedir=$this->ftp_config['dir'];
$subpath=str_replace(FCPATH,'',$localdir);

if($subpath!='') {$remotedir=rtrim($remotedir,'/').'/'.ltrim($subpath,'/');}

$this->_remotedir=$remotedir;
$this->_localdir=$localdir;

$this->getFtpConnection();
if(!$this->conn_id) {return;}


@chdir ($localdir);

$this->ftp_sync($remotedir);
}

/**
* sync an entire ftp path locally
*
* @dir the starting remote directory
*/
function ftp_sync ($dir) {
    global $conn_id;

    if ($dir != ".") {
        if (ftp_chdir($this->conn_id, $dir) == false) {
            $this->output->writeln("Change Dir Failed: $dir");
            return;
        }

        if (!(is_dir($dir)))
              @mkdir($dir,0777,true);
              @chdir ($dir);
          }

    $contents = ftp_nlist($this->conn_id, ".");
    foreach ($contents as $file) {

        if ($file == '.' || $file == '..')
        {
          continue;
        }
        if (@ftp_chdir($this->conn_id, $file)) {
            @ftp_chdir ($this->conn_id, "..");

            $this->ftp_sync ($file);
        }
        else {
          //grab file
          $localfile=$this->_localdir. trim($dir,'/') . '/' . $file;

          $shortfile=str_replace(FCPATH,'',$localfile);

            if(ftp_get($this->conn_id, $file, $file, FTP_BINARY)) {
              $this->output->writeln("Saved $shortfile");

              //update data coming from online
              $this->ftp_out["$localfile"]=sha1_file($file);
              //save uploaded file to config
              Shell::save_config($this->ftp_list_out,$this->ftp_out);

            } else {
              $this->output->writeln("Error $localfile");
            }
        }
    }

    ftp_chdir ($this->conn_id, "..");
    chdir ("..");

}


/**
* Reset data
*
*/
public function ftp_reset()
{
  $helper = $this->getHelper('question');

  $options=array(
  '1'=>'Reset sync data to current',
  '2'=>'Wipe sync sync data',
  '3'=>'Wipe connection data',
  '0'=>'Quit ftp reset menu');


  $question = new ChoiceQuestion("Afro FTP Reset Options:",$options,0);
  $question->setErrorMessage('Option %s is invalid.');
  $opt = $helper->ask($this->input, $this->output, $question);

  $response=array_search($opt, $options);

  switch($response) {
  case "0":
  return;
  break;
  case "1":
  $this->output->write('Reseting sync data...');
  $this->ftp_commit(true);
  $this->output->writeln('done');
  break;
  case "2":
  $this->output->write('Wiping sync data...');

  Shell::wipe_config($this->ftp_list_in);
  Shell::wipe_config($this->ftp_list_out);

  $this->output->writeln('done');
  break;
  case "3":
  $this->output->write('Wiping connection data...');
  Shell::wipe_config($this->ftp_list_conf);
  $this->output->writeln('done');
  break;
  }

  return;
}



/**
* Show status
*
*/
public function ftp_status()
{
  $data=$this->ftp_config;

  $data['pass']=str_repeat('*',strlen($data['pass']));

  $data['*local_path'] = $this->local_dir();

  $this->output->writeln("Ftp Config Status:");

  $tab=array();

  foreach($data as $key=>$value) {
    $tab[]=array($key,$value);
  }

  $table = new Table($this->output);
  $table
      ->setHeaders(array('Name', 'Value'))
      ->setRows($tab)
  ;
  $table->render();

  //$this->output->writeln("Pulling data offline");
}


/**
* retrieves the local directory
* you local directory should be something like:
* /
* system
* system/core
*
*/
public function local_dir()
{
  $ldir=$this->ftp_config['local_dir'];

  switch($ldir) {
    case FCPATH:
    case '/':
    case '':
    $ldir=FCPATH;
    break;
    default:
    $ldir=FCPATH.ltrim($ldir,'/');
    break;
  }

  return $ldir;
}

/**
* retrieves the remote directory
*/
public function remote_dir()
{
  return '/'.trim($this->ftp_config['dir'],'/').'/';
}

//filter files
public function filter_files($files) {
  $result=array();


  foreach ($files as $file) {
    if($this->ftp_ignore($file)) {continue;}
    $result[]=$file;
  }
  return $result;
}


/**
* Should this file be excluded?
*
* @param string $file The name of a file
*
* @return boolean
*/
public function ftp_ignore($file)
{

//list of files to ignore
$invalid_file_list=array('.ds_store','.log','.zip');

//list of folders to accept
$valid_folder_list=array(APPPATH,FCPATH.'bin',BASEPATH);

//list of folders to ignore
$invalid_folder_list=array(APPPATH.'config/console',APPPATH.'templates_c',APPPATH.'cache',APPPATH.'logs');

//exclude the ftp config directory
$dir=pathinfo($file,PATHINFO_DIRNAME);

//ignore folders/subfolders that match
foreach ($invalid_folder_list as $item) {
  if(strpos($file,$item)!==false) {return true;}
}


//ignore files/extensions that match
foreach ($invalid_file_list as $item) {
  if(strpos($file,$item)!==false) {return true;}
}

//if any matches, then return false, as it is valid
foreach ($valid_folder_list as $item) {
  if(strpos($file,$item)!==false) {return false;}
}


return true; //file is truly invalid
}



/**
* gets ftp connection using current configuration
*/
  function getFtpConnection($testmode=false)
  {
      extract($this->ftp_config);

      //$host='test.localhost.com';


      if(!($conn_id = @ftp_connect($host,$port,$timeout))) {
        $this->output->writeln("Couldn't connect to $host");
        return false;
      }


      // try to login
      if (@ftp_login($conn_id, $user, $pass)) {
          $this->conn_id=$conn_id;
          $this->output->writeln("FTP connected to $host");
          if($testmode) {return true;}
      } else {
         $this->output->writeln("Couldn't connect to $host as $user");
         return false;
      }

      // turn passive mode on
      ftp_pasv($conn_id, true);



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
    if(!$this->conn_id) {return false;}
    // Get the current working directory
    $origin = @ftp_pwd($this->conn_id);

    // Attempt to change directory, suppress errors
    if (@ftp_chdir($this->conn_id, $dir))
    {
        // If the directory exists, set back to origin
        if($reset) {@ftp_chdir($this->conn_id, $origin);}
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

/**
* This allows you to fetch an argument
*
*
*/
public function param($key,$default=null)
{
return isset($this->args[$key]) ? $this->args[$key] : $default;
}

}
