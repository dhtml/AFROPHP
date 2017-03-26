<?php defined('BASEPATH') OR exit('No direct script access allowed');

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
            <img src="<?= site_url("assets/images/logo.png") ?>"/>            <p>Version <?= afro_version ?></p>
        </h1>
    </div>
    <div class="Form">
			<div class="Messages Errors">
        <ul>
        <li>Unable to secure connection to the database</li>
        <li><code><?= $error_message ?></code></li>
        </ul>
			</div>

    </div>
</div>
</form>		</div>
   </div>

</body>
</html>
