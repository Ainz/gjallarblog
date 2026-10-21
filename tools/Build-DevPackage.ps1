$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
& php (Join-Path $PSScriptRoot 'check-package.php')
if ($LASTEXITCODE -ne 0) { throw 'Package checks failed' }
$stageRoot = Join-Path $projectRoot '.dev/package'
$pluginRoot = Join-Path $stageRoot 'gjallarblog'
if (Test-Path -LiteralPath $pluginRoot) {
    $resolved = [IO.Path]::GetFullPath($pluginRoot)
    $allowed = [IO.Path]::GetFullPath((Join-Path $projectRoot '.dev/package/gjallarblog'))
    if ($resolved -ne $allowed -or (Get-Item -LiteralPath $resolved).Attributes.HasFlag([IO.FileAttributes]::ReparsePoint)) { throw 'Unsafe staging path' }
    Remove-Item -LiteralPath $resolved -Recurse -Force
}
New-Item -ItemType Directory -Force $pluginRoot | Out-Null
$runtime = @('admin.css','editor.js','admin.php','blog.php','blog.tpl','config.inc.php','config.tpl','feed.php','index.php','main.inc.php','maintain.class.php','manage.inc.php','manage.tpl','editor.tpl','menubar_gjallarblog.tpl','post.php','post.tpl','posts.css','LICENSE','COPYING.txt','LICENSE.txt','NOTICE.md','README.md','CHANGELOG.md')
foreach ($name in $runtime) { Copy-Item -LiteralPath (Join-Path $projectRoot $name) -Destination $pluginRoot }
Copy-Item -LiteralPath (Join-Path $projectRoot 'language') -Destination $pluginRoot -Recurse
Copy-Item -LiteralPath (Join-Path $projectRoot 'include') -Destination $pluginRoot -Recurse
New-Item -ItemType Directory -Force (Join-Path $pluginRoot 'docs') | Out-Null
foreach ($name in @('VALIDATION.md','LEGACY-MIGRATION.md','UPSTREAM.md','UI-CORRECTIONS.md')) { Copy-Item -LiteralPath (Join-Path $projectRoot ('docs/'+$name)) -Destination (Join-Path $pluginRoot 'docs') }
Copy-Item -LiteralPath (Join-Path $projectRoot 'docs/test-evidence') -Destination (Join-Path $pluginRoot 'docs') -Recurse
$dist = Join-Path $projectRoot 'dist'
New-Item -ItemType Directory -Force $dist | Out-Null
$archive = Join-Path $dist 'gjallarblog-0.1.0-alpha.2.zip'
Compress-Archive -LiteralPath $pluginRoot -DestinationPath $archive -Force
Add-Type -AssemblyName System.IO.Compression.FileSystem
$zip = [IO.Compression.ZipFile]::OpenRead($archive)
try {
    foreach ($entry in $zip.Entries) {
        $path = $entry.FullName.Replace('\','/')
        if (!$path.StartsWith('gjallarblog/') -or $path -match '/(?:\.git|\.dev|dev|tools|dist)/|/\.env$') { throw "Unexpected archive entry: $path" }
    }
    Write-Output ('PASS: development ZIP, '+$zip.Entries.Count+' entries under gjallarblog/.')
} finally { $zip.Dispose() }
Get-FileHash -LiteralPath $archive -Algorithm SHA256
