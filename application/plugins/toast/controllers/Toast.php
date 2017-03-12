<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Myclass {
        public $title = 'My Title';
        public $name = 'My Name';
        public $date = 'My Date';
}

class User {

        public $id;
        public $email;
        public $username;

        protected $last_login;

        public function last_login($format)
        {
                return $this->last_login->format($format);
        }

        public function __set($name, $value)
        {
                if ($name === 'last_login')
                {
                        $this->last_login = DateTime::createFromFormat('U', $value);
                }
        }

        public function __get($name)
        {
                if (isset($this->$name))
                {
                        return $this->$name;
                }
        }
}


class Toast extends Controller {

  public function test()
  {
    $this->load->library("session");

    //var_dump($this->session->flashdata("item"));
    //var_dump($this->session->flashdata());

    stdout($this->session->flashdata());
    stdout($_SESSION);

    $this->session->keep_flashdata('item');

    $this->session->keep_flashdata(array('item2','item3'));


    //writeln("Test");
  }


    public function index()
    {
      writeln("Toast Index");

      //stdout($this->feed_model->dlookup("text"));


      $f=$this->load->model('feed_model');

      stdout($f->dlookup("text"));

      //stdout($f->count_all());

      //stdout($this->feed_model->dlookup("text"));

      //$this->load->model(array("user_model","feed_model"));
    }
}
