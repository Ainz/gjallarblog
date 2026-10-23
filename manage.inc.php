<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');
check_status(ACCESS_ADMINISTRATOR);
$settings=gjallarblog_settings($conf['gjallarblog']??null);
$errors=[]; $preview=''; $new=['header'=>'','text'=>'','input_date'=>date('Y-m-d\\TH:i')]; $editingId=0; $submitted=null;
$openId=isset($_GET['edit'])?gjallarblog_id($_GET['edit']):0;
$requested=gjallarblog_id($_GET['npage']??1);
if ($_SERVER['REQUEST_METHOD']==='POST') {
    check_pwg_token();
    if (isset($_POST['upload_image'])) {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        if ($_POST['upload_image']!=='1' || isset($_POST['submit_post']) || isset($_POST['preview']) || isset($_POST['edit_post_id'])) { http_response_code(400); echo json_encode(['error'=>l10n('Invalid image upload request.')]); exit; }
        try { echo json_encode(['url'=>gjallarblog_media_url(gjallarblog_insert_media($_FILES['image']??null))],JSON_THROW_ON_ERROR); }
        catch (Throwable $error) { http_response_code(400); echo json_encode(['error'=>$error instanceof RuntimeException?$error->getMessage():l10n('The image could not be uploaded.')],JSON_INVALID_UTF8_SUBSTITUTE); }
        exit;
    }
    foreach (['submit_post','preview'] as $flag) if (isset($_POST[$flag]) && $_POST[$flag]!=='1') bad_request('Invalid journal action');
    $actions=(int)isset($_POST['del'])+(int)(isset($_POST['submit_post'])||isset($_POST['edit_post_id'])||isset($_POST['preview']));
    if ($actions!==1) bad_request('Invalid journal action');
    if (isset($_POST['del'])) {
        $id=gjallarblog_id($_POST['del']);
        if (($_POST['confirm_delete']??null)!=='1') bad_request('Deletion requires explicit confirmation');
        gjallarblog_query('DELETE FROM '.Gjallarblog_Table.' WHERE id='.$id);
        gjallarblog_cleanup_orphan_media();
        gjallarblog_flash_redirect(l10n('Entry deleted.'), Gjallarblog_Admin.'-manage&npage='.$requested.'#published-entries');
    }
    $editingId=isset($_POST['edit_post_id'])?gjallarblog_id($_POST['edit_post_id']):0;
    if ($editingId && !pwg_db_fetch_assoc(gjallarblog_query('SELECT id FROM '.Gjallarblog_Table.' WHERE id='.$editingId))) {
        page_not_found(l10n('This journal entry does not exist.'));
    }
    $titleKey=$editingId?'edit_post_header':'post_header';
    $bodyKey=$editingId?'edit_post_text':'post_textarea';
    $header=gjallarblog_plain_title(gjallarblog_input($titleKey,l10n('Title'),GJALLARBLOG_TITLE_LIMIT,$errors,true));
    if ($header==='') $errors[$titleKey]=l10n('%s is required.',l10n('Title'));
    $body=gjallarblog_input($bodyKey,l10n('Content'),GJALLARBLOG_BODY_LIMIT,$errors,true,true);
    $dateKey=$editingId?'edit_post_date':'post_date';
    $dateValue=gjallarblog_input($dateKey,l10n('Publication date and time'),16,$errors,true);
    $postDate=gjallarblog_parse_post_date($dateValue);
    if ($postDate===false && !isset($errors[$dateKey])) $errors[$dateKey]=l10n('Choose a valid date and time no later than now, using the gallery timezone.');
    $submitted=['header'=>$header,'text'=>$body,'input_date'=>$dateValue];
    if ($editingId) { $openId=$editingId; $requested=gjallarblog_entry_page($editingId,$settings['posts_per_page']); }
    else $new=$submitted;
    if (!$errors) {
        $safeBody=gjallarblog_sanitize_html($body);
        if (trim(html_entity_decode(strip_tags($safeBody),ENT_QUOTES,'UTF-8'))==='' && !preg_match('/<img\b/i',$safeBody)) {
            $errors[$bodyKey]=l10n('Content must contain text or an image after unsupported markup is removed.');
        } elseif (isset($_POST['preview'])) {
            $preview=gjallarblog_preview_document($header,$safeBody);
        } else {
            $escapedTitle=pwg_db_real_escape_string($header);
            $escapedBody=pwg_db_real_escape_string($safeBody);
            if ($editingId) {
                $escapedDate=pwg_db_real_escape_string($postDate);
                gjallarblog_query("UPDATE ".Gjallarblog_Table." SET date='$escapedDate',header='$escapedTitle',text='$escapedBody' WHERE id=$editingId");
                $id=$editingId;
            } else {
                $escapedDate=pwg_db_real_escape_string($postDate);
                gjallarblog_query("INSERT INTO ".Gjallarblog_Table." (date,header,text) VALUES ('$escapedDate','$escapedTitle','$escapedBody')",$id);
            }
            gjallarblog_cleanup_orphan_media();
            gjallarblog_flash_redirect(l10n($editingId?'Entry updated.':'Entry published.'), Gjallarblog_Admin.'-manage&npage='.gjallarblog_entry_page($id,$settings['posts_per_page']).'&edit='.$id.'#entry-'.$id);
        }
    }
    if ($errors) http_response_code(400);
}
[$count]=pwg_db_fetch_row(gjallarblog_query('SELECT COUNT(*) FROM '.Gjallarblog_Table));
$pages=max(1,(int)ceil($count/$settings['posts_per_page']));
$npage=min($pages,$requested);
$offset=($npage-1)*$settings['posts_per_page'];
$result=gjallarblog_query('SELECT * FROM '.Gjallarblog_Table.' ORDER BY date DESC,id DESC LIMIT '.$offset.','.$settings['posts_per_page']);
$rows=[];
while ($row=pwg_db_fetch_assoc($result)) {
    $row['header']=gjallarblog_plain_title($row['header']);
    if ((int)$row['id']===$editingId && $submitted!==null) $row=array_merge($row,$submitted);
    if (!isset($row['input_date'])) $row['input_date']=date('Y-m-d\\TH:i',strtotime($row['date']));
    $row['display_date']=gjallarblog_date(strtotime($row['date'])).', '.date('H:i',strtotime($row['date']));
    $row['delete_confirm']=l10n('Permanently delete “%s”? This cannot be undone.',$row['header']);
    $rows[]=$row;
}
gjallarblog_admin_assign(['GB_ERRORS'=>$errors,'GB_PREVIEW'=>$preview,'GB_NEW'=>$new,'GB_OPEN_ID'=>$openId,
    'GB_POSTS'=>$rows,'GB_PAGES'=>gjallarblog_page_links($npage,$pages),'GB_PAGE'=>$npage,'GB_PAGE_COUNT'=>$pages,
    'GB_ADMIN'=>Gjallarblog_Admin.'-manage&npage='.$npage,'GB_NEW_OPEN'=>($npage===1||(!$editingId&&$_SERVER['REQUEST_METHOD']==='POST')),
    'GB_JOURNAL'=>get_root_url().'index.php?/blog','GB_TIMEZONE'=>date_default_timezone_get()]);
$template->set_filename('gjallarblog_manage',__DIR__.'/manage.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT','gjallarblog_manage');
