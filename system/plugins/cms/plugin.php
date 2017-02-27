<?php
defined('BASEPATH') or exit('No direct script access allowed');

//trigger menu event here



//cms menu event handlers
bind('menu', function () {


  //first menu
    menu::get('cms_home')
      ->setTitle('Home')
      ->setID('20')
      ->setIcons(['fa fa-home', 'fa fa-chevron-down'])
      ->setPriority(0);

      //stdout(menu::get('cms_home')->getID());

    menu::get('cms_home_dash_board')
      ->setParent('cms_home')
      ->setTitle('dashboard')
      ->setUri('admin');


    menu::get('cms_home_dash_board-1')
            ->setParent('cms_home')
            ->setTitle('dashboard 1')
            ->setUri('admin/dashboard1');


    menu::get('cms_home_dash_board-1')
                  ->setParent('cms_home')
                  ->setTitle('dashboard 1')
                  ->setUri('admin/dashboard1');


    menu::get('cms_home_dash_board-2')
                  ->setParent('cms_home')
                  ->setTitle('dashboard 2')
                  ->setUri('admin/dashboard2');

    //second menu
    menu::get('cms_forms')
                  ->setPriority(1)
                  ->setTitle('Forms')
                  ->setIcons(['fa fa-edit', 'fa fa-chevron-down'])
                  ->setUri('admin/forms');

    menu::get('cms_forms_general')
                  ->setParent('cms_forms')
                  ->setTitle('General forms')
                  ->setUri('admin/forms/general');


    menu::get('cms_forms_basic')
                      ->setParent('cms_forms')
                      ->setTitle('Basic forms')
                      ->setUri('admin/forms/basic');



    menu::get('cms_forms_advance')
                        ->setParent('cms_forms')
                        ->setTitle('Advance forms')
                        ->setUri('admin/forms/advance');



    //third root
    menu::get('cms_ui')
                        ->setPriority(3)
                        ->setTitle('UI Elements')
                        ->setIcons(['fa fa-desktop', 'fa fa-chevron-down'])
                        ->setUri('admin/ui');

    menu::get('cms_ui_general')
                        ->setParent('cms_ui')
                        ->setTitle('General UI')
                        ->setUri('admin/ui/general');


    menu::get('cms_ui_media')
                        ->setParent('cms_ui')
                        ->setTitle('Media UI')
                        ->setUri('admin/ui/media');


    menu::get('cms_ui_typo')
                        ->setParent('cms_ui')
                        ->setTitle('Typography UI')
                        ->setUri('admin/ui/typo');


    menu::get('cms_ui_test')
                          ->setParent('cms_ui')
                          ->setTitle('Typography UI')
                          ->setUri('admin/ui/typo');
});




//->setTitle('Home');
//new Menu()->setTitle('About');

$this->router->addRoute(new Route('admin.default', 'admin', 'CMS', 'admin'));

$this->router->addRoute(new Route(array(
      'view'=>'admin.pages',
      'uri'=>'admin/pages',
      'controller'=>'CMS',
      'method'=>'pages'
    )));
