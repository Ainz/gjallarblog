<?php
// The metadata string is authoritative. Never cast it to a decimal number.
function gjallarblog_valid_release_version($version)
{
    return is_string($version) && preg_match('/^(?:0|[1-9][0-9]*)\.(?:[0-9]{2}|[1-9][0-9]{2,})(?:-(?:alpha|beta|rc)(?:\.[1-9][0-9]*)?)?$/D', $version) === 1;
}
function gjallarblog_release_version($root)
{
    preg_match('/^Version: ([^\r\n]+)$/m', file_get_contents($root.'/main.inc.php'), $match);
    $version = $match[1] ?? '';
    if (!gjallarblog_valid_release_version($version)) throw new RuntimeException('Expected Major.Minor with a two-digit minimum minor, optionally -alpha, -beta or -rc with a positive sequence number');
    return $version;
}
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    echo gjallarblog_release_version(dirname(__DIR__))."\n";
}
