<?php
/* Smarty version 3.1.30, created on 2017-02-22 18:26:15
  from "/Users/dhtml/Sites/www/afrophp.com/sandbox/application/plugins/helloworld/views/default.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_58add7c74063c4_79844419',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'd9195b145c1d3059fcbcbf2aa12f881ada3d9b68' => 
    array (
      0 => '/Users/dhtml/Sites/www/afrophp.com/sandbox/application/plugins/helloworld/views/default.html',
      1 => 1487787434,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_58add7c74063c4_79844419 (Smarty_Internal_Template $_smarty_tpl) {
?>

        <?php echo theme_text_func(array('key'=>"base+greet"),$_smarty_tpl);?>


        <?php $_block_plugin1 = isset($_smarty_tpl->smarty->registered_plugins['block']['translate'][0]) ? $_smarty_tpl->smarty->registered_plugins['block']['translate'][0] : null;
if (!is_callable($_block_plugin1)) {
throw new SmartyException('block tag \'translate\' not callable or registered');
}
$_smarty_tpl->smarty->_cache['_tag_stack'][] = array('translate', array('from'=>"es"));
$_block_repeat1=true;
echo $_block_plugin1(array('from'=>"es"), null, $_smarty_tpl, $_block_repeat1);
while ($_block_repeat1) {
ob_start();
?>
gracias<?php $_block_repeat1=false;
echo $_block_plugin1(array('from'=>"es"), ob_get_clean(), $_smarty_tpl, $_block_repeat1);
}
array_pop($_smarty_tpl->smarty->_cache['_tag_stack']);?>


          <h2><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
</h2>
          <h1><?php echo $_smarty_tpl->tpl_vars['body']->value;?>
</h1>

          <em>&copy; 2017</em>
<?php }
}
