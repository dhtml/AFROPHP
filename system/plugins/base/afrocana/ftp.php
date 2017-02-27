<?php
namespace Console;

defined('BASEPATH') or exit('No direct script access allowed');


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

//taking user inputs e.g. bundle name
use Symfony\Component\Console\Question\Question;


//choice questions
use Symfony\Component\Console\Question\ChoiceQuestion;


//table helper
use Symfony\Component\Console\Helper\Table;



(new afrocana())
->setName("ftp:init")
->setDescription('initialzes ftp connection')
->setHelp('It allows you to configure/reconfigure your ftp details')
->execute(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new ftp_console($cmd,$input,$output))->ftp_init();
});


(new afrocana())
->setName("ftp:test")
->setDescription('tests ftp connection')
->setHelp('It allows you to determine the validity of your connections')
->execute(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new ftp_console($cmd,$input,$output))->ftp_test();
});


(new afrocana())
->setName("ftp:status")
->setDescription('display ftp config status')
->setHelp('It allows you to determine the validity of your connections')
->execute(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new ftp_console($cmd,$input,$output))->ftp_status();
});

(new afrocana())
->setName("ftp:reset")
->setDescription('resets some ftp data')
->setHelp('You can update your various ftp data')
->execute(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new ftp_console($cmd,$input,$output))->ftp_reset();
});

(new afrocana())
->setName("ftp:chmod")
->setDescription('Changes the mode of a file remotely')
->addArgument('file', InputArgument::REQUIRED, 'The name of the remote file e.g. index.php?')
->addArgument('mode', InputArgument::REQUIRED, 'The new file mange e.g. 0755?')
->setHelp('This will change the file permissions e.g. ftp:chmod index 0755')
->execute(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new ftp_console($cmd,$input,$output))->ftp_chmod($input->getArgument('file'),$input->getArgument('mode'));;
});


(new afrocana())
->setName("ftp:commit")
->setDescription('commits changes to the remote server')
->setHelp('Saves all changes made to the remote server')
->execute(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new ftp_console($cmd,$input,$output))->ftp_commit();
});

(new afrocana())
->setName("ftp:pull")
->setDescription('Pulls all data from remote to local server')
->setHelp('Transfers all the files on remote to local server')
->execute(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new ftp_console($cmd,$input,$output))->ftp_pull();
});




class ftp_console {

  public $conn_id=false;

  public $ftp_list_in =APPPATH."config/console/ftp/in.php";
  public $ftp_list_out = APPPATH."config/console/ftp/out.php";
  public $ftp_list_conf = APPPATH."config/console/ftp/conf.php";

  public $ftp_config= array();

  public $ftp_out=array();
  public $ftp_in=array();

  public $ftp_default_config = array(
    'host'=>'localhost',
    'user'=>'user',
    'pass'=>'test',
    'port'=>'21',
    'dir'=>'/public_html',
    'local_dir'=>'/',
    'timeout'=>'90',
  );

  public function __get($key)
  {
    if(isset($this->command->$key)) {
      return $this->command->$key;
    } else {
      show_error("There is no property called: {$key}()");
    }
  }


  /**
  * __call
  *
  * reroute method calls to the framework
  *
  * @return mixed
  */
  public function __call($name, $arguments)
  {
    if(method_exists($this->command,$name)) {$result=call_user_func_array(array($this->command, $name),$arguments);}
    else {
      show_error("There is no method called: {$name}()");
    }

    return $result;
  }

public function __construct($command=null,$input=null, $output=null)
{
  if($command!=null) {
    $this->command=$command;
    $this->input=$input;
    $this->output=$output;

    //load config if it actually exists
    $this->ftp_config=array_get_contents($this->ftp_list_conf);

    get_instance()->load->helper('inflect');



    //if there is no ftp config
    if(empty($this->ftp_config)) {
      $this->ftp_config=$this->ftp_default_config;
    }
  }
}




  function __destruct() {
    if($this->conn_id) {
      ftp_close($this->conn_id);
      $this->output->writeln("<info>Ftp connection closed</info>");
    }
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


   $this->output->write("<info>Changing $target to $mode...</info>");



   if (@ftp_chmod($this->conn_id, $mode, $file) !== false) {
    $this->output->writeln("<info>done</info>");
   } else {
     $this->output->writeln("<info>failed</info>");
   }


   }


   /**
   * initialize ftp client
   *
   */
   public function ftp_init()
   {
       $helper = $this->getHelper('question');


       $this->output->writeln("<info>initializing ftp client</info>");



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

       array_put_contents($this->ftp_list_conf,$this->ftp_config);


       $this->output->writeln("<info>ftp initialization completed</info>");
   }

   /**
   * initialize ftp client
   *
   */
   public function ftp_test()
   {
      $this->output->writeln("<info>Attempting ftp connection</info>");

      if($this->getFtpConnection(true)) {
        $this->output->writeln("<info>ftp connection was successful</info>");
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

     array_put_contents($this->ftp_list_in,$this->ftp_in);

     if($synced) {
       array_put_contents($this->ftp_list_out,$this->ftp_in);
       return;
     }


   //get out files
   $this->ftp_out=array_get_contents($this->ftp_list_out);

     //lets compare with online
   $comp=$this->compare_files($this->ftp_in,$this->ftp_out);

   //clear duplicates
     $comp['upload']=array_unique($comp['upload']);
     $comp['remove']=array_unique($comp['remove']);


   if(empty($comp['upload']) && empty($comp['remove'])) {
     $this->output->writeln("<info>No changes detected</info>");
     return;
   }


   $upload_files=pluralize_if(count($comp['upload']),"file");
   $remove_files=pluralize_if(count($comp['remove']),"file");

   $rf= count($comp['remove'])==0 ? "":"-{$remove_files}";
   $this->output->writeln("<info>+{$upload_files} $rf</info>");

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

     $this->output->writeln("<info>Preparing to upload ".pluralize_if(count($cfiles),'file')."</info>");

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
           $this->output->writeln("<info>Creating $remotedir on remote server</info>");
           $this->ftp_mkdir($remotedir);
       }

       //check if file is binary
       $var=file_get_contents($file);
       $binary = (is_string($var) === true && ctype_print($var) === false);

       if (@ftp_put($this->conn_id, $remotefile, $file, $binary ? FTP_BINARY : FTP_ASCII)) {
         $rcount++;
         $this->output->writeln("<info>{$rcount}. Uploaded $remotefile on remote server</info>");

         $this->ftp_out["$file"]=sha1_file($file);

         //save uploaded file to config
         array_put_contents($this->ftp_list_out,$this->ftp_out);
       } else {
         $this->output->writeln("<info>Failed to create $remotefile on remote server</info>");
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
     $this->output->writeln("<info>Preparing to remove ".pluralize_if(count($cfiles),'file')."</info>");


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
         $this->output->writeln("<info>{$rcount}. Removed $remotefile on remote server</info>");
       } else {
         $this->output->writeln("<info>Failed to remove $remotefile on remote server</info>");
       }

       //remove file from config
       if(isset($this->ftp_out["$file"])) {unset($this->ftp_out["$file"]);

       //save uploaded file to config
       array_put_contents($this->ftp_list_out,$this->ftp_out);
       $dirlist[]=$remotedir;
     }

     //remove directories if they are empty
     $dirlist=array_unique($dirlist);
     foreach($dirlist as $dir) {
       if (@ftp_rmdir($this->conn_id, $dir)) {
        $this->output->writeln("<info>Successfully deleted $dir</info>\n");
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
     $this->output->writeln("<info>Pulling data offline</info>");

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
               $this->output->writeln("<info>Change Dir Failed: $dir</info>");
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
                 $this->output->writeln("<info>Saved $shortfile</info>");

                 //update data coming from online
                 $this->ftp_out["$localfile"]=sha1_file($file);
                 //save uploaded file to config
                 array_put_contents($this->ftp_list_out,$this->ftp_out);

               } else {
                 $this->output->writeln("<info>Error $localfile</info>");
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
     $this->output->write('<info>Reseting sync data...</info>');
     $this->ftp_commit(true);
     $this->output->writeln('<info>done</info>');
     break;
     case "2":
     $this->output->write('<info>Wiping sync data...</info>');

     array_put_contents($this->ftp_list_in);
     array_put_contents($this->ftp_list_out);

     $this->output->writeln('<info>done</info>');
     break;
     case "3":
     $this->output->write('<info>Wiping connection data...</info>');
     array_put_contents($this->ftp_list_conf);
     $this->output->writeln('<info>done</info>');
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

     $this->output->writeln("<info>Ftp Config Status:</info>");


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
   $valid_folder_list=array(APPPATH,FCPATH.'bin',FCPATH.'3rdparty',BASEPATH);

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
           $this->output->writeln("<info>Couldn't connect to $host</info>");
           return false;
         }


         // try to login
         if (@ftp_login($conn_id, $user, $pass)) {
             $this->conn_id=$conn_id;
             $this->output->writeln("<info>FTP connected to $host</info>");
             if($testmode) {return true;}
         } else {
            $this->output->writeln("<info>Couldn't connect to $host as $user</info>");
            return false;
         }

         // turn passive mode on
         ftp_pasv($conn_id, true);



         if(!$this->ftp_directory_exists($dir,false)) {
           $this->output->writeln("<info>Creating $dir on remote server</info>");
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


}
