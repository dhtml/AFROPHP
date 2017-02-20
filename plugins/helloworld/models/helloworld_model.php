<?php
class HelloWorld_Model extends Model {
public $title;
public $body;

public function __construct($title,$body) {
  $this->title=$title;
  $this->body=$body;
}

//return reversed title
function getTitle()
{
 return strrev($this->title);
}

//return reversed body
function getBody()
{
  return strrev($this->body);
}


}
