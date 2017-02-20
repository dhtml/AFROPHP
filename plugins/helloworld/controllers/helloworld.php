<?php
class HelloWorld extends Controller {
        public function index()
        {

          //loads the model with array parameters
          $this->load->model('HelloWorld_Model',['Model','Hello World']);

          $title = $this->HelloWorld_Model->getTitle();
          $body = $this->HelloWorld_Model->getBody();

          $this->assign('title',$title);
          $this->assign('body',$body);
        }


}
