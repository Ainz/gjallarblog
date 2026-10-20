<?php
// Check whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Fetch the template.
global $page, $template;

include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');

$page['tab'] = isset($_GET['tab']) ? $_GET['tab'] : 'manage';

if (!in_array($page['tab'], ['manage','config'], true)) bad_request('Unknown blog tab');
check_status(ACCESS_ADMINISTRATOR);
include(Gjallarblog_PATH.$page['tab'].'.inc.php');

$template->func_combine_css(array(
        'path' => Gjallarblog_PATH.'admin.css',
        )
      );

$tabsheet = new tabsheet();
$tabsheet->add('manage', l10n('Manage'), Gjallarblog_Admin.'-manage');
$tabsheet->add('config', l10n('Configuration'), Gjallarblog_Admin.'-config');
$tabsheet->select($page['tab']);
$tabsheet->assign();
?>
