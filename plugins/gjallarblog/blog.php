<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');
$settings=$page['gjallarblog'];
[$count]=pwg_db_fetch_row(gjallarblog_query('SELECT COUNT(*) FROM '.Gjallarblog_Table));
$pages=max(1,(int)ceil($count/$settings['posts_per_page']));
$embedded=(($tokens[0]??'')!=='blog');
$requested=$embedded?1:($tokens[1]??1);
$npage=min($pages,gjallarblog_id($requested));
$offset=($npage-1)*$settings['posts_per_page'];
$result=gjallarblog_query('SELECT id,UNIX_TIMESTAMP(date) AS date,header,text FROM '.Gjallarblog_Table.' ORDER BY date DESC,id DESC LIMIT '.$offset.','.$settings['posts_per_page']);
$rows=[];
while ($row=pwg_db_fetch_assoc($result)) {
    $row['date']=gjallarblog_date((int)$row['date'],$settings['date_format']);
    $row['header']=gjallarblog_plain_title($row['header']);
    if ($ExDesc['state']==='active') $row['header']=get_user_language_desc($row['header']);
    $row['text']=gjallarblog_render_body($row['text']);
    $row['open_label']=l10n('Open entry: %s',$row['header']);
    $rows[]=$row;
}
if (!$embedded) {
    $page['title']=gjallarblog_escape($settings['blog_name']);
    $page['section_title']='<a href="'.gjallarblog_escape(get_gallery_home_url()).'">'.gjallarblog_escape(l10n('Home')).'</a> / '.gjallarblog_escape($settings['blog_name']);
}
$template->assign(['row'=>$rows,'npage'=>$npage,'pages'=>$pages,'pagi'=>gjallarblog_page_links($npage,$pages),
    'show_date'=>$settings['show_date'],
    'show_on_main_page'=>$settings['show_on_main_page'],'GB_EMBEDDED'=>$embedded,
    'GB_NAME'=>$settings['blog_name'],'GB_DESCRIPTION'=>$settings['blog_description']]);
$template->set_filename('gjallarblog_list',__DIR__.'/template/blog.tpl');
$template->func_combine_css(['path'=>Gjallarblog_PATH.'posts.css']);
$slot='PLUGIN_INDEX_CONTENT_END';
if ($embedded) {
    $placement=$settings[($tokens[0]??'')==='category'?'album_display':'home_display'];
    $slot=$placement==='after'?'PLUGIN_INDEX_CONTENT_AFTER':'PLUGIN_INDEX_CONTENT_BEFORE';
}
$template->concat($slot,$template->parse('gjallarblog_list',true));
