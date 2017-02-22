<?php
/**
 * AfroPHP
 *
 *
 *
 * @package	AfroPHP
 * @author	Africoders Dev Team
 * @copyright	Copyright (c) 2017, Africoders, Inc. (http://africoders.com/)
 * @link	http://africoders.com
 */


/*
 *---------------------------------------------------------------
 * SYSTEM DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * This variable must contain the name of your "system" directory.
 * Set the path if it is not in the same directory as this file.
 */
// $system_path = 'system';
 $system_path = 'system';

/*
 *---------------------------------------------------------------
 * APPLICATION DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * directory than the default one you can set its name here. The directory
 * can also be renamed or relocated anywhere on your server. If you do,
 * use an absolute (full) server path.
 * For more info please see the user guide:
 *
 *
 */
	$application_folder = 'application';



	// Set the current directory correctly for CLI requests
	if (defined('STDIN'))
	{
		chdir(dirname(__FILE__));
	}

	if (($_temp = realpath($system_path)) !== FALSE)
	{
		$system_path = $_temp.DIRECTORY_SEPARATOR;
	}
	else
	{
		// Ensure there's a trailing slash
		$system_path = strtr(
			rtrim($system_path, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		).DIRECTORY_SEPARATOR;
	}

	// Is the system path correct?
	if ( ! is_dir($system_path))
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
		exit(3); // EXIT_CONFIG
	}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
	// The name of THIS file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

  // Path to the loading directory
  define('LPATH',dirname(__FILE__));


	// Path to the system directory
	define('BASEPATH', $system_path);

	// Path to the front controller (this file) directory
	define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);


	define('APPPATH', FCPATH.strtr(
    trim($application_folder, '/\\'),
    '/\\',
    DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
  ).DIRECTORY_SEPARATOR);

/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 */
require_once BASEPATH.'core/application.php';



/*
 * --------------------------------------------------------------------
 * LOAD THE APPLICATION IN NORMAL MODE
 * --------------------------------------------------------------------
 *
 */
Afrophp::instance()->run();
