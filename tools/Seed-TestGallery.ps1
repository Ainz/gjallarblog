$ErrorActionPreference='Stop'
$root=Split-Path $PSScriptRoot -Parent
$base='http://127.0.0.1:8088'
$credentials=Get-Content (Join-Path $root '.dev/test-credentials.json') -Raw | ConvertFrom-Json
$session=[Microsoft.PowerShell.Commands.WebRequestSession]::new()
function Api([hashtable]$body) {
    $r=Invoke-RestMethod "$base/ws.php?format=json" -Method Post -Body $body -WebSession $session
    if($r.stat -ne 'ok'){throw ('Piwigo API: '+($r|ConvertTo-Json -Compress -Depth 4))}; return $r.result
}
$null=Api @{method='pwg.session.login';username=$credentials.username;password=$credentials.password}
$status=Api @{method='pwg.session.getStatus'}
$stateFile=Join-Path $root '.dev/seed-state.json'
$state=if(Test-Path $stateFile){Get-Content $stateFile -Raw|ConvertFrom-Json -AsHashtable}else{@{albums=@{};images=@{};posts=@{}}}
function Save-State {[IO.File]::WriteAllText($stateFile,($state|ConvertTo-Json -Depth 8),[Text.UTF8Encoding]::new($false))}
foreach($name in @('Northern light','Woodland walks','After hours')) {
    if(!$state.albums.ContainsKey($name)){$album=Api @{method='pwg.categories.add';name=$name;comment='Synthetic imagery for Gjallarblog evaluation.';pwg_token=$status.pwg_token};$state.albums[$name]=$album.id;Save-State}
}
$images=@(
 @{slug='dawn';title='Fjord at dawn';album='Northern light'},
 @{slug='blue-hour';title='The blue hour';album='Northern light'},
 @{slug='forest';title='A quieter path';album='Woodland walks'},
 @{slug='autumn';title='October hills';album='Woodland walks'},
 @{slug='afterglow';title='Last light on the coast';album='After hours'},
 @{slug='night';title='North after dark';album='After hours'}
)
foreach($image in $images) {
 if(!$state.images.ContainsKey($image.slug)) {
  $form=@{method='pwg.images.addSimple';'category[]'=[string]$state.albums[$image.album];name=$image.title;author='Gjallarblog demo';comment='Locally generated illustrative fixture, not a photograph.';image=Get-Item (Join-Path $root ('.dev/fixtures/'+$image.slug+'.jpg'))}
  $r=Invoke-RestMethod "$base/ws.php?format=json" -Method Post -Form $form -WebSession $session
  if($r.stat -ne 'ok'){throw ('Upload failed: '+($r|ConvertTo-Json -Compress -Depth 4))};$state.images[$image.slug]=$r.result.image_id;Save-State
 }
}
foreach($image in $images) {
 $null=Api @{method='pwg.images.setInfo';image_id=[string]$state.images[$image.slug];categories=[string]$state.albums[$image.album];multiple_value_mode='append'}
}
if(!$state.settingsInitialized) {
 $null=Invoke-WebRequest "$base/admin.php?page=plugin-gjallarblog-config" -Method Post -WebSession $session -Body @{
 pwg_token=$status.pwg_token;blog_name='Field notes';blog_description='Small stories from the north. Light, landscape, and the space in between.';posts_per_page='3';date_format='j F Y';show_on_main_page='on';show_date='on'
}
 $state.settingsInitialized=$true;Save-State
}
$posts=@(
 @{title='Welcome to Gjallarblog';body='<p>A gallery holds the pictures. A journal holds the stories around them.</p><p>This is our first working Gjallarblog preview: a small space for field notes, new albums, and the moments that deserve a few more words.</p><p>Browse the albums below, or open the journal to try pagination and individual entries.</p>'},
 @{title='When the fjord wakes';body='<img src="_data/gjallarblog-fixtures/dawn.jpg" alt="Illustrative fjord landscape at dawn"><p>Some mornings arrive quietly. A pale band of light, a dark ridge, and water that almost forgets to move.</p><p>These synthetic landscapes give us a useful way to test how images and words sit together. The <a href="index.php?/category/'+$state.albums['Northern light']+'">Northern light album</a> collects the cooler end of the day.</p>'},
 @{title='A slower route through October';body='<img src="_data/gjallarblog-fixtures/autumn.jpg" alt="Illustrative autumn hills"><p>The familiar path looks different when the season changes. Ochre on the hillside, softer light, and a reason to take the long way home.</p><blockquote>Look twice. The second glance is usually the better one.</blockquote><p>Open an entry to evaluate longer text, image proportions, and the return link.</p>'},
 @{title='After the last light';body='<img src="_data/gjallarblog-fixtures/afterglow.jpg" alt="Illustrative coastal afterglow"><p>Blue turns to violet. The horizon keeps a little warmth while everything nearby settles into shadow.</p><p>The <a href="index.php?/category/'+$state.albums['After hours']+'">After hours album</a> is a small study in that transition.</p>'},
 @{title='What to try in this preview';body='<p>This gallery is an evaluation space with disposable content.</p><ul><li>Read the journal on the home page and as a separate page.</li><li>Move between pages and open individual entries.</li><li>Browse three albums and six illustrative images.</li><li>Sign in to create, edit, or delete an entry and adjust journal settings.</li><li>Open the RSS feed.</li></ul><p>Artwork is generated locally for testing; these are not photographs.</p>'}
)
foreach($post in $posts) {
 if(!$state.posts.ContainsKey($post.title)) {
  $response=Invoke-WebRequest "$base/admin.php?page=plugin-gjallarblog-manage" -Method Post -WebSession $session -Body @{pwg_token=$status.pwg_token;submit_post='1';post_header=$post.title;post_textarea=$post.body}
  if($response.StatusCode -ne 200 -or $response.Content -match 'Fatal error|Uncaught|Warning:'){throw 'Blog sample publication failed'}
  $state.posts[$post.title]=$true;Save-State
 }
}
Write-Output 'Seeded three albums, six synthetic images, and five journal entries.'
& wsl.exe -d Debian -u root --exec docker exec gjallarblog-dev-gallery-1 php /tmp/seed-gallery-settings.php
if($LASTEXITCODE -ne 0){throw 'Final sample gallery settings failed'}
