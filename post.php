<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');
$id=gjallarblog_id($tokens[2]??null);
$row=pwg_db_fetch_assoc(gjallarblog_query('SELECT id,date,UNIX_TIMESTAMP(date) AS timestamp,header,text FROM '.Gjallarblog_Table.' WHERE id='.$id));
if (!$row) { page_not_found(l10n('This journal entry does not exist.')); return; }
$settings=$page['gjallarblog'];
$npage=gjallarblog_entry_page($id,$settings['posts_per_page']);
$dateSql=pwg_db_real_escape_string($row['date']);
$older=pwg_db_fetch_assoc(gjallarblog_query("SELECT id FROM ".Gjallarblog_Table." WHERE (date<'$dateSql' OR (date='$dateSql' AND id<$id)) ORDER BY date DESC,id DESC LIMIT 1"));
$newer=pwg_db_fetch_assoc(gjallarblog_query("SELECT id FROM ".Gjallarblog_Table." WHERE (date>'$dateSql' OR (date='$dateSql' AND id>$id)) ORDER BY date ASC,id ASC LIMIT 1"));
$row['date']=gjallarblog_date((int)$row['timestamp'],$settings['date_format']);
$row['header']=gjallarblog_plain_title($row['header']);
if ($ExDesc['state']==='active') $row['header']=get_user_language_desc($row['header']);
$row['text']=gjallarblog_render_body($row['text']);
$page['title']=gjallarblog_escape($row['header']);
$page['section_title']='<a href="'.gjallarblog_escape(get_gallery_home_url()).'">'.gjallarblog_escape(l10n('Home')).'</a> / <a href="'.gjallarblog_escape(get_root_url().'?/blog').'">'.gjallarblog_escape($settings['blog_name']).'</a>';
$template->assign(['row'=>$row,'npage'=>$npage,'older'=>$older,'newer'=>$newer,'show_date'=>$settings['show_date'],'show_on_main_page'=>false,
    'GB_BACK_LABEL'=>l10n('Back to %s',$settings['blog_name'])]);
$template->set_filename('gjallarblog_post',__DIR__.'/post.tpl');
$template->func_combine_css(['path'=>Gjallarblog_PATH.'posts.css']);
$template->concat('PLUGIN_INDEX_CONTENT_END',$template->parse('gjallarblog_post',true));
