<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');
$settings=$page['gjallarblog'];
[$count]=pwg_db_fetch_row(gjallarblog_query('SELECT COUNT(*) FROM '.Gjallarblog_Table));
$pages=max(1,(int)ceil($count/$settings['posts_per_page']));
$requested=(($tokens[0]??'')==='blog')?($tokens[1]??1):1;
$npage=min($pages,gjallarblog_id($requested));
$offset=($npage-1)*$settings['posts_per_page'];
$result=gjallarblog_query('SELECT id, UNIX_TIMESTAMP(date) AS date, header, text FROM '.Gjallarblog_Table.' ORDER BY date DESC, id DESC LIMIT '.$offset.','.$settings['posts_per_page']);
$rows=[];
while ($row=pwg_db_fetch_assoc($result)) {
    $row['date']=date($settings['date_format'],(int)$row['date']);
    if ($ExDesc['state']==='active') { $row['text']=get_extended_desc($row['text']); $row['header']=get_user_language_desc($row['header']); }
    $rows[]=$row;
}
$embedded=(($tokens[0]??'')!=='blog');
if (!$embedded) $page['title']=gjallarblog_escape($settings['blog_name']);
$template->assign(['row'=>$rows,'npage'=>$npage,'pages'=>$pages,'pagi'=>gjallarblog_page_links($npage,$pages),
    'show_number'=>$settings['show_number'],'show_date'=>$settings['show_date'],
    'show_on_main_page'=>$settings['show_on_main_page'],'GB_EMBEDDED'=>$embedded,
    'GB_NAME'=>$settings['blog_name'],'GB_DESCRIPTION'=>$settings['blog_description'],
    'GB_FEED'=>get_root_url().'?/blog/feed']);
$template->set_filename('gjallarblog_list',__DIR__.'/blog.tpl');
$template->func_combine_css(['path'=>Gjallarblog_PATH.'posts.css']);
$template->concat($embedded?'PLUGIN_INDEX_CONTENT_BEFORE':'PLUGIN_INDEX_CONTENT_END',$template->parse('gjallarblog_list',true));
