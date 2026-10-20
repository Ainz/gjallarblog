<?php
chdir('/var/www/html');
define('PHPWG_ROOT_PATH','./');
$_SERVER['REMOTE_ADDR']='127.0.0.1'; $_SERVER['HTTP_HOST']='127.0.0.1:8088'; $_SERVER['SCRIPT_NAME']='/index.php';
$_GET['no_photo_yet']='browse';
include 'include/common.inc.php';
conf_update_param('gallery_title',pwg_db_real_escape_string('Gjallarblog · Field notes'));
if (empty($conf['gjallarblog_banner_configured'])) conf_update_param('page_banner',pwg_db_real_escape_string('<h1>Gjallarblog</h1><p>A gallery, a journal, and a few things worth noticing.</p>'));
conf_update_param('gallery_url','http://127.0.0.1:8088');
[$demoCount]=pwg_db_fetch_row(pwg_query('SELECT COUNT(*) FROM '.$prefixeTable.'gjallarblog'));
if (empty($conf['gjallarblog_demo_dates_seeded']) && (int)$demoCount>=5) {
    $dates=['When the fjord wakes'=>'2026-10-20 07:30:00','A slower route through October'=>'2026-10-18 14:00:00',
        'After the last light'=>'2026-10-16 18:15:00','Welcome to Gjallarblog'=>'2026-10-14 09:00:00','What to try in this preview'=>'2026-10-12 09:00:00'];
    foreach($dates as $title=>$date) pwg_query("UPDATE ".$prefixeTable."gjallarblog SET date='".$date."' WHERE header='".pwg_db_real_escape_string($title)."'");
    conf_update_param('gjallarblog_demo_dates_seeded',true);
}
echo "Applied evaluation gallery identity.\n";
