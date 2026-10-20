<?php
chdir('/var/www/html');
define('PHPWG_ROOT_PATH', './');
define('IN_ADMIN', true);
$_SERVER['REMOTE_ADDR']='127.0.0.1';
$_SERVER['HTTP_HOST']='127.0.0.1';
$_SERVER['SCRIPT_NAME']='/index.php';
$_GET['no_photo_yet']='browse';
include 'include/common.inc.php';
conf_update_param('no_photo_yet', 'false');
echo "Disabled the core empty-gallery welcome screen in this disposable test gallery.\n";
