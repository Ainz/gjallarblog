[CmdletBinding()]
param([ValidateSet('Start','Status','Stop')][string]$Action = 'Start')
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
$helper = if ($env:GJALLARBLOG_WSL_HELPER) { $env:GJALLARBLOG_WSL_HELPER } else { Join-Path $env:USERPROFILE 'Documents/ChatGPT/Development - GIT/Wsl-Session.ps1' }
if (!(Test-Path -LiteralPath $helper)) { throw 'Shared Wsl-Session.ps1 not found; set GJALLARBLOG_WSL_HELPER to its path.' }
if ($Action -eq 'Status') {
    $status = & $helper -Action Status
    $status
    if (!$status.Running) { return }
} else {
    & $helper -Action Start
}
if ($Action -eq 'Start') {
    & (Join-Path $PSScriptRoot 'Build-DevPackage.ps1')
    $coreDir = Join-Path $projectRoot '.dev/core'
    New-Item -ItemType Directory -Force $coreDir | Out-Null
    $core = Join-Path $coreDir 'piwigo-16.4.0.zip'
    if (!(Test-Path -LiteralPath $core)) { Invoke-WebRequest 'https://piwigo.org/download/dlcounter.php?code=16.4.0' -OutFile $core }
    if ((Get-FileHash -LiteralPath $core -Algorithm SHA256).Hash -ne '72C58A337A1A0639B6AD8DC8F5900A2781FC19091B60AE157EB2EE58C5431C17') { throw 'Piwigo archive checksum mismatch' }
    $envFile = Join-Path $projectRoot '.dev/gallery.env'
    if (!(Test-Path -LiteralPath $envFile)) {
        $dbPassword = [Convert]::ToHexString([Security.Cryptography.RandomNumberGenerator]::GetBytes(24))
        [IO.File]::WriteAllText($envFile, "DB_PASSWORD=$dbPassword`n", [Text.UTF8Encoding]::new($false))
    }
}
$linuxRoot = '/mnt/' + $projectRoot.Substring(0,1).ToLowerInvariant() + '/' + $projectRoot.Substring(3).Replace('\','/')
& wsl.exe --distribution Debian --user root --exec /bin/bash "$linuxRoot/tools/wsl-test-gallery.sh" $Action $linuxRoot
if ($LASTEXITCODE -ne 0) { throw "WSL gallery $Action failed" }
if ($Action -eq 'Start') { Write-Output 'Gallery: http://127.0.0.1:8088. Shared WSL lease renewed; no global shutdown/release performed.' }
