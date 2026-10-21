<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');
check_status(ACCESS_ADMINISTRATOR);
$settings=gjallarblog_settings($conf['gjallarblog']??null);
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    check_pwg_token();
    if (isset($_POST['submit_config']) && $_POST['submit_config']!=='1') bad_request('Invalid settings action');
    $fields=['blog_name'=>l10n('Journal title'),'menu_label'=>l10n('Menu label'),'blog_description'=>l10n('Description'),'date_format'=>l10n('Date format')];
    foreach ($fields as $key=>$label) $settings[$key]=gjallarblog_input($key,$label,$key==='date_format'?80:255,$errors,$key==='blog_name'||$key==='date_format');
    $perPage=gjallarblog_input('posts_per_page',l10n('Entries per page'),3,$errors,true);
    if (!preg_match('/^[1-9][0-9]{0,2}$/D',$perPage) || (int)$perPage>100) $errors['posts_per_page']=l10n('Entries per page must be between 1 and 100.');
    $settings['posts_per_page']=$perPage;
    foreach (['show_on_main_page','show_on_category_page','show_number','show_date'] as $key) {
        if (isset($_POST[$key]) && (!is_string($_POST[$key]) || !in_array($_POST[$key],['on','1'],true))) $errors[$key]=l10n('Choose a valid checkbox value.');
        $settings[$key]=isset($_POST[$key]);
    }
    if (!$errors) {
        if ($settings['menu_label']==='') $settings['menu_label']=$settings['blog_name'];
        $settings['posts_per_page']=(int)$perPage;
        // ASCII JSON escapes preserve all Unicode in the core's three-byte UTF-8 table.
        conf_update_param('gjallarblog',json_encode($settings,JSON_THROW_ON_ERROR),true,'pwg_db_real_escape_string');
        gjallarblog_flash_redirect(l10n('Settings saved.'),Gjallarblog_Admin.'-config');
    }
    http_response_code(400);
}
gjallarblog_admin_assign(['GB_SETTINGS'=>$settings,'GB_ERRORS'=>$errors,'GB_ADMIN'=>Gjallarblog_Admin.'-config',
    'GB_MENU_SETTINGS'=>get_root_url().'admin.php?page=menubar',
    'GB_DATE_EXAMPLE'=>gjallarblog_date(time(),$settings['date_format']), 'GB_TIMEZONE'=>date_default_timezone_get()]);
$template->set_filename('gjallarblog_config',__DIR__.'/config.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT','gjallarblog_config');
