<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CMS extends Controller {
        public function index()
        {
          set_title("Hello World");

          //stdout(get_instance());


          stdout($this->input->session('test2'));
          //$this->input->set_cookie('beer','no will do');

          $_SESSION['test2']='lovely';

          //d($this->input->cookie('beer'));
          //$this->input->cookie('beer', TRUE); // with XSS filter
          //d($this->input->method());
//die();

          stdout($this->input->post('comment',true));

print<<<end
<form method="post">
 <input type="text" name="comment" value="">
 <input type="submit" name="submit" value="Submit">
</form>
end;
exit();
        }

        public function admin()
        {

          /*
          addScript("alert('This is a serious matter');",null,'inline');
          addScript("alert('Testing the mike');",'bottom','inline');
          addScript("alert('Testing the top');",'top','inline');

          addScript("base");
          addScript("core.js");
          addScript("js/bootstrap.min.js");
          addScript("js/bootstrap.min.js",'bottom','plugin');
          addScript("js/test.js",'top','theme');
          addScript("js/forum.js",'top','forums');

          addStyle(".test {color:red;background:gray;}",null,'inline');
          addStyle(".test2 {color:red;background:green;}",'bottom','inline');
          addStyle("css/test.css");
          addStyle("css/test.css","bottom");

          addTag('<meta name="keywords" values="tony,ayo,jide">');
          addTag('<meta name="keywords" values="tony,ayo,jide">','bottom');
          */

          set_title("Hello World");
        }
        public function pages()
        {
          set_title("Hello World");
        }

}
