<?php
// Run inside a disposable Piwigo installation. Uses the core comparator directly.
chdir('/var/www/html');
define('PHPWG_ROOT_PATH','./');
define('IN_ADMIN',true);
$_SERVER['REMOTE_ADDR']='127.0.0.1';
$_SERVER['HTTP_HOST']='127.0.0.1';
$_SERVER['SCRIPT_NAME']='/index.php';
include 'include/common.inc.php';
$checks=0;
function verify_version($ok,$label) {
  global $checks;
  if (!$ok) throw new RuntimeException($label);
  ++$checks; echo "PASS: $label\n";
}
$pairs=[
 ['0.01','0.02'],['0.02','0.09'],['0.09','0.10'],['0.10','0.11'],['0.99','0.100'],['0.100','1.00'],
 ['0.03-alpha.1','0.03-alpha.2'],['0.03-alpha.2','0.03-alpha.10'],['0.03-alpha.10','0.03-beta.1'],
 ['0.03-beta.1','0.03-rc.1'],['0.03-rc.1','0.03-rc.2'],['0.03-rc.2','0.03'],['0.03','0.04-alpha.1'],['0.05-alpha.1','0.05-beta.1'],
 ['0.05-beta.1','0.06-beta'],['0.06-beta','0.06-beta.1'],['0.1.0-alpha.1','0.03-alpha.1'],['0.1.0-alpha.2','0.03-alpha.1'],['0.1.0-alpha.2','0.06-beta.1']
];
foreach($pairs as [$a,$b]) {
 verify_version(safe_version_compare($a,$b,'<'),"$a < $b");
 verify_version(safe_version_compare($b,$a,'>='),"update completion: $b >= $a");
}
verify_version(safe_version_compare('0.01','0.1','=='),'Padding is presentation: 0.01 equals 0.1');
verify_version(safe_version_compare('0.01-alpha.1','0.1.0-alpha.1','<'),'Do not restart at 0.01: it precedes the published legacy version');
$source=file_get_contents(PHPWG_PLUGINS_PATH.'gjallarblog/main.inc.php');
preg_match('/^Version:\s*([\w.-]+)/m',$source,$match);
if(in_array('--upgrade',$argv,true)) {
 if(getenv('GJALLARBLOG_DISPOSABLE')!=='1') throw new RuntimeException('Upgrade checks require a disposable gallery');
 $target=$match[1]??'';
verify_version((bool)preg_match('/^[0-9]+\.[0-9]{2,}(?:-(?:alpha|beta|rc)(?:\.[1-9][0-9]*)?)?$/D',$target),'Core-compatible metadata parser reads exact padded version');
 function post_fingerprint() {
   $res=gjallarblog_query('SELECT id,date,header,text FROM '.Gjallarblog_Table.' ORDER BY id');
   $rows=[];while($row=pwg_db_fetch_assoc($res))$rows[]=$row;
   return hash('sha256',json_encode($rows));
 }
 $originalSettings=gjallarblog_settings($conf['gjallarblog']);
 $settings=$originalSettings;
 $settings['blog_name']="Version test 北 🌄 ' \\";
 $settings['menu_label']=$settings['blog_name'];
 conf_update_param('gjallarblog',json_encode($settings,JSON_THROW_ON_ERROR),true,'pwg_db_real_escape_string');
 gjallarblog_query("INSERT INTO ".Gjallarblog_Table." (date,header,text) VALUES (NOW(),'Version upgrade fixture','<p>Preserve this entry.</p>')",$fixtureId);
 $before=post_fingerprint();
 try {
 foreach(['0.1.0-alpha.1','0.1.0-alpha.2','0.05-beta.1','0.06-beta'] as $old) {
   pwg_query("UPDATE ".PLUGINS_TABLE." SET version='".$old."' WHERE id='gjallarblog'");
   $plugin=['id'=>'gjallarblog','version'=>$old,'state'=>'active'];
   autoupdate_plugin($plugin);
   $stored=pwg_db_fetch_assoc(pwg_query("SELECT version FROM ".PLUGINS_TABLE." WHERE id='gjallarblog'"));
   verify_version($plugin['version']===$target && $stored['version']===$target,"Core autoupdate migrates $old to $target");
   verify_version(post_fingerprint()===$before,'Upgrade preserves existing posts');
   verify_version(gjallarblog_settings($conf['gjallarblog'])===$settings,'Upgrade preserves normalized settings');
   autoupdate_plugin($plugin);
   verify_version($plugin['version']===$target && post_fingerprint()===$before,'Equal version is stable on a repeated update check');
 }
 } finally {
   gjallarblog_query('DELETE FROM '.Gjallarblog_Table.' WHERE id='.(int)$fixtureId);
   conf_update_param('gjallarblog',json_encode($originalSettings,JSON_THROW_ON_ERROR),true,'pwg_db_real_escape_string');
 }
}
echo "Total: $checks versioning checks; Piwigo ".PHPWG_VERSION.'; PHP '.PHP_VERSION."\n";
