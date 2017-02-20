<?php
defined('BASEPATH') or exit('No direct script access allowed');


bind('theme', function() {
  $this->assign('bodyClass','nav-md');
});


//decorator callbacks
bind('decorator', function($full_name='theme+sidebar',$decorator='sidebar',$ext_name='theme') {
  if($decorator=='sidebar') {
  }
});
