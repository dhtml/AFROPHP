<?php
namespace System\Base;


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

    /**
     * Make constructor private, so nobody can call "new Class".
     */
    public function __construct()
    {
      static $init;
      if($init) {return;}
      define('MODE','test');
      $app=dirname(dirname(__DIR__))."/index.php";
      include_once $app;
      $this->app=$this->application=get_instance();
      $init=true;
    }

}
