<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');
check_status(ACCESS_ADMINISTRATOR);
$settings=gjallarblog_settings($conf['gjallarblog']??null);
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    check_pwg_token();
    if (isset($_POST['submit_config']) && $_POST['submit_config']!=='1') bad_request('Invalid settings action');
    $fields=['blog_name'=>l10n('Journal name'),'blog_description'=>l10n('Description')];
    foreach ($fields as $key=>$label) {
        $value=gjallarblog_input($key,$label,255,$errors,$key==='blog_name');
        $settings[$key]=$key==='blog_name'?gjallarblog_plain_title($value):$value;
        if ($key==='blog_name' && $settings[$key]==='') $errors[$key]=l10n('%s is required.', $label);
    }
    $perPage=gjallarblog_input('posts_per_page',l10n('Entries per page'),3,$errors,true);
    if (!preg_match('/^[1-5]$/D',$perPage)) $errors['posts_per_page']=l10n('Entries per page must be between 1 and 5.');
    $settings['posts_per_page']=$perPage;
    $dateFormat=$_POST['date_format']??null;
    if (!is_string($dateFormat) || !in_array($dateFormat,['j F Y','Y-m-d','d/m/Y'],true)) $errors['date_format']=l10n('Choose one of the available date formats.');
    else $settings['date_format']=$dateFormat;
    foreach (['show_number','show_date','show_menu'] as $key) {
        if (isset($_POST[$key]) && (!is_string($_POST[$key]) || !in_array($_POST[$key],['on','1'],true))) $errors[$key]=l10n('Choose a valid checkbox value.');
        $settings[$key]=isset($_POST[$key]);
    }
    foreach (['home_display'=>'show_on_main_page','album_display'=>'show_on_category_page'] as $key=>$legacy) {
        $value=$_POST[$key]??null;
        if (!is_string($value) || !in_array($value,['hidden','before','after'],true)) $errors[$key]=l10n('Choose Hidden, Before content or After content.');
        else { $settings[$key]=$value; $settings[$legacy]=$value!=='hidden'; }
    }
    if (!$errors) {
        $settings['menu_label']=$settings['blog_name'];
        $settings['posts_per_page']=(int)$perPage;
        // ASCII JSON escapes preserve all Unicode in the core's three-byte UTF-8 table.
        conf_update_param('gjallarblog',json_encode($settings,JSON_THROW_ON_ERROR),true,'pwg_db_real_escape_string');
        gjallarblog_flash_redirect(l10n('Settings saved.'),Gjallarblog_Admin.'-config');
    }
    http_response_code(400);
}
[$postCount]=pwg_db_fetch_row(gjallarblog_query('SELECT COUNT(*) FROM '.Gjallarblog_Table));
gjallarblog_admin_assign(['GB_POST_COUNT'=>(int)$postCount,'GB_REMOVE'=>Gjallarblog_Admin.'-remove','GB_CAN_REMOVE'=>is_webmaster(), 'GB_SETTINGS'=>$settings,'GB_ERRORS'=>$errors,'GB_ADMIN'=>Gjallarblog_Admin.'-config',
    'GB_MENU_SETTINGS'=>get_root_url().'admin.php?page=menubar',
    'GB_DATE_EXAMPLE'=>gjallarblog_date(time(),$settings['date_format']), 'GB_TIMEZONE'=>date_default_timezone_get()]);
$template->set_filename('gjallarblog_config',__DIR__.'/config.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT','gjallarblog_config');
