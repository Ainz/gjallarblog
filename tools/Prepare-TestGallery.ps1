$ErrorActionPreference='Stop'
$root=Split-Path $PSScriptRoot -Parent
& (Join-Path $PSScriptRoot 'Start-TestGallery.ps1')
& (Join-Path $PSScriptRoot 'Install-TestGallery.ps1')
$linuxRoot='/mnt/'+$root.Substring(0,1).ToLowerInvariant()+'/'+$root.Substring(3).Replace('\','/')
& wsl.exe -d Debian -u root --exec /bin/bash "$linuxRoot/tools/seed-test-gallery.sh" $linuxRoot
if($LASTEXITCODE -ne 0){throw 'Fixture preparation failed'}
& (Join-Path $PSScriptRoot 'Seed-TestGallery.ps1')
Write-Output 'Gjallarblog evaluation gallery prepared: http://127.0.0.1:8088'
