<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');
check_status(ACCESS_ADMINISTRATOR);
if ($_SERVER['REQUEST_METHOD']==='POST') {
    check_pwg_token();
    if (isset($_POST['submit_post']) || isset($_POST['edit_post_id'])) {
        $editing=isset($_POST['edit_post_id']);
        $header=gjallarblog_post_string($editing?'edit_post_header':'post_header',1020);
        $text=gjallarblog_post_string($editing?'edit_post_text':'post_textarea',1048576);
        if (mb_strlen($header,'UTF-8')>255 || trim($header)==='') bad_request('A title of 1–255 characters is required');
        $header=pwg_db_real_escape_string($header); $text=pwg_db_real_escape_string($text);
        if ($editing) gjallarblog_query("UPDATE ".Gjallarblog_Table." SET header='".$header."',text='".$text."' WHERE id=".gjallarblog_id($_POST['edit_post_id']));
        else gjallarblog_query("INSERT INTO ".Gjallarblog_Table." (date,header,text) VALUES (NOW(),'".$header."','".$text."')");
        $page['infos'][]=l10n($editing?'post_modified':'post_is_saved');
    } elseif (isset($_POST['del'])) {
        gjallarblog_query('DELETE FROM '.Gjallarblog_Table.' WHERE id='.gjallarblog_id($_POST['del']));
        $page['infos'][]=l10n('post_is_deleted');
    }
}
$settings=gjallarblog_settings($conf['gjallarblog']??null);
[$count]=pwg_db_fetch_row(gjallarblog_query('SELECT COUNT(*) FROM '.Gjallarblog_Table));
$pages=max(1,(int)ceil($count/$settings['posts_per_page']));
$npage=min($pages,gjallarblog_id($_GET['npage']??1));
$offset=($npage-1)*$settings['posts_per_page'];
$result=gjallarblog_query('SELECT * FROM '.Gjallarblog_Table.' ORDER BY date DESC,id DESC LIMIT '.$offset.','.$settings['posts_per_page']);
$rows=[]; while ($row=pwg_db_fetch_assoc($result)) $rows[]=$row;
$template->assign(['GB_POSTS'=>$rows,'GB_PAGES'=>gjallarblog_page_links($npage,$pages),'GB_PAGE'=>$npage,
    'GB_ADMIN'=>Gjallarblog_Admin.'-manage','PWG_TOKEN'=>get_pwg_token()]);
$template->set_filename('gjallarblog_manage',__DIR__.'/manage.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT','gjallarblog_manage');
