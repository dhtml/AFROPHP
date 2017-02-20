<?php
defined('BASEPATH') or exit('No direct script access allowed');

//get the navigation bar
$navigation=get_instance()->navigation->compile();

//process the whole thing to html
$menus=Array();
$submenus=Array();

foreach($navigation as $menu)
{
if($menu['parent']==null) {$menus[]=$menu;} else {$submenus[]=$menu;}
}

$menus=array_multisort_field($menus,'priority');

$response="<ul class=\"nav side-menu\">;\n";

foreach($menus as $menu)
{
extract($menu);
$response.="<li><a><i class=\"{$icons[0]}\"></i> $title <span class=\"{$icons[1]}\"></span></a>\n";

//find the submenus for this menu
$subs=Array();

//get subs
foreach($submenus as $menu) {
if($menu['parent']!=$key) {continue;}
$subs[]=$menu;
}

//if there be submenus
if(!empty($subs)) {

$subs=array_multisort_field($subs,'priority');

$response.="<ul class=\"nav child_menu\" style=\"display: none\">\n";

foreach($subs as $smenu) {
$uri=site_url($smenu['uri']);
$title=$smenu['title'];
$response.="<li><a href=\"$uri\">$title</a></li>\n";
}


$response.="</ul>\n";
}

$response.="</li>\n";
}


$response.="</ul>\n";
