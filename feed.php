<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');
$xmlEscape=static function($value) { return gjallarblog_xml_escape($value); };
$xml='<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel>';
$xml.='<title>'.$xmlEscape($page['gjallarblog']['blog_name']).'</title><link>'.$xmlEscape(get_absolute_root_url().'?/blog').'</link><description>'.$xmlEscape($page['gjallarblog']['blog_description']).'</description>';
$result=gjallarblog_query('SELECT id,UNIX_TIMESTAMP(date) AS date,header,text FROM '.Gjallarblog_Table.' ORDER BY date DESC,id DESC LIMIT 50');
while ($row=pwg_db_fetch_assoc($result)) {
    if ($ExDesc['state']==='active') $row['header']=get_user_language_desc($row['header']);
    $row['text']=gjallarblog_render_body($row['text']);
    $link=$xmlEscape(get_absolute_root_url().'?/blog/id/'.$row['id']);
    $xml.='<item><title>'.$xmlEscape($row['header']).'</title><link>'.$link.'</link><guid isPermaLink="true">'.$link.'</guid><pubDate>'.date(DATE_RSS,(int)$row['date']).'</pubDate><description>'.$xmlEscape($row['text']).'</description></item>';
}
header('Content-Type: application/rss+xml; charset=utf-8');
echo $xml.'</channel></rss>';
exit;
