<?php

//$this->config->set_item('front_theme','bootstrap');

$this->router->addRoute(new Route('default','', 'Welcome', 'index'));

$this->router->addRoute(new Route('','test', 'Welcome', 'test'));
