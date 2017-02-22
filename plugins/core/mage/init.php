<?php
defined('BASEPATH') or exit('No direct script access allowed');

$this->router->addRoute(new Route('cache_clear', 'cache/clear', 'Cache_Cli', 'clear'));

$this->router->addRoute(new Route('cache_clear', 'cache/list', 'Cache_Cli', '_list'));
