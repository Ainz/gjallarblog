<?php
// Check whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Fetch the template.
global $page, $template;

include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');

$page['tab'] = isset($_GET['tab']) ? $_GET['tab'] : 'manage';

if (!in_array($page['tab'], ['manage','config','remove'], true)) bad_request('Unknown blog tab');
check_status(ACCESS_ADMINISTRATOR);
include(Gjallarblog_PATH.$page['tab'].'.inc.php');
if (!empty($gbRemovalComplete)) return;
$template->func_combine_script(['id'=>'gjallarblog-editor', 'load'=>'footer', 'path'=>Gjallarblog_PATH.'editor.js']);

$template->func_combine_css(array(
        'path' => Gjallarblog_PATH.'admin.css',
        )
      );

$tabsheet = new tabsheet();
$tabsheet->add('manage', l10n('Entries'), Gjallarblog_Admin.'-manage');
$tabsheet->add('config', l10n('Settings'), Gjallarblog_Admin.'-config');
$tabsheet->select($page['tab']==='remove'?'config':$page['tab']);
$tabsheet->assign();
?>
