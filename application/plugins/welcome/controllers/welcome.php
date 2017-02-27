<?php
class Welcome extends Controller {
        public function index()
        {
          //stdout($this->config->set_item('front_theme','bootstrap'));
          //stdout($this->config->item('front_theme'));

          $this->assign('date','Today is '.date('F j, Y h:i:s'));
        }
}
