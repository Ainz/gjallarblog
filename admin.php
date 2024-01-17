<?php
// Chech whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Fetch the template.
global $page, $template;

include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');

$page['tab'] = isset($_GET['tab']) ? $_GET['tab'] : 'manage';

include(Evil_Blog_PATH.$page['tab'].'.inc.php');

$template->func_combine_css(array(
        'path' => Evil_Blog_PATH.'admin.css',
        )
      );

$tabsheet = new tabsheet();
$tabsheet->add('manage', l10n('Manage'), Evil_Blog_Admin.'-manage');
$tabsheet->add('config', l10n('Configuration'), Evil_Blog_Admin.'-config');
$tabsheet->select($page['tab']);
$tabsheet->assign();
?>
