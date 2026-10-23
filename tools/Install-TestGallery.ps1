param([string]$BaseUrl='http://127.0.0.1:8088', [string]$StateDirectory='')
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$base = $BaseUrl
if (!$StateDirectory) { $StateDirectory = Join-Path $root '.dev' }
New-Item -ItemType Directory -Force $StateDirectory | Out-Null
$credentialFile = Join-Path $StateDirectory 'test-credentials.json'
if (!(Test-Path -LiteralPath $credentialFile)) {
    $credentials = @{ username='evaluator'; password=[Convert]::ToHexString([Security.Cryptography.RandomNumberGenerator]::GetBytes(12)); url=$base }
    [IO.File]::WriteAllText($credentialFile,($credentials | ConvertTo-Json),[Text.UTF8Encoding]::new($false))
}
$credentials = Get-Content -LiteralPath $credentialFile -Raw | ConvertFrom-Json
$dbPassword = (Get-Content -LiteralPath (Join-Path $StateDirectory 'gallery.env') -Raw).Trim().Substring('DB_PASSWORD='.Length)
$session = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
$response = Invoke-WebRequest "$base/install.php?language=en_UK" -WebSession $session -UseBasicParsing
if ($response.Content -notmatch 'Piwigo is already installed') {
    $response = Invoke-WebRequest "$base/install.php?language=en_UK" -Method Post -WebSession $session -Body @{
        install='Install'; dbhost='db'; dbuser='piwigo'; dbpasswd=$dbPassword; dbname='piwigo'; prefix='piwigo_';
        admin_name=$credentials.username; admin_pass1=$credentials.password; admin_pass2=$credentials.password; admin_mail='evaluator@example.test'
    }
    if ($response.Content -notmatch 'Piwigo installation is completed') { throw 'Piwigo installation did not confirm success; inspect the local installer.' }
    Write-Output 'Piwigo installation completed; no newsletter or mail requested.'
}
function Api([hashtable]$body) {
    $result = Invoke-RestMethod "$base/ws.php?format=json" -Method Post -Body $body -WebSession $session
    if ($result.stat -ne 'ok') { throw ('Piwigo API: '+($result | ConvertTo-Json -Compress -Depth 5)) }
    return $result.result
}
$null = Api @{method='pwg.session.login'; username=$credentials.username; password=$credentials.password}
$status = Api @{method='pwg.session.getStatus'}
$plugins = @(Api @{method='pwg.plugins.getList'})
$plugin = $plugins | Where-Object id -eq 'gjallarblog'
if (!$plugin -or $plugin.state -eq 'uninstalled') { $null=Api @{method='pwg.plugins.performAction'; action='install'; plugin='gjallarblog'; pwg_token=$status.pwg_token} }
$plugins = @(Api @{method='pwg.plugins.getList'})
if (($plugins | Where-Object id -eq 'gjallarblog').state -ne 'active') { $null=Api @{method='pwg.plugins.performAction'; action='activate'; plugin='gjallarblog'; pwg_token=$status.pwg_token} }
Write-Output 'Evaluation administrator can log in; Gjallarblog installed and active.'
