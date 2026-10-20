<?php
// Apply local demo branding through Piwigo/Modus configuration, not theme source edits.
chdir('/var/www/html');
define('PHPWG_ROOT_PATH','./');
$_SERVER['REMOTE_ADDR']='127.0.0.1'; $_SERVER['HTTP_HOST']='127.0.0.1:8088'; $_SERVER['SCRIPT_NAME']='/index.php';
$_GET['no_photo_yet']='browse';
include 'include/common.inc.php';
require_once 'themes/modus/functions.inc.php';
$current=$conf['modus_theme']??[];
if (is_string($current)) $current=@unserialize($current,['allowed_classes'=>false]);
if (!is_array($current)) $current=[];
$backup=['page_banner'=>$conf['page_banner']??'', 'modus_theme'=>$current];
file_put_contents('/tmp/gjallarblog-banner-before.json',json_encode($backup,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
$settings=array_merge(modus_get_default_config(),$current);
$settings['display_page_banner']=true;
$markup=file_get_contents('/tmp/gjallarblog-modus-banner.html');
if ($markup===false) throw new RuntimeException('Missing banner markup');
conf_update_param('page_banner',pwg_db_real_escape_string($markup));
conf_update_param('modus_theme',$settings,true);
conf_update_param('gjallarblog_banner_configured',true);
echo "Piwigo page banner configured; Modus display_page_banner enabled; other theme options retained.\n";
