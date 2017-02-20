<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (version_compare(PHP_VERSION, '5.3.0') <= 0) {

  if (PHP_SAPI == 'cli') {
       echo 'AFROPHP supports PHP 5.3 and above.' .
           'Please read http://afrophp.com/user_guide/';
   } else {
       echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
   <p>AFROPHP supports PHP 5.3 and above. Please read
   <a target="_blank" href="http://afrophp.com/user_guide/">
   AFROPHP User Guide</a>.
</div>
HTML;
   }
   exit(1);


}




define('time_start',microtime(true));

include dirname(__DIR__)."/base/prototype.php";
include dirname(__DIR__)."/base/singleton.php";


//load core application class
include __DIR__."/common.php";
include __DIR__."/loader.php";
include __DIR__."/exceptions.php";
include __DIR__."/afrophp.php";
