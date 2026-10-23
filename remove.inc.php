<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');
check_status(ACCESS_WEBMASTER);
[$postCount]=pwg_db_fetch_row(gjallarblog_query('SELECT COUNT(*) FROM '.Gjallarblog_Table));
$mediaCount=count(gjallarblog_media_files());
$errors=[];
$mode='keep';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    check_pwg_token();
    $mode=$_POST['data_action']??null;
    if (!is_string($mode) || !in_array($mode,['keep','erase'],true)) $errors[]=l10n('Choose whether to keep or erase journal data.');
    if (($_POST['confirm_remove']??null)!=='1') $errors[]=l10n('Confirm that you want to remove the plugin.');
    if (($_POST['entry_count']??null)!==(string)$postCount) $errors[]=l10n('The entry count changed. Review the current count and confirm again.');
    if ($mode==='erase' && ($_POST['erase_name']??null)!=='Gjallarblog') $errors[]=l10n('Type Gjallarblog to confirm permanent data removal.');
    if (empty($conf['enable_extensions_install'])) $errors[]=l10n('Piwigo extension installation and deletion are disabled.');
    // Fail before core uninstalls anything when the installation is read-only.
    if (!is_writable(__DIR__) || !is_writable(dirname(__DIR__))) $errors[]=l10n('The plugin folder is read-only. Ask the server administrator to remove it. No journal data has been erased.');
    if (!$errors) {
        include_once PHPWG_ROOT_PATH.'admin/include/plugins.class.php';
        $plugins=new plugins();
        $errors=$plugins->perform_action('delete',basename(__DIR__));
        clearstatcache(true,__DIR__);
        if (!$errors && !is_dir(__DIR__)) {
            if ($mode==='erase' && !gjallarblog_remove_media()) {
                $template->assign('ADMIN_CONTENT','<h2>Gjallarblog</h2><p role="alert">'.gjallarblog_escape(l10n('The plugin folder is gone, but image cleanup could not finish. Entries and settings were kept; check the gallery data folder permissions and reinstall the plugin to retry.')).'</p><p><a href="'.gjallarblog_escape(get_root_url().'admin.php?page=plugins').'">'.gjallarblog_escape(l10n('Plugins')).'</a></p>');
                $gbRemovalComplete=true;
                http_response_code(500);
                return;
            }
            if ($mode==='erase') {
                gjallarblog_query('DROP TABLE '.Gjallarblog_Table);
                gjallarblog_query('DROP TABLE '.Gjallarblog_Media_Table);
                conf_delete_param('gjallarblog');
            }
            $message=$mode==='erase'?l10n('The plugin folder, %s journal entries, %s image files and settings have been removed.',(int)$postCount,(int)$mediaCount):l10n('The plugin folder has been removed. Journal entries, images and settings were kept for reinstalling.');
            $template->assign('ADMIN_CONTENT','<h2>Gjallarblog</h2><p role="status">'.gjallarblog_escape($message).'</p><p><a href="'.gjallarblog_escape(get_root_url().'admin.php?page=plugins').'">'.gjallarblog_escape(l10n('Plugins')).'</a></p>');
            $gbRemovalComplete=true;
            return;
        }
        // Core may have unloaded the plugin even when filesystem deletion failed.
        $template->assign('ADMIN_CONTENT','<h2>Gjallarblog</h2><p role="alert">'.gjallarblog_escape(l10n('Removal did not complete. Check the plugin folder and its permissions. Journal data has not been erased.')).'</p><p><a href="'.gjallarblog_escape(get_root_url().'admin.php?page=plugins').'">'.gjallarblog_escape(l10n('Plugins')).'</a></p>');
        $gbRemovalComplete=true;
        http_response_code(500);
        return;
    }
    http_response_code(400);
}
gjallarblog_admin_assign(['GB_POST_COUNT'=>(int)$postCount,'GB_MEDIA_COUNT'=>$mediaCount,'GB_ERRORS'=>$errors,'GB_DATA_ACTION'=>is_string($mode)?$mode:'keep','GB_ADMIN'=>Gjallarblog_Admin.'-remove','GB_SETTINGS_URL'=>Gjallarblog_Admin.'-config']);
$template->set_filename('gjallarblog_remove',__DIR__.'/remove.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT','gjallarblog_remove');
