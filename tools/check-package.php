<?php
// Static validation; never loads plugin code.
$root = dirname(__DIR__);
$errors = [];
require_once __DIR__.'/version.php';
$required = ['main.inc.php','maintain.class.php','admin.php','manage.inc.php','manage.tpl','menubar_gjallarblog.tpl','config.inc.php','config.tpl','remove.inc.php','remove.tpl','blog.php','blog.tpl','post.php','post.tpl','feed.php','index.php','admin.css','editor.js','editor.tpl','posts.css','LICENSE','NOTICE.md','include/functions.inc.php','language/en_UK/plugin.lang.php'];
foreach ($required as $path) if (!is_file($root.'/'.$path)) $errors[] = 'Missing runtime file: '.$path;
$main = file_get_contents($root.'/main.inc.php');
if (!preg_match('/^Plugin Name: Gjallarblog\r?$/m', $main)) $errors[] = 'Wrong plugin name';
try {
    $version = gjallarblog_release_version($root);
    foreach (['README.md','docs/RELEASE-NOTES.md'] as $doc) {
        if (!str_contains(file_get_contents($root.'/'.$doc), $version)) $errors[] = 'Current version missing from '.$doc;
    }
    if (!str_contains(file_get_contents($root.'/CHANGELOG.md'), '## '.$version.' —')) $errors[] = 'Current changelog entry missing';
} catch (RuntimeException $error) { $errors[] = $error->getMessage(); }
if (str_contains($main, 'eid=611')) $errors[] = 'Original plugin update identity remains';
if (preg_match('/^\d+\t/m', file_get_contents($root.'/admin.css'))) $errors[] = 'Copied CSS line numbers';
$phpFiles = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/language', FilesystemIterator::SKIP_DOTS)) as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') $phpFiles[] = $file->getPathname();
}
foreach (glob($root.'/*.php') as $path) $phpFiles[] = $path;
foreach (glob($root.'/include/*.php') as $path) $phpFiles[] = $path;
foreach ($phpFiles as $path) {
    if (preg_match('/\b(?:Evil_Blog|evil_blog)\b/', file_get_contents($path))) $errors[] = 'Old identity: '.basename($path);
    $output = []; $code = 0;
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1', $output, $code);
    if ($code !== 0) $errors[] = implode("\n", $output);
}
foreach (glob($root.'/*.php') as $path) {
    preg_match_all('/[\'"]\/?([a-z_]+\.tpl)[\'"]/', file_get_contents($path), $matches);
    foreach ($matches[1] as $template) if (!is_file($root.'/'.$template)) $errors[] = basename($path).' references missing '.$template;
}
if (!str_contains($main, '$prefixeTable.'.chr(39).'gjallarblog'.chr(39))) $errors[] = 'Wrong posts table identity';
if (str_contains(file_get_contents($root.'/maintain.class.php'), 'evil_blog')) $errors[] = 'Original lifecycle storage remains';
if ($errors) {fwrite(STDERR, implode("\n", $errors)."\n"); exit(1);}
echo 'PASS: runtime files, metadata, storage, template references, CSS and '.count($phpFiles)." PHP syntax checks.\n";
