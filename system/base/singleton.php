<?php
namespace System\Base;

/**
 * Singleton Pattern.
 *
 * Modern implementation.
 */
class Singleton extends \System\Base\Prototype
{


    /**
     * Make constructor private, so nobody can call "new Class".
     */
    public function __construct()
    {
    }

    /**
     * Make clone magic method private, so nobody can clone instance.
     */
    private function __clone()
    {
    }

    /**
     * Make sleep magic method private, so nobody can serialize instance.
     */
    private function __sleep()
    {
    }

    /**
     * Make wakeup magic method private, so nobody can unserialize instance.
     */
    private function __wakeup()
    {
    }

}
