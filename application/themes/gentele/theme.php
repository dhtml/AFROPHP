<?php
defined('BASEPATH') or exit('No direct script access allowed');

//change body class
bind('theme', function() {
  $this->assign('bodyClass','nav-md');
});
