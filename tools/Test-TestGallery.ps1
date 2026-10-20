$ErrorActionPreference='Stop'
$root=Split-Path $PSScriptRoot -Parent
$base='http://127.0.0.1:8088'
$credentials=Get-Content (Join-Path $root '.dev/test-credentials.json') -Raw|ConvertFrom-Json
$session=[Microsoft.PowerShell.Commands.WebRequestSession]::new()
$results=[Collections.Generic.List[string]]::new()
function Api([hashtable]$body) {
 $r=Invoke-RestMethod "$base/ws.php?format=json" -Method Post -Body $body -WebSession $session
 if($r.stat -ne 'ok'){throw ($r|ConvertTo-Json -Depth 5)};return $r.result
}
function Assert($condition,[string]$label){if(!$condition){throw "FAIL: $label"};$results.Add($label);Write-Output "PASS: $label"}
function Page([string]$path,$body=$null,$auth=$false){
 $args=@{Uri="$base/$path";SkipHttpErrorCheck=$true}
 if($auth){$args.WebSession=$session};if($null -ne $body){$args.Method='Post';$args.Body=$body}
 Invoke-WebRequest @args
}
$null=Api @{method='pwg.session.login';username=$credentials.username;password=$credentials.password}
$status=Api @{method='pwg.session.getStatus'}
foreach($path in @('','?/blog','?/blog/2','?/blog/id/1')){
 $r=Page $path;Assert ($r.StatusCode -eq 200 -and $r.Content -notmatch 'Warning:|Fatal error|Uncaught|Deprecated:') "Clean public view: /$path"
}
$rss=Page '?/blog/feed';$xml=[xml]$rss.Content
Assert ($rss.StatusCode -eq 200 -and $xml.rss.channel.item.Count -eq 5) 'RSS contains five well-formed entries'
foreach($path in @('?/blog/id/1%20OR%201=1','?/blog/abc','?/blog/id/0')){
 $r=Page $path;Assert ($r.StatusCode -eq 400) "Malformed route rejected: $path"
}
$r=Page '?/blog/id/999999';Assert ($r.StatusCode -eq 404) 'Missing journal entry returns 404'
foreach($tab in @('manage','config')){
 $r=Page "admin.php?page=plugin-gjallarblog-$tab" $null $true
 Assert ($r.StatusCode -eq 200 -and $r.Content -notmatch 'Warning:|Fatal error|Uncaught|Deprecated:') "Clean administration tab: $tab"
}
$r=Page 'admin.php?page=plugin-gjallarblog-manage' @{submit_post='1';post_header='MUST NOT BE SAVED';post_textarea='Missing token'} $true
Assert ($r.StatusCode -eq 400) 'Authenticated mutation without CSRF token rejected'
$r=Page 'admin.php?page=plugin-gjallarblog-manage' @{pwg_token=$status.pwg_token;del='1 OR 1=1'} $true
Assert ($r.StatusCode -eq 400) 'Malformed deletion identifier rejected'
$r=Page 'admin.php?page=plugin-gjallarblog-config' @{pwg_token=$status.pwg_token;posts_per_page='0'} $true
Assert ($r.StatusCode -eq 400) 'Zero page size rejected'
$title='Regression <entry> & apostrophe''s'
$r=Page 'admin.php?page=plugin-gjallarblog-manage' @{pwg_token=$status.pwg_token;submit_post='1';post_header=$title;post_textarea='<p>Round-trip test.</p>'} $true
Assert ($r.StatusCode -eq 200 -and $r.Content.Contains('Regression &lt;entry&gt; &amp; apostrophe')) 'Create entry and escape its title in the editor'
$xml=[xml](Page '?/blog/feed').Content
$item=@($xml.rss.channel.item)|Where-Object title -eq $title
Assert ($null -ne $item) 'Created title round-trips through RSS XML'
$id=([string]$item.link -split '/')[-1]
try {
 $r=Page "?/blog/id/$id";Assert ($r.StatusCode -eq 200 -and $r.Content.Contains('Regression &lt;entry&gt; &amp; apostrophe')) 'Public entry escapes title markup'
 $r=Page 'admin.php?page=plugin-gjallarblog-manage' @{pwg_token=$status.pwg_token;edit_post_id=$id;edit_post_header=$title;edit_post_text='<p>Edited round-trip.</p>'} $true
 Assert ($r.StatusCode -eq 200 -and (Page "?/blog/id/$id").Content.Contains('Edited round-trip.')) 'Edit entry persists content'
 foreach($action in @('deactivate','activate')){$null=Api @{method='pwg.plugins.performAction';action=$action;plugin='gjallarblog';pwg_token=$status.pwg_token}}
 $r=Page 'admin.php?page=plugin-gjallarblog-config' $null $true
 Assert ($r.Content.Contains('value="Field notes"')) 'Deactivate/reactivate preserves journal settings'
 Assert ((Page "?/blog/id/$id").Content.Contains('Edited round-trip.')) 'Deactivate/reactivate preserves posts'
}finally{$null=Page 'admin.php?page=plugin-gjallarblog-manage' @{pwg_token=$status.pwg_token;del=$id} $true}
$xml=[xml](Page '?/blog/feed').Content
Assert ($xml.rss.channel.item.Count -eq 5) 'Test entry deleted; original five samples retained'
$albums=Api @{method='pwg.categories.getList';recursive='true'}
Assert (@($albums.categories|Where-Object nb_images -eq 2).Count -eq 3) 'Three albums each contain two images'
$results | Set-Content (Join-Path $root '.dev/integration-results.txt')
Write-Output ("All "+$results.Count+' integration checks passed.')
