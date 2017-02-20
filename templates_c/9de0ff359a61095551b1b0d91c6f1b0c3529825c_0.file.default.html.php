<?php
/* Smarty version 3.1.30, created on 2017-02-20 06:39:47
  from "/Users/dhtml/Sites/www/afrophp.com/sandbox/plugins/welcome/views/default.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_58aa8f338ec571_87579906',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '9de0ff359a61095551b1b0d91c6f1b0c3529825c' => 
    array (
      0 => '/Users/dhtml/Sites/www/afrophp.com/sandbox/plugins/welcome/views/default.html',
      1 => 1487571998,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_58aa8f338ec571_87579906 (Smarty_Internal_Template $_smarty_tpl) {
?>

<nav class="navbar navbar-fixed-top navbar-inverse">
     <div class="container">
       <div class="navbar-header">
         <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
           <span class="sr-only">Toggle navigation</span>
           <span class="icon-bar"></span>
           <span class="icon-bar"></span>
           <span class="icon-bar"></span>
         </button>
         <a class="navbar-brand" href="#">Project name</a>
       </div>
       <div id="navbar" class="collapse navbar-collapse">
         <ul class="nav navbar-nav">
           <li class="active"><a href="#">Home</a></li>
           <li><a href="#about">About</a></li>
           <li><a href="#contact">Contact</a></li>
         </ul>
       </div><!-- /.nav-collapse -->
     </div><!-- /.container -->
   </nav><!-- /.navbar -->

   <div class="container">

     <div class="row row-offcanvas row-offcanvas-right">

       <div class="col-xs-12 col-sm-9">
         <p class="pull-right visible-xs">
           <button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">Toggle nav</button>
         </p>
         <div class="jumbotron">
           <h1>Hello, world!</h1>
           <p><?php echo $_smarty_tpl->tpl_vars['date']->value;?>
</p>
           <p>This is an example to show the potential of an offcanvas layout pattern in Bootstrap. Try some responsive-range viewport sizes to see it in action.</p>
         </div>
         <div class="row">
           <div class="col-xs-6 col-lg-4">
             <h2>Heading</h2>
             <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
             <p><a class="btn btn-default" href="#" role="button">View details &raquo;</a></p>
           </div><!--/.col-xs-6.col-lg-4-->
           <div class="col-xs-6 col-lg-4">
             <h2>Heading</h2>
             <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
             <p><a class="btn btn-default" href="#" role="button">View details &raquo;</a></p>
           </div><!--/.col-xs-6.col-lg-4-->
           <div class="col-xs-6 col-lg-4">
             <h2>Heading</h2>
             <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
             <p><a class="btn btn-default" href="#" role="button">View details &raquo;</a></p>
           </div><!--/.col-xs-6.col-lg-4-->
         </div><!--/row-->
       </div><!--/.col-xs-12.col-sm-9-->

       <div class="col-xs-6 col-sm-3 sidebar-offcanvas" id="sidebar">
         <div class="list-group">
           <a href="#" class="list-group-item active">Link</a>
           <a href="#" class="list-group-item">Link</a>
           <a href="#" class="list-group-item">Link</a>
           <a href="#" class="list-group-item">Link</a>
         </div>
       </div><!--/.sidebar-offcanvas-->
     </div><!--/row-->

     <hr>

     <footer>
       <p>&copy; 2017 Company, Inc.</p>
     </footer>

   </div><!--/.container-->
<?php }
}
