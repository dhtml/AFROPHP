<?php defined('BASEPATH') OR exit('No direct script access allowed');

include BASEPATH . 'base/setup.inc.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <title><?= config_item('site_name') ?></title>
  <link rel="shortcut icon" href="<?= asset_url ?>images/favicon.ico" type="image/vnd.microsoft.icon" />
  <link rel="stylesheet" href="<?= asset_url ?>css/setup.css"/>
</head>
<body id="dashboard_setup_index" class="Dashboard Setup index ">
   <div id="Frame">
		<div id="Content">
			<form method="post" action="">
        <div>
          <div class="Title">
        <h1>
            <img src="<?= site_url("assets/images/logo.png") ?>"/>            <p>Version <?= afro_version ?> Installer</p>
        </h1>
    </div>
    <div class="Form">
      <?php if (!empty($error)) { ?>
			<div class="Messages Errors">
        <?= $error ?>
			</div>
      <?php } ?>

      <ul>
			<li class="Warning">
						<div><h1>
						MySQL Database Configuration</h1>
						<p style="margin-bottom:0;">Please create the database first before filling the details below.</p>
					</div>
						</li>

            <li>
                <label for="Form_Database-dot-Name">Database Name</label>
<input type="text" name="database" value="<?= config_item('dbase_database') ?>" class="InputBox" required/>            </li>

            <li>
                <label for="Form_Database-dot-User">Database Username</label>
<input type="text" name="username" value="<?= config_item('dbase_username') ?>" class="InputBox" required/>            </li>
            <li>
                <label for="Form_Database-dot-Password">Database Password</label>
<input type="text" name="password" value="<?= config_item('dbase_password') ?>" class="InputBox" />            </li>

<li>
    <label for="Form_Database-dot-Host">Database Host</label>
<input type="text" name="hostname" value="<?= config_item('dbase_hostname') ?>" class="InputBox" required/>
</li>


<li>
    <label for="Form_Database-dot-Host">Database Port</label>
<input type="text" name="port" value="<?= config_item('dbase_port') ?>" class="InputBox"/>
</li>

<li>
    <label for="Form_Database-dot-Host">Table Prefix</label>
<input type="text" name="prefix" value="<?= config_item('dbase_prefix') ?>" class="InputBox"/>
</li>

        </ul>
        <div class="Button">
            <input type="submit" value="Continue &rarr;" class="Button" />
        </div>
    </div>
</div>
</form>		</div>
   </div>

</body>
</html>
