<?php
/* Smarty version 3.1.30, created on 2017-02-22 18:02:33
  from "/Users/dhtml/Sites/www/afrophp.com/sandbox/themes/gentele/master.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_58add239a4e6b2_33465521',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '207914053da05ecac98bdc6c15f42a2a98b46904' => 
    array (
      0 => '/Users/dhtml/Sites/www/afrophp.com/sandbox/themes/gentele/master.html',
      1 => 1487777616,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_58add239a4e6b2_33465521 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!doctype html>
<html lang="<?php echo $_smarty_tpl->tpl_vars['page_lang']->value;?>
" dir="<?php echo $_smarty_tpl->tpl_vars['page_direction']->value;?>
">
<head>
  <title><?php echo $_smarty_tpl->tpl_vars['page_title']->value;?>
</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="shortcut icon" href="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
favicon.ico" type="image/vnd.microsoft.icon" />
  <?php echo $_smarty_tpl->tpl_vars['headData']->value;?>


  <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
<!--[if lt IE 9]><?php echo '<script'; ?>
 src="<?php echo $_smarty_tpl->tpl_vars['theme_url']->value;?>
js/ie8-responsive-file-warning.js"><?php echo '</script'; ?>
><![endif]-->

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
  <?php echo '<script'; ?>
 src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"><?php echo '</script'; ?>
>
  <?php echo '<script'; ?>
 src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"><?php echo '</script'; ?>
>
<![endif]-->
</head>
<body class="<?php echo $_smarty_tpl->tpl_vars['bodyClass']->value;?>
">
  <?php echo $_smarty_tpl->tpl_vars['pageBody']->value;?>

  <?php echo $_smarty_tpl->tpl_vars['footData']->value;?>

</body>
</html>
<?php }
}