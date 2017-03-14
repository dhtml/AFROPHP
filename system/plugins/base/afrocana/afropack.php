<?php
namespace Console;

defined('BASEPATH') or exit('No direct script access allowed');

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;



(new afrocana())
->setName("make:language")
->setDescription('Creates a language file')
->setHelp('This will modify your plugin')
->addArgument('plugin', InputArgument::REQUIRED, 'The name of the plugin?')
->addArgument('code', InputArgument::REQUIRED, 'The code of the language e.g. en?')
->addArgument('name', InputArgument::REQUIRED, 'The name of the file e.g. default?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->make_language($input->getArgument('plugin'),$input->getArgument('code'),$input->getArgument('name'));
});

(new afrocana())
->setName("list:menu")
->setDescription('Lists all available menus')
->setHelp('This will only affect enabled plugins')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->list_menus();
});

(new afrocana())
->setName("smarty:clear")
->setDescription('Clears smartys application/templates_c')
->setHelp('This will delete the templates_c files')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->smarty_clear();
});


(new afrocana())
->setName("menu:list")
->setDescription('Lists all available menus')
->setHelp('This will only affect enabled plugins')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->list_menus();
});


(new afrocana())
->setName("menu:show")
->setDescription('Shows details of a menu')
->setHelp('This will dump the menu items')
->addArgument('uri', InputArgument::REQUIRED, 'The uri of the menu e.g. / ?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->menu_show($input->getArgument('uri'));
});


(new afrocana())
->setName("theme:list")
->setDescription('Lists all available themes')
->setHelp('This will show some information on your current themes')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->list_themes();
});

(new afrocana())
->setName("theme:show")
->setDescription('Shows details of a theme')
->setHelp('This will dump the theme info')
->addArgument('name', InputArgument::REQUIRED, 'The name of the theme e.g. default ?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->theme_show($input->getArgument('name'));
});


(new afrocana())
->setName("make:template")
->setDescription('Creates a template')
->setHelp('This will modify your theme')
->addArgument('theme', InputArgument::REQUIRED, 'The name of the theme?')
->addArgument('template', InputArgument::REQUIRED, 'The name of the template?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->make_template($input->getArgument('theme'),$input->getArgument('template'));
});

(new afrocana())
->setName("make:theme")
->setDescription('Creates a theme')
->setHelp('This will modify your themes folder')
->addArgument('name', InputArgument::REQUIRED, 'The name of the theme?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->make_theme($input->getArgument('name'));
});


(new afrocana())
->setName("history:list")
->setDescription('Lists commands history')
->setHelp('The list of commands used recently in afrocana console')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afroconsole($cmd,$input,$output))->history_list();
});

(new afrocana())
->setName("history:clear")
->setDescription('Clears commands history')
->setHelp('Clears the list of commands used recently in afrocana console')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afroconsole($cmd,$input,$output))->history_clear();
});


(new afrocana())
->setName("console")
->setDescription('Starts the afrocana command line interactive interface')
->setHelp('This interface shortens you command, you can do stuffs like ftp:status')
->setHidden(true)
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afroconsole($cmd,$input,$output))->interactive();
});



(new afrocana())
->setName("list:commands")
->setDescription('Lists all commands')
->setHelp('This will list all available command locations')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->list_commands();
});

(new afrocana())
->setName("route:list")
->setDescription('Lists all registered routes')
->setHelp('This will list all available routes')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->list_routes();
});


(new afrocana())
->setName("route:show")
->setDescription('Shows details a uri')
->setHelp('This will display routing info for a URI')
->addArgument('uri', InputArgument::REQUIRED, 'The uri of the route?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->route_show($input->getArgument('uri'));
});


(new afrocana())
->setName("plugin:list")
->setDescription('Lists all plugins')
->setHelp('This will list all plugin locations')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->plugin_list();
});

(new afrocana())
->setName("plugin:show")
->setDescription('Shows details a plugin')
->setHelp('This will display the config of the plugin')
->addArgument('name', InputArgument::REQUIRED, 'The name of the plugin?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->plugin_show($input->getArgument('name'));
});

(new afrocana())
->setName("plugin:enable")
->setDescription('Enables a plugin')
->setHelp('This will change the status of the plugin')
->addArgument('name', InputArgument::REQUIRED, 'The name of the plugin?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->plugin_enable($input->getArgument('name'));
});

(new afrocana())
->setName("plugin:disable")
->setDescription('Disables a plugin')
->setHelp('This will change the status of the plugin')
->addArgument('name', InputArgument::REQUIRED, 'The name of the plugin?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->plugin_disable($input->getArgument('name'));
});

(new afrocana())
->setName("make:controller")
->setDescription('Creates a controller')
->setHelp('This will modify your plugin')
->addArgument('plugin', InputArgument::REQUIRED, 'The name of the plugin?')
->addArgument('controller', InputArgument::REQUIRED, 'The name of the controller?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->make_controller($input->getArgument('plugin'),$input->getArgument('controller'));
});

(new afrocana())
->setName("make:model")
->setDescription('Creates a model')
->setHelp('This will modify your plugin')
->addArgument('plugin', InputArgument::REQUIRED, 'The name of the plugin?')
->addArgument('model', InputArgument::REQUIRED, 'The name of the model?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->make_model($input->getArgument('plugin'),$input->getArgument('model'));
});


(new afrocana())
->setName("make:view")
->setDescription('Creates a view')
->setHelp('This will modify your plugin')
->addArgument('plugin', InputArgument::REQUIRED, 'The name of the plugin?')
->addArgument('view', InputArgument::REQUIRED, 'The name of the view?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->make_view($input->getArgument('plugin'),$input->getArgument('view'));
});

(new afrocana())
->setName("make:bool")
->setDescription('Creates a class in bool folder')
->setHelp('This will modify your plugin')
->addArgument('plugin', InputArgument::REQUIRED, 'The name of the plugin?')
->addArgument('file', InputArgument::REQUIRED, 'The name of the file?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->make_bool($input->getArgument('plugin'),$input->getArgument('file'));
});

(new afrocana())
->setName("make:library")
->setDescription('Creates a library')
->setHelp('This will modify your plugin')
->addArgument('plugin', InputArgument::REQUIRED, 'The name of the plugin?')
->addArgument('library', InputArgument::REQUIRED, 'The name of the library?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->make_library($input->getArgument('plugin'),$input->getArgument('library'));
});

(new afrocana())
->setName("make:command")
->setDescription('Creates a command')
->setHelp('This will modify your plugin')
->addArgument('plugin', InputArgument::REQUIRED, 'The name of the plugin?')
->addArgument('afrocana', InputArgument::REQUIRED, 'The name of the command?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->make_command($input->getArgument('plugin'),$input->getArgument('afrocana'));
});


(new afrocana())
->setName("make:plugin")
->setDescription('Creates a new plugin')
->setHelp('This will create a new plugin')
->addArgument('name', InputArgument::REQUIRED, 'The name of the plugin?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->make_plugin($input->getArgument('name'));
});

(new afrocana())
->setName("plugin:create")
->setDescription('Creates a new plugin')
->setHelp('This will create a new plugin')
->addArgument('name', InputArgument::REQUIRED, 'The name of the plugin?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->make_plugin($input->getArgument('name'));
});

(new afrocana())
->setName("cache:clear")
->setDescription('Flush the application cache')
->setHelp('This will empty the application/cache folder')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->cache_clear();
});

(new afrocana())
->setName("cache:flush")
->setDescription('Flush the application cache')
->setHelp('This will empty the application/cache folder')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->cache_clear();
});


(new afrocana())
->setName("logs:flush")
->setDescription('Flush the application log')
->setHelp('This will empty the application/logs/errors.log file')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->logs_clear();
});

(new afrocana())
->setName("logs:clear")
->setDescription('Flush the application log')
->setHelp('This will empty the application/logs/errors.log file')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->logs_clear();
});

(new afrocana())
->setName("log:show")
->setDescription('Shows the application log')
->setHelp('This will display the application/logs/errors.log file')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->logs_show();
});

(new afrocana())
->setName("logs:show")
->setDescription('Shows the application log')
->setHelp('This will display the application/logs/errors.log file')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->logs_show();
});


(new afrocana())
->setName("test:email")
->setDescription('Test email sending')
->setHelp('This will send an email to specified address')
->addArgument('email', InputArgument::REQUIRED, 'The email address to send to?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->test_email($input->getArgument('email'));
});


(new afrocana())
->setName("cache:forget")
->setDescription('Remove an item from the cache')
->setHelp('This will delete the entry from application/cache folder')
->addArgument('name', InputArgument::REQUIRED, 'The name of the cache item?')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->cache_forget($input->getArgument('name'));
});


(new afrocana())
->setName("env:init")
->setDescription('initializes your environment variable')
->setHelp('This will capture the url of your application')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->env_init();
});

(new afrocana())
->setName("env:hta")
->setDescription('creates htaccess in the root of your installation')
->setHelp('This will create/recreate the .htaccess')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->env_hta();
});

(new afrocana())
->setName("composer")
->setDescription('Accepts composer commands for afrophp')
->setHelp('This command targets your third party folder')
->addArgument(
        'params',
        InputArgument::IS_ARRAY | InputArgument::REQUIRED,
        'This allows you to run regular composer commands e.g. ./afrocana composer require package'
    )
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->composer();
});



(new afrocana())
->setName("ftp:init")
->setDescription('initialzes ftp connection')
->setHelp('It allows you to configure/reconfigure your ftp details')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->ftp_init();
});


(new afrocana())
->setName("ftp:test")
->setDescription('tests ftp connection')
->setHelp('It allows you to determine the validity of your connections')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->ftp_test();
});


(new afrocana())
->setName("ftp:status")
->setDescription('display ftp config status')
->setHelp('It allows you to determine the validity of your connections')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->ftp_status();
});

(new afrocana())
->setName("ftp:reset")
->setDescription('resets some ftp data')
->setHelp('You can update your various ftp data')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->ftp_reset();
});

(new afrocana())
->setName("ftp:chmod")
->setDescription('Changes the mode of a file remotely')
->addArgument('file', InputArgument::REQUIRED, 'The name of the remote file e.g. index.php?')
->addArgument('mode', InputArgument::REQUIRED, 'The new file mange e.g. 0755?')
->setHelp('This will change the file permissions e.g. ftp:chmod index 0755')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->ftp_chmod($input->getArgument('file'),$input->getArgument('mode'));;
});


(new afrocana())
->setName("ftp:commit")
->setDescription('commits changes to the remote server')
->setHelp('Saves all changes made to the remote server')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->ftp_commit();
});

(new afrocana())
->setName("ftp:pull")
->setDescription('Pulls all data from remote to local server')
->setHelp('Transfers all the files on remote to local server')
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->ftp_pull();
});


(new afrocana())
->setName("shell")
->setDescription('Accepts operating commands from afrophp')
->setHelp('This command targets your root folder')
->addArgument(
        'params',
        InputArgument::IS_ARRAY | InputArgument::REQUIRED,
        'This allows you to run os commands e.g. ./afrocana shell ls'
    )
->exec(function(InputInterface $input, OutputInterface $output, $cmd) {
  (new afropack($cmd,$input,$output))->shell_exec();
});
