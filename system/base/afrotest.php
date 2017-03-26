<?php
namespace System\Base;

define('MODE','test');


//load afrophp index
include dirname(dirname(__DIR__))."/index.php";


use PHPUnit\Framework\TestCase;

/**
 * Singleton Pattern.
 *
 * Modern implementation.
 */
class Afrotest extends TestCase
{

  /**
  * application object
  *
  * @var application
  */
  public $application;

  /**
  * application object
  *
  * @var application
  */
  public $app;

    public function __construct()
    {
      static $init;
      if($init) {return;}
      $this->app=get_instance();
      $init=true;
    }
}
