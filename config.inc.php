<?php
// Check whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Fetch the template.
global $template, $conf, $page;

if ((!empty($_POST['modified'])) and ($_POST['modified']=true)) {
	$eb_conf = array(
      'blog_name' => $_POST['blog_name'],
      'posts_per_page' => $_POST['posts_per_page'],
	  'blog_description' => $_POST['blog_description'],
	  'date_format' => $_POST['date_format']
    );
	if (!empty($_POST['show_on_main_page'])) {
		$eb_conf['show_on_main_page'] = $_POST['show_on_main_page'];
		}else{
		$eb_conf['show_on_main_page'] = false;
		}
	if (!empty($_POST['show_on_category_page'])) {
		$eb_conf['show_on_category_page'] = $_POST['show_on_category_page'];
		}else{
		$eb_conf['show_on_category_page'] = false;
		}
	if (!empty($_POST['show_number'])) {
		$eb_conf['show_number'] = $_POST['show_number'];
		}else{
		$eb_conf['show_number'] = false;
		}
	if (!empty($_POST['show_date'])) {
		$eb_conf['show_date'] = $_POST['show_date'];
		}else{
		$eb_conf['show_date'] = false;
		}
	pwg_query('
		UPDATE '.CONFIG_TABLE.' 
		SET '.CONFIG_TABLE.'.value = "'.pwg_db_real_escape_string(serialize($eb_conf)).'" 
		WHERE '.CONFIG_TABLE.'.param = "evil_blog" LIMIT 1 ;
		');
	array_push($page['infos'], l10n('config_is_saved'));
	} else { $eb_conf = @unserialize($conf['evil_blog']);}
	
$template->assign('blog_name', $eb_conf['blog_name']);
$template->assign('blog_description', $eb_conf['blog_description']);
$template->assign('posts_per_page', $eb_conf['posts_per_page']);
$template->assign('show_on_main_page', $eb_conf['show_on_main_page']);
$template->assign('show_on_category_page', $eb_conf['show_on_category_page']);
$template->assign('show_number', $eb_conf['show_number']);
$template->assign('show_date', $eb_conf['show_date']);
$template->assign('date_format', $eb_conf['date_format']);

$template->set_filenames( array('plugin_admin_content' => dirname(__FILE__).'/config.tpl') );
$template->assign_var_from_handle( 'ADMIN_CONTENT', 'plugin_admin_content');

?>
