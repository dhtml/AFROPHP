<?php return array (
  'site_name' => 'AFROPHP Framework',
	'language' => 'en',
	'charset' => 'UTF-8',
	'log_threshold' => '2',
  'autohtaccess' => false,
	'profiling' => 0,

  //php server-related settings
  'display_errors' => 1,
	'error_reporting' => E_ALL,
  'default_timezone' => 'UTC',

  //url related settings
  'uri_protocol' => 'REQUEST_URI',
	'enable_query_strings' => false,
	'controller_trigger' => 'q',
  'admin_path' => 'admin',

  //theme configuration
  'theme'=>[
    'front' => 'default',
    'back' => 'gentele',
  ],

  //cache configuration
  'cache'=>[
    'time' => 0,
    'script' => '2',
    'style' => '1',
  ],

  //cookie configuration
  'cookie'=>[
    'prefix' => '',
  	'domain' => '',
  	'path' => '/',
  	'secure' => false,
  	'httponly' => false,
  ],

  //exception configuration
  'exception'=>[
    'sender_name' => 'AFROPHP',
    'sender_email' => 'exception@afrophp.com',
    'subject' => 'Error Type: {level}',
    'recipients' => ['diltony@yahoo.com','diltony@gmail.com'],
    'mailer_enabled' => false,
    'enable_stack_trace' => true,
    'show_error' => true,
  ],

  //database configuration
  'dbase'=>[
    'dsn'=>'',
    'driver' => 'mysql',
    'hostname' => 'localhost',
    'username' => 'admin',
    'password' => 'pass',
    'name' => 'afrophp',
    'port' => null,
    'char_set' => 'utf8',
    'collat' => 'utf8_general_ci',
    'prefix' => 'afro_',
    'schema'=>'public',
    'persistent'=>false,
  ],

  //session configuration
  'session'=>[
    'handler' => 'dbase',
  	'match_ip' => false,
  	'match_fingerprint' => false,
  	'match_token' => false,
  	'table' => 'session',
  	'name' => 'afro_session',
  	'id' => null,
  	'cookie_path' => null,
  	'cookie_secure' => null,
  	'cookie_regenerate' => 300,
  	'expiration' => 7200,
  	'gc_probability' => 100,
  ],

  //email settings
  'email'=>[
    'smtp_host' => 'smtp.gmail.com',
  	'smtp_username' => 'diltony@gmail.com',
  	'smtp_password' => 'xxx',
  	'smtp_port' => 465,
  	'smtp_secure' => 'ssl',
  	'smtp_enabled' => 0,
  	'send_enabled' => 1,
  ],

);
