<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
* catch php exceptions and displays error page
* the behaviour of this error page depends on your configurations
*
* @return void
*/
function exception_error_handler($errno, $errstr, $errfile, $errline ) {

  $report="$errstr in $errfile on line $errline with code $errno";

  $exception=new ErrorException($errstr, $errno, 0, $errfile, $errline);



  $stacktrace=array(); $html_report=""; $cli_report="";


  if (config_item('exception_show_error',false,true)) {
    $_class=get_class($exception);

    $html_report.="<p>Type: $_class</p>\n";
    $html_report.="<p>Message: $errline</p>\n";
    $html_report.="<p>Filename: $errfile</p>\n";
    $html_report.="<p>Line number: $errline</p><br/>\n";


    $cli_report.="\nType: $_class\n";
    $cli_report.="Message: $errline\n";
    $cli_report.="Filename: $errfile\n";
    $cli_report.="Line number: $errline\n\n";
  }


    if (config_item('exception_enable_stack_trace',false,true)) {
      $html_report.="<p>Backtrace:</p>\n";
      $cli_report.="Backtrace:\n";
      foreach ($exception->getTrace() as $error) {
        if (isset($error['file']) && isset($error['line']) && isset($error['function']) ) {
          $stacktrace[]=$error;

          $html_report.="<p style=\"margin-left:10px\">\n";
          $html_report.="File: {$error['file']} <br />\n";
          $html_report.="Line: {$error['line']} <br />\n";
          $html_report.="Function: {$error['function']} <br />\n";
          //$html_report.="Trace: {$error['trace']} \n";
          $html_report.="</p>";

          $cli_report.="File: {$error['file']} \n";
          $cli_report.="Line: {$error['line']} \n";
          $cli_report.="Function: {$error['function']} \n";
          //$cli_report.="Trace: {$error['trace']} \n\n";
        }
      }
  }


if(!empty($html_report)) {
  log_message('error', "$cli_report");
  mail_message('error', "$html_report");
} else {
  log_message('error',$report);
  mail_message('error',$report);
}

  $data = array(
          'trace' => is_cli() ? $cli_report : $html_report,
  );


  if (PHP_SAPI === 'cli') {$view="errors/cli/error_exception";} else {$view="errors/html/error_exception";}


  get_instance()->load->view("$view",$data);
  exit();
}

/**
* catch fatal errors
*
*/
function exception_fatal_handler() {
  $errfile = "unknown file";
  $errstr  = "shutdown";
  $errno   = E_CORE_ERROR;
  $errline = 0;



  $error = error_get_last();

  if( $error !== NULL) {
    $errno   = $error["type"];
    $errfile = $error["file"];
    $errline = $error["line"];
    $errstr  = $error["message"];

    exception_error_handler($errno, $errstr, $errfile, $errline );
  }
}


set_error_handler("exception_error_handler");

register_shutdown_function( "exception_fatal_handler" );
