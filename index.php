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

/**
* Start the application
*/
require_once __DIR__.'/system/core/afrophp.php';
