<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');
$id=gjallarblog_id($tokens[2]??null);
$row=pwg_db_fetch_assoc(gjallarblog_query('SELECT id,date AS sort_date,UNIX_TIMESTAMP(date) AS date,header,text FROM '.Gjallarblog_Table.' WHERE id='.$id));
if (!$row) { page_not_found('This journal entry does not exist.'); return; }
$sort=pwg_db_real_escape_string($row['sort_date']);
[$ahead]=pwg_db_fetch_row(gjallarblog_query("SELECT COUNT(*) FROM ".Gjallarblog_Table." WHERE date>'".$sort."' OR (date='".$sort."' AND id>".$id.')'));
$settings=$page['gjallarblog'];
$npage=1+(int)floor($ahead/$settings['posts_per_page']);
$row['date']=date($settings['date_format'],(int)$row['date']);
if ($ExDesc['state']==='active') { $row['text']=get_extended_desc($row['text']); $row['header']=get_user_language_desc($row['header']); }
$page['title']=gjallarblog_escape($row['header']);
$template->assign(['row'=>$row,'npage'=>$npage,'show_number'=>$settings['show_number'], 'show_date'=>$settings['show_date'],'show_on_main_page'=>false]);
$template->set_filename('gjallarblog_post',__DIR__.'/post.tpl');
$template->func_combine_css(['path'=>Gjallarblog_PATH.'posts.css']);
$template->concat('PLUGIN_INDEX_CONTENT_END',$template->parse('gjallarblog_post',true));
