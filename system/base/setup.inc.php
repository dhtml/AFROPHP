<?php
defined('BASEPATH') or exit('No direct script access allowed');

$error="";

if(!empty($_POST)) {
$post=$_POST;
get_config(array('dbase'=>$post));

$cfg=$this->load->_compile_db_config();

$db=new \System\Base\DHTMLPDO($cfg);

if($db->connect()) {
$tables=$db->list_tables();

if(!empty($tables)) {
$error=<<<end
<ul>
<li>The database selected is not empty</li>
<li>To start over, you must empty this database.</li>
</ul>
end;
} else {
//save config and move on
array_put_contents(
  APPPATH."config/settings.php",
  array('dbase'=>$post)
);

redirect(current_url);
}

} else {
//connection error
$err=$db->last_error();

$error=<<<end
<ul>
<li>Failed to connect to the database with the username and password you entered. Did you mistype them? The database reported:
  <code>$err</code>
</li>
</ul>
end;
}

}
