<?php
require __DIR__.'/version.php';
$valid=['0.01','0.02','0.09','0.10','0.11','0.100','1.00','0.03-alpha','0.03-beta','0.03-rc','0.03-alpha.1','0.03-beta.2','0.03-rc.10'];
$invalid=['0.1','0.1.0','0.1.0-alpha.1','00.03','0.003','v0.03','0.03-a.1','0.03-rc.0','0.03-alpha.','0.03-beta.01',0.03,null];
foreach($valid as $value) if(!gjallarblog_valid_release_version($value)) throw new RuntimeException('Valid version rejected');
foreach($invalid as $value) if(gjallarblog_valid_release_version($value)) throw new RuntimeException('Invalid version accepted');
echo 'PASS: '.(count($valid)+count($invalid))." version syntax cases\n";
