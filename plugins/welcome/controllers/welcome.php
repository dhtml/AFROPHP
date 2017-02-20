<?php
class Welcome extends Controller {
        public function index()
        {
          $this->assign('date','Today is '.date('F j, Y h:i:s'));
        }
}
