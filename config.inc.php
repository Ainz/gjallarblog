<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');
check_status(ACCESS_ADMINISTRATOR);
$settings=gjallarblog_settings($conf['gjallarblog']??null);
if ($_SERVER['REQUEST_METHOD']==='POST') {
    check_pwg_token();
    $perPage=gjallarblog_id($_POST['posts_per_page']??null);
    if ($perPage>100) bad_request('Page size must be 1–100');
    foreach (['blog_name','blog_description','date_format','menu_label'] as $key) $settings[$key]=gjallarblog_post_string($key,$key==='date_format'?80:255);
    foreach (['blog_name','blog_description','date_format','menu_label'] as $key) {
        if (preg_match('/[\\x{10000}-\\x{10FFFF}]/u', $settings[$key])) bad_request('Settings require characters supported by the core configuration table');
    }
    if (trim($settings['blog_name'])==='') bad_request('A journal name is required');
    $settings['posts_per_page']=$perPage;
    foreach (['show_on_main_page','show_on_category_page','show_number','show_date'] as $key) {
        if (isset($_POST[$key]) && !is_string($_POST[$key])) bad_request('Invalid checkbox');
        $settings[$key]=isset($_POST[$key]);
    }
    conf_update_param('gjallarblog',$settings,true);
    $page['infos'][]=l10n('config_is_saved');
}
$template->assign(['GB_SETTINGS'=>$settings,'PWG_TOKEN'=>get_pwg_token(),'GB_ADMIN'=>Gjallarblog_Admin.'-config']);
$template->set_filename('gjallarblog_config',__DIR__.'/config.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT','gjallarblog_config');
