[CmdletBinding()]
param([string]$PythonExecutable='python',[switch]$KeepOnFailure)
$ErrorActionPreference='Stop'
$root=Split-Path $PSScriptRoot -Parent
$helper=Join-Path $env:USERPROFILE 'Documents/ChatGPT/Development - GIT/Wsl-Session.ps1'
& $helper -Action Start | Out-Null
$id=[Guid]::NewGuid().ToString('N').Substring(0,8)
$project="gjallarblog-removal-$id"
$port=8095
$container="$project-gallery-1"
$state=Join-Path $root ".dev/removal-$id"
$testPassed=$false
New-Item -ItemType Directory -Force $state | Out-Null
$password=[Convert]::ToHexString([Security.Cryptography.RandomNumberGenerator]::GetBytes(16))
[IO.File]::WriteAllText((Join-Path $state '.env'),"DB_PASSWORD=$password`n",[Text.UTF8Encoding]::new($false))
[IO.File]::WriteAllText((Join-Path $state 'gallery.env'),"DB_PASSWORD=$password`n",[Text.UTF8Encoding]::new($false))
$yaml=@"
name: $project
services:
  gallery:
    image: gjallarblog-a16-gallery
    ports: ["127.0.0.1:${port}:80"]
    depends_on:
      db:
        condition: service_healthy
    volumes: ["config:/var/www/html/local/config", "data:/var/www/html/_data"]
  db:
    image: mariadb:11.4
    environment:
      MARIADB_RANDOM_ROOT_PASSWORD: "yes"
      MARIADB_DATABASE: piwigo
      MARIADB_USER: piwigo
      MARIADB_PASSWORD: "$password"
    healthcheck:
      test: ["CMD", "healthcheck.sh", "--connect", "--innodb_initialized"]
      interval: 3s
      timeout: 5s
      retries: 40
    volumes: ["db:/var/lib/mysql"]
volumes:
  config:
  data:
  db:
"@
$compose=Join-Path $state 'compose.yaml'
[IO.File]::WriteAllText($compose,$yaml,[Text.UTF8Encoding]::new($false))
$wslState="/mnt/d/Development/gjallarblog/.dev/removal-$id"
$wslCompose="$wslState/compose.yaml"
$wslEnv="$wslState/.env"
try {
  & wsl.exe -d Debian -u root --exec docker compose --env-file $wslEnv -f $wslCompose up -d
  if($LASTEXITCODE -ne 0){throw 'Disposable removal gallery failed to start'}
  $ready=$false
  for($attempt=0;$attempt -lt 20 -and !$ready;$attempt++){
    try{$null=Invoke-WebRequest "http://127.0.0.1:$port/install.php" -TimeoutSec 3;$ready=$true}catch{Start-Sleep -Seconds 1}
  }
  if(!$ready){& wsl.exe -d Debian -u root --exec docker logs $container;throw 'Disposable gallery did not become ready'}
  & wsl.exe -d Debian -u root --exec docker cp /mnt/d/Development/gjallarblog/.dev/package/gjallarblog "${container}:/var/www/html/plugins/"
  if($LASTEXITCODE -ne 0){throw 'Could not install writable test copy'}
  & wsl.exe -d Debian -u root --exec docker exec $container chown -R www-data:www-data /var/www/html/plugins/gjallarblog
  & $PSScriptRoot/Install-TestGallery.ps1 -BaseUrl "http://127.0.0.1:$port" -StateDirectory $state
  & wsl.exe -d Debian -u root --exec docker cp /mnt/d/Development/gjallarblog/dev/bootstrap-alpha.php "${container}:/tmp/bootstrap-alpha.php"
  if($LASTEXITCODE -ne 0){throw 'Could not copy disposable gallery bootstrap'}
  & wsl.exe -d Debian -u root --exec docker exec $container php /tmp/bootstrap-alpha.php
  if($LASTEXITCODE -ne 0){throw 'Could not prepare the disposable gallery routes'}
  $credentials=Get-Content (Join-Path $state 'test-credentials.json') -Raw|ConvertFrom-Json
  $session=[Microsoft.PowerShell.Commands.WebRequestSession]::new()
  function Api([hashtable]$body){$r=Invoke-RestMethod "http://127.0.0.1:$port/ws.php?format=json" -Method Post -Body $body -WebSession $session;if($r.stat -ne 'ok'){throw ($r|ConvertTo-Json -Compress -Depth 5)};return $r.result}
  function Assert($ok,[string]$message){if(!$ok){throw "FAIL: $message"};Write-Output "PASS: $message"}
  function CopyPlugin { & wsl.exe -d Debian -u root --exec docker cp /mnt/d/Development/gjallarblog/.dev/package/gjallarblog "${container}:/var/www/html/plugins/"; if($LASTEXITCODE -ne 0){throw 'Could not restore test copy'}; & wsl.exe -d Debian -u root --exec docker exec $container chown -R www-data:www-data /var/www/html/plugins/gjallarblog }
  $null=Api @{method='pwg.session.login';username=$credentials.username;password=$credentials.password}
  $status=Api @{method='pwg.session.getStatus'}
  $null=Api @{method='pwg.plugins.performAction';action='install';plugin='gjallarblog';pwg_token=$status.pwg_token}
  $null=Api @{method='pwg.plugins.performAction';action='activate';plugin='gjallarblog';pwg_token=$status.pwg_token}
  $manage='admin.php?page=plugin-gjallarblog-manage'
  $config='admin.php?page=plugin-gjallarblog-config'
  $settings=@{pwg_token=$status.pwg_token;posts_per_page=3;blog_name='Keep test journal';blog_description='Test preservation';date_format='j F Y';menu_label='Keep test';home_display='before';album_display='hidden';show_menu='on';show_date='on'}
  $null=Invoke-WebRequest "http://127.0.0.1:$port/$config" -Method Post -WebSession $session -Body $settings
  $pngPath=Join-Path $state 'removal-pixel.png'
  $linuxPng="/mnt/d/Development/gjallarblog/.dev/removal-$id/removal-pixel.png"
  $pngGenerator='import pathlib,struct,zlib,binascii,sys; chunk=lambda k,d: struct.pack(">I",len(d))+k+d+struct.pack(">I",binascii.crc32(k+d)&0xffffffff); pathlib.Path(sys.argv[1]).write_bytes(b"\x89PNG\r\n\x1a\n"+chunk(b"IHDR",struct.pack(">IIBBBBB",1,1,8,6,0,0,0))+chunk(b"IDAT",zlib.compress(b"\x00\xff\x20\x10\xff"))+chunk(b"IEND",b""))'
  & wsl.exe -d Debian --exec python3 -c $pngGenerator $linuxPng
  if($LASTEXITCODE -ne 0){throw 'Could not create PNG fixture for removal test'}
  $upload=Invoke-WebRequest "http://127.0.0.1:$port/$manage" -Method Post -WebSession $session -Form @{pwg_token=$status.pwg_token;upload_image='1';image=(Get-Item -LiteralPath $pngPath)}
  $media=($upload.Content | ConvertFrom-Json)
  if(!$media.url){throw 'Image upload did not return a media URL'}
  $testIds=@()
  foreach($title in @('Keep fixture one','Keep fixture two')){$body="<p>$title body</p>";if($title -eq 'Keep fixture one'){$body+="<img src=`"$($media.url)`" alt=`"Preserved image`">"};$saved=Invoke-WebRequest "http://127.0.0.1:$port/$manage" -Method Post -WebSession $session -Body @{pwg_token=$status.pwg_token;submit_post=1;post_header=$title;post_textarea=$body;post_date='2020-01-02T12:00'};$null=$saved.BaseResponse.RequestMessage.RequestUri.Query -match '[?&]edit=(\d+)';$testIds+= $Matches[1]}
  $mediaPath=([uri]::new([uri]"http://127.0.0.1:$port/",[string]$media.url)).PathAndQuery
  $mediaBefore=Invoke-WebRequest "http://127.0.0.1:$port$mediaPath" -WebSession $session
  Assert ($mediaBefore.Headers['Content-Type'] -like 'image/png*') 'Uploaded image is served before plugin removal'
  $null=Api @{method='pwg.plugins.performAction';action='restore';plugin='gjallarblog';pwg_token=$status.pwg_token}
  $restoredPlugin=@(Api @{method='pwg.plugins.getList'})|Where-Object id -eq 'gjallarblog'
  Assert ($restoredPlugin.state -eq 'active') 'Piwigo Restore reactivates Gjallarblog'
  Assert ((Invoke-WebRequest "http://127.0.0.1:$port/$manage" -WebSession $session).Content.Contains('Keep fixture one')) 'Piwigo Restore keeps journal entries'
  Assert ((Invoke-WebRequest "http://127.0.0.1:$port/$config" -WebSession $session).Content.Contains('Keep test journal')) 'Piwigo Restore keeps journal settings'
  Assert ((Invoke-WebRequest "http://127.0.0.1:$port$mediaPath" -WebSession $session).Headers['Content-Type'] -like 'image/png*') 'Piwigo Restore keeps and serves journal images'
  $remove='admin.php?page=plugin-gjallarblog-remove'
  $review=Invoke-WebRequest "http://127.0.0.1:$port/$remove" -WebSession $session
  Assert ($review.Content.Contains('<strong>2</strong>') -and $review.Content.Contains('<strong>1</strong>') -and $review.Content.Contains('Keep journal entries, images and settings')) 'Removal warning names live entry/image counts and keep option'
  $removed=Invoke-WebRequest "http://127.0.0.1:$port/$remove" -Method Post -WebSession $session -SkipHttpErrorCheck -Body @{pwg_token=$status.pwg_token;entry_count='2';data_action='keep';confirm_remove='1'}
  $folder=& wsl.exe -d Debian -u root --exec docker exec $container sh -c 'test -d /var/www/html/plugins/gjallarblog && echo present'
  Assert (!$folder -and $removed.Content.Contains('Journal entries, images and settings were kept')) 'Keep removal reports success only after the plugin folder is gone'
  CopyPlugin
  $null=Api @{method='pwg.plugins.performAction';action='install';plugin='gjallarblog';pwg_token=$status.pwg_token}
  $null=Api @{method='pwg.plugins.performAction';action='activate';plugin='gjallarblog';pwg_token=$status.pwg_token}
  $settingsPage=(Invoke-WebRequest "http://127.0.0.1:$port/$config" -WebSession $session).Content
  $managePage=(Invoke-WebRequest "http://127.0.0.1:$port/$manage" -WebSession $session).Content
  Assert ($managePage.Contains('Keep fixture one')) "Fresh installation lists preserved entry one (id $($testIds[0]))"
  Assert ($managePage.Contains('Keep fixture two')) "Fresh installation lists preserved entry two (id $($testIds[1]))"
  Assert ($settingsPage.Contains('Keep test journal')) 'Fresh installation restores preserved journal settings'
  $mediaResponse=Invoke-WebRequest "http://127.0.0.1:$port$mediaPath" -WebSession $session
  Assert ($mediaResponse.Headers['Content-Type'] -like 'image/png*') "Reinstalled plugin serves preserved media (URL $($media.url), requested $mediaPath, status $($mediaResponse.StatusCode), type $($mediaResponse.Headers['Content-Type']))"
  $null=Invoke-WebRequest "http://127.0.0.1:$port/$manage" -Method Post -WebSession $session -Body @{pwg_token=$status.pwg_token;submit_post=1;post_header='Erase fixture';post_textarea='<p>Delete me</p>';post_date='2020-01-02T12:00'}
  $before=Invoke-WebRequest "http://127.0.0.1:$port/$remove" -WebSession $session
  Assert ($before.Content.Contains('<strong>3</strong>') -and $before.Content.Contains('Stored journal images:') -and $before.Content.Contains('<strong>1</strong>')) 'Second removal shows updated entry and image counts'
  $noConfirm=Invoke-WebRequest "http://127.0.0.1:$port/$remove" -Method Post -WebSession $session -SkipHttpErrorCheck -Body @{pwg_token=$status.pwg_token;entry_count='3';data_action='erase';confirm_remove='1';erase_name='wrong'}
  Assert ($noConfirm.StatusCode -eq 400 -and (Test-Path -LiteralPath $state)) 'Permanent erasure requires the typed name'
  $removed=Invoke-WebRequest "http://127.0.0.1:$port/$remove" -Method Post -WebSession $session -SkipHttpErrorCheck -Body @{pwg_token=$status.pwg_token;entry_count='3';data_action='erase';confirm_remove='1';erase_name='Gjallarblog'}
  $folder=& wsl.exe -d Debian -u root --exec docker exec $container sh -c 'test -d /var/www/html/plugins/gjallarblog && echo present'
  Assert (!$folder -and $removed.Content.Contains('journal entries, 1 image files and settings have been removed')) 'Permanent removal drops posts, media and settings after verified folder removal'
  $mediaFolder=& wsl.exe -d Debian -u root --exec docker exec $container sh -c 'test -d /var/www/html/_data/gjallarblog/media && echo present'
  Assert (!$mediaFolder) 'Permanent removal deletes the plugin media folder'
  CopyPlugin
  $null=Api @{method='pwg.plugins.performAction';action='install';plugin='gjallarblog';pwg_token=$status.pwg_token}
  $null=Api @{method='pwg.plugins.performAction';action='activate';plugin='gjallarblog';pwg_token=$status.pwg_token}
  Assert ((Invoke-WebRequest "http://127.0.0.1:$port/$manage" -WebSession $session).Content.Contains('No entries yet.')) 'Fresh installation after erasure starts with an empty journal'
  $testPassed=$true
  Write-Output 'PASS: writable deletion tests passed; the main evaluation gallery was not used.'
}
finally {
  if(!$KeepOnFailure -or $testPassed){& wsl.exe -d Debian -u root --exec docker compose --env-file $wslEnv -f $wslCompose down -v --remove-orphans | Out-Null}
}
