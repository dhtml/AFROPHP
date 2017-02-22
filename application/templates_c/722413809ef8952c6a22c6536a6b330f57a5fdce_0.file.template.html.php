<?php
/* Smarty version 3.1.30, created on 2017-02-22 06:48:53
  from "/Users/dhtml/Sites/www/afrophp.com/sandbox/themes/gentele/templates/default/template.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_58ad34556975b1_85764494',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '722413809ef8952c6a22c6536a6b330f57a5fdce' => 
    array (
      0 => '/Users/dhtml/Sites/www/afrophp.com/sandbox/themes/gentele/templates/default/template.html',
      1 => 1487468652,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_58ad34556975b1_85764494 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div class="container body">


    <div class="main_container">

        <div class="col-md-3 left_col">
            <div class="left_col scroll-view">

                <div class="navbar nav_title" style="border: 0;">
                    <a href="index.html" class="site_title"><i class="fa fa-paw"></i> <span>Gentellela Alela!</span></a>
                </div>
                <div class="clearfix"></div>

                <?php echo theme_decorator_func(array('name'=>"theme+side_bar_profile.html"),$_smarty_tpl);?>




                <br />
                <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                  <div class="menu_section">
                    <?php echo theme_decorator_func(array('name'=>"theme+decorators/sidebar.php"),$_smarty_tpl);?>

                  </div>
                </div>

                <!-- sidebar menu -->
                <!-- /sidebar menu -->

                <!-- /menu footer buttons -->
                <!--
                <div class="sidebar-footer hidden-small">
                    <a data-toggle="tooltip" data-placement="top" title="Settings">
                        <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                    </a>
                    <a data-toggle="tooltip" data-placement="top" title="FullScreen">
                        <span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
                    </a>
                    <a data-toggle="tooltip" data-placement="top" title="Lock">
                        <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
                    </a>
                    <a data-toggle="tooltip" data-placement="top" title="Logout">
                        <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
                    </a>
                </div>
              -->
                <!-- /menu footer buttons -->
            </div>
        </div>

        <!-- top navigation -->
        <div class="top_nav">

            <div class="nav_menu">
                <nav class="" role="navigation">
                    <div class="nav toggle">
                        <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                    </div>

                    <ul class="nav navbar-nav navbar-right">
                        <li class="">
                            <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <img src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
images/img.jpg" alt="">John Doe
                                <span class=" fa fa-angle-down"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-usermenu animated fadeInDown pull-right">
                                <li><a href="javascript:;">  Profile</a>
                                </li>
                                <li>
                                    <a href="javascript:;">
                                        <span class="badge bg-red pull-right">50%</span>
                                        <span>Settings</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;">Help</a>
                                </li>
                                <li><a href="login.html"><i class="fa fa-sign-out pull-right"></i> Log Out</a>
                                </li>
                            </ul>
                        </li>

                        <li role="presentation" class="dropdown">
                            <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-envelope-o"></i>
                                <span class="badge bg-green">6</span>
                            </a>
                            <ul id="menu1" class="dropdown-menu list-unstyled msg_list animated fadeInDown" role="menu">
                                <li>
                                    <a>
                                        <span class="image">
                                      <img src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
images/img.jpg" alt="Profile Image" />
                                  </span>
                                        <span>
                                      <span>John Smith</span>
                                        <span class="time">3 mins ago</span>
                                        </span>
                                        <span class="message">
                                      Film festivals used to be do-or-die moments for movie makers. They were where...
                                  </span>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                        <span class="image">
                                      <img src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
images/img.jpg" alt="Profile Image" />
                                  </span>
                                        <span>
                                      <span>John Smith</span>
                                        <span class="time">3 mins ago</span>
                                        </span>
                                        <span class="message">
                                      Film festivals used to be do-or-die moments for movie makers. They were where...
                                  </span>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                        <span class="image">
                                      <img src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
images/img.jpg" alt="Profile Image" />
                                  </span>
                                        <span>
                                      <span>John Smith</span>
                                        <span class="time">3 mins ago</span>
                                        </span>
                                        <span class="message">
                                      Film festivals used to be do-or-die moments for movie makers. They were where...
                                  </span>
                                    </a>
                                </li>
                                <li>
                                    <a>
                                        <span class="image">
                                      <img src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
images/img.jpg" alt="Profile Image" />
                                  </span>
                                        <span>
                                      <span>John Smith</span>
                                        <span class="time">3 mins ago</span>
                                        </span>
                                        <span class="message">
                                      Film festivals used to be do-or-die moments for movie makers. They were where...
                                  </span>
                                    </a>
                                </li>
                                <li>
                                    <div class="text-center">
                                        <a>
                                            <strong>See All Alerts</strong>
                                            <i class="fa fa-angle-right"></i>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </nav>
            </div>

        </div>
        <!-- /top navigation -->

        <?php echo $_smarty_tpl->tpl_vars['page_content']->value;?>

    </div>

</div>

<div id="custom_notifications" class="custom-notifications dsp_none">
    <ul class="list-unstyled notifications clearfix" data-tabbed_notifications="notif-group">
    </ul>
    <div class="clearfix"></div>
    <div id="notif-group" class="tabbed_notifications"></div>
</div>
<?php }
}
