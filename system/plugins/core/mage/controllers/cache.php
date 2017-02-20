<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cache_Cli extends Controller {

        public function clear()
        {
             $response=$this->lang->text('mage', 'cache_clear');
             $this->assign('response',$response);
        }

        public function _list()
        {
          $this->assign('response',"You have 20 items in your cache");
        }
}
