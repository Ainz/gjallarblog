<?php
// Synthetic, locally generated test images. No external photographs or licenses.
$dir='/var/www/html/_data/gjallarblog-fixtures';
if (!is_dir($dir)) mkdir($dir,0755,true);
$scenes=[
 ['dawn','FJORD / DAWN',[36,67,87],[231,184,139],[45,78,86]],
 ['forest','FOREST / STILLNESS',[42,75,70],[164,181,141],[27,62,55]],
 ['afterglow','COAST / AFTERGLOW',[98,60,92],[241,163,116],[70,52,89]],
 ['blue-hour','MOUNTAINS / BLUE HOUR',[25,39,77],[130,158,184],[38,62,100]],
 ['autumn','HILLS / OCTOBER',[97,95,90],[224,185,125],[105,78,55]],
 ['night','NORTH / NIGHTFALL',[13,24,43],[62,93,114],[20,43,55]]
];
foreach($scenes as [$slug,$label,$top,$bottom,$land]) {
 $im=imagecreatetruecolor(1400,900);
 for($y=0;$y<900;$y++) {$t=$y/899;$c=[];for($j=0;$j<3;$j++)$c[$j]=(int)round($top[$j]*(1-$t)+$bottom[$j]*$t);imageline($im,0,$y,1400,$y,imagecolorallocate($im,...$c));}
 $sun=imagecolorallocate($im,245,221,176);imagefilledellipse($im,1000,280,115,115,$sun);
 for($layer=0;$layer<3;$layer++) {
  $color=imagecolorallocate($im,max(0,$land[0]-$layer*8),max(0,$land[1]-$layer*8),max(0,$land[2]-$layer*8));
  $points=[0,590+$layer*100,240,360+$layer*130,500,580+$layer*60,800,430+$layer*110,1100,540+$layer*100,1400,380+$layer*140,1400,900,0,900];
  imagefilledpolygon($im,$points,$color);
 }
 $white=imagecolorallocate($im,240,237,222); imagestring($im,5,58,805,$label,$white); imagestring($im,3,58,836,'GJALLARBLOG / SYNTHETIC EVALUATION IMAGE',$white);
 imagejpeg($im,$dir.'/'.$slug.'.jpg',90);
}
echo "Created six synthetic gallery fixtures.\n";
