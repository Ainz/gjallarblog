[CmdletBinding()]
param([switch]$SkipBuild, [string[]]$Fixtures = @())
$ErrorActionPreference='Stop'
$root=Split-Path $PSScriptRoot -Parent
$helper=if($env:GJALLARBLOG_WSL_HELPER){$env:GJALLARBLOG_WSL_HELPER}else{Join-Path $env:USERPROFILE 'Documents/ChatGPT/Development - GIT/Wsl-Session.ps1'}
& $helper -Action Start
if(!$SkipBuild){
 & (Join-Path $PSScriptRoot 'Build-DevPackage.ps1')
 & wsl.exe -d Debian -u root --exec bash /mnt/d/Development/gjallarblog/tools/start-alpha.sh --extended
 if($LASTEXITCODE -ne 0){throw 'Matrix startup failed'}
}
$cases=@(@('a15',8089,'15.7.0'),@('a16',8090,'16.4.0'),@('a1582',8091,'15.7.0'),@('a1682',8092,'16.4.0'),@('a1683',8093,'16.4.0'),@('a1685',8094,'16.4.0'))
$evidence=Join-Path $root 'docs/test-evidence'
New-Item -ItemType Directory -Force $evidence|Out-Null
$matrix=@()
foreach($case in $cases){
 if($Fixtures.Count -gt 0 -and $case[0] -notin $Fixtures){continue}
 $project=$case[0];$port=[int]$case[1];$state=Join-Path $root ".dev/$project";$container="gjallarblog-$project-gallery-1"
 & (Join-Path $PSScriptRoot 'Install-TestGallery.ps1') -BaseUrl "http://127.0.0.1:$port" -StateDirectory $state
 & wsl.exe -d Debian -u root --exec docker cp /mnt/d/Development/gjallarblog/dev/bootstrap-alpha.php "${container}:/tmp/bootstrap-alpha.php"
 if($LASTEXITCODE -ne 0){throw 'Bootstrap copy failed'}
 & wsl.exe -d Debian -u root --exec docker exec $container php /tmp/bootstrap-alpha.php
 if($LASTEXITCODE -ne 0){throw 'Bootstrap failed'}
 & wsl.exe -d Debian -u root --exec docker cp /mnt/d/Development/gjallarblog/tools/test-helpers.php "${container}:/tmp/test-helpers.php"
 $helperResult=& wsl.exe -d Debian -u root --exec docker exec $container php /tmp/test-helpers.php /var/www/html/plugins/gjallarblog
 if($LASTEXITCODE -ne 0){throw 'Helper checks failed'}
 $helperResult|Set-Content (Join-Path $evidence "$project-helpers.txt")
 & (Join-Path $PSScriptRoot 'Test-Alpha.ps1') -Port $port -StateDirectory $state
 $lines=Get-Content (Join-Path $state 'alpha-results.txt')|ForEach-Object {$_.TrimEnd()}
 [IO.File]::WriteAllText((Join-Path $evidence "$project-integration.txt"), ($lines -join "`n")+"`n", [Text.UTF8Encoding]::new($false))
 $php=& wsl.exe -d Debian -u root --exec docker exec $container php -r 'echo PHP_VERSION;'
 $matrix+=@{piwigo=$case[2];php=($php -join '').Trim();database='MariaDB 11.4';integration_checks=44;helper_checks=21;result='PASS';fixture=$project}
}
[IO.File]::WriteAllText((Join-Path $evidence 'matrix.json'), ($matrix|ConvertTo-Json -Depth 5).Replace("`r`n","`n")+"`n", [Text.UTF8Encoding]::new($false))
Write-Output 'PASS: selected disposable alpha compatibility cases.'
