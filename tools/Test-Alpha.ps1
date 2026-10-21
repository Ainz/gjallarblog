param([Parameter(Mandatory)][int]$Port,[Parameter(Mandatory)][string]$StateDirectory)
$ErrorActionPreference='Stop'
if($Port -notin @(8089,8090,8091,8092,8093,8094)){throw 'This suite only mutates the disposable alpha galleries.'}
$base="http://127.0.0.1:$Port"
$credentials=Get-Content (Join-Path $StateDirectory 'test-credentials.json') -Raw|ConvertFrom-Json
$session=[Microsoft.PowerShell.Commands.WebRequestSession]::new()
$results=[Collections.Generic.List[string]]::new()
function Api([hashtable]$body){$r=Invoke-RestMethod "$base/ws.php?format=json" -Method Post -Body $body -WebSession $session;if($r.stat -ne 'ok'){throw ($r|ConvertTo-Json -Depth 5)};return $r.result}
function Assert($condition,[string]$label){if(!$condition){throw "FAIL: $label"};$results.Add($label);Write-Output "PASS: $label"}
function Page([string]$path,$body=$null,$auth=$false){$args=@{Uri="$base/$path";SkipHttpErrorCheck=$true};if($auth){$args.WebSession=$session};if($null -ne $body){$args.Method='Post';$args.Body=$body};Invoke-WebRequest @args}
$null=Api @{method='pwg.session.login';username=$credentials.username;password=$credentials.password}
$status=Api @{method='pwg.session.getStatus'}
$initial=[xml](Page '?/blog/feed').Content
Assert ($initial.SelectNodes('/rss/channel/item').Count -eq 0) 'Disposable gallery starts empty'
foreach($path in @('','?/blog','?/blog/999999','?/blog/feed')){
 $r=Page $path;Assert ($r.StatusCode -eq 200 -and $r.Content -notmatch 'Warning:|Fatal error|Uncaught|Deprecated:') "Clean empty public view: $path"
}
foreach($path in @('?/blog/id/1%20OR%201=1','?/blog/abc','?/blog/id/0','?/blog/id/2147483648')){Assert ((Page $path).StatusCode -eq 400) "Reject malformed route: $path"}
Assert ((Page '?/blog/id/999999').StatusCode -eq 404) 'Missing post is 404'
foreach($tab in @('manage','config')){$r=Page "admin.php?page=plugin-gjallarblog-$tab" $null $true;Assert ($r.StatusCode -eq 200 -and $r.Content -notmatch 'Warning:|Fatal error|Uncaught|Deprecated:') "Clean admin: $tab"}
$manage='admin.php?page=plugin-gjallarblog-manage'
$config='admin.php?page=plugin-gjallarblog-config'
Assert ((Page $manage @{submit_post=1;post_header='Unauthorized';post_textarea='x'}).Content -notmatch 'Post is saved') 'Guest mutation cannot reach editor'
$r=Page $manage @{submit_post=1;post_header='No token';post_textarea='x'} $true
Assert ($r.StatusCode -eq 400) 'Missing CSRF token rejected'
$r=Page $manage @{pwg_token='invalid';submit_post=1;post_header='Bad token';post_textarea='x'} $true
Assert ($r.StatusCode -in @(400,401,403)) 'Incorrect CSRF token rejected'
foreach($fields in @(@{del='1 OR 1=1'},@{'del[]'='1'},@{submit_post=1;'post_header[]'='x';post_textarea='x'},@{submit_post=1;post_header=('x'*256);post_textarea='x'})){
 $fields.pwg_token=$status.pwg_token;Assert ((Page $manage $fields $true).StatusCode -eq 400) 'Reject malformed administrative input'
}
$r=Page $config @{pwg_token=$status.pwg_token;posts_per_page=0} $true;Assert ($r.StatusCode -eq 400) 'Reject zero page size'
$settings=@{pwg_token=$status.pwg_token;posts_per_page=3;blog_name='Field notes';blog_description='Alpha compatibility';date_format='j F Y';menu_label='Journal';show_on_main_page='on';show_date='on'}
Assert ((Page $config $settings $true).StatusCode -eq 200) 'Save settings and custom menu label'
$invalidSettings=$settings.Clone();$invalidSettings.blog_name='Emoji 🌄';Assert ((Page $config $invalidSettings $true).StatusCode -eq 200) 'Store configuration Unicode through ASCII JSON';$null=Page $config $settings $true
$readerName='alpha-reader-'+[Guid]::NewGuid().ToString('N').Substring(0,12)
$readerPassword=[Guid]::NewGuid().ToString('N')
$readerResult=Api @{method='pwg.users.add';username=$readerName;password=$readerPassword;password_confirm=$readerPassword;email='reader@example.test';pwg_token=$status.pwg_token}
$readerId=@($readerResult.users)[0].id
try {
 $readerSession=[Microsoft.PowerShell.Commands.WebRequestSession]::new()
 $null=Invoke-RestMethod "$base/ws.php?format=json" -Method Post -WebSession $readerSession -Body @{method='pwg.session.login';username=$readerName;password=$readerPassword}
 $readerStatus=(Invoke-RestMethod "$base/ws.php?format=json" -Method Post -WebSession $readerSession -Body @{method='pwg.session.getStatus'}).result
 Assert ($readerStatus.status -eq 'normal') 'Permission test account is an ordinary user'
 foreach($target in @($manage,$config)){
  $r=Invoke-WebRequest "$base/$target" -Method Post -WebSession $readerSession -SkipHttpErrorCheck -Body @{pwg_token=$readerStatus.pwg_token;submit_post=1;post_header='Forbidden reader post';post_textarea='x';posts_per_page=1}
  Assert ($r.StatusCode -in @(401,403)) 'Ordinary user with valid token cannot administer the journal'
 }
} finally {
 if($readerId){$null=Api @{method='pwg.users.delete';'user_id[]'=$readerId;pwg_token=$status.pwg_token}}
}
$ids=[Collections.Generic.List[string]]::new()
try {
 foreach($n in 1..5){
  $title="Alpha $n <entry> & apostrophe's — 北 🌄"
  $r=Page $manage @{pwg_token=$status.pwg_token;submit_post=1;post_header=$title;post_textarea="<p>Alpha body $n.</p>"} $true
  Assert ($r.StatusCode -eq 200 -and $r.Content.Contains('&lt;entry&gt;')) "Create and escape UTF-8 title $n"
  $xml=[xml](Page '?/blog/feed').Content
  $item=@($xml.rss.channel.item)|Where-Object title -eq $title
  Assert ($null -ne $item) "RSS Unicode round-trip $n"
  $ids.Add(([string]$item.link -split '/')[-1])
 }
 foreach($path in @('','?/blog','?/blog/2',"?/blog/id/$($ids[0])")){$r=Page $path;Assert ($r.StatusCode -eq 200 -and $r.Content -notmatch 'Warning:|Fatal error|Uncaught|Deprecated:') "Clean populated public view: $path"}
 $r=Page $manage @{pwg_token=$status.pwg_token;edit_post_id=$ids[0];edit_post_header='Updated alpha';edit_post_text='<p>Edited alpha.</p>'} $true
 Assert ($r.StatusCode -eq 200 -and (Page "?/blog/id/$($ids[0])").Content.Contains('Edited alpha.')) 'Editing persists'
 foreach($action in @('deactivate','activate','deactivate','uninstall','install','activate')){$null=Api @{method='pwg.plugins.performAction';action=$action;plugin='gjallarblog';pwg_token=$status.pwg_token}}
 Assert ((Page $config $null $true).Content.Contains('value="Journal"')) 'Reinstall preserves menu settings'
 Assert ((Page "?/blog/id/$($ids[0])").Content.Contains('Edited alpha.')) 'Reinstall preserves posts'
 Assert (@(([xml](Page '?/blog/feed').Content).rss.channel.item).Count -eq 5) 'Exactly five authorized posts; rejected requests added none'
} finally {foreach($id in $ids){$null=Page $manage @{pwg_token=$status.pwg_token;del=$id;confirm_delete='1'} $true}}
$xml=[xml](Page '?/blog/feed').Content
Assert ($xml.SelectNodes('/rss/channel/item').Count -eq 0) 'Delete leaves clean empty journal'
$results|Set-Content (Join-Path $StateDirectory 'alpha-results.txt')
Write-Output "All $($results.Count) alpha checks passed on port $Port."
