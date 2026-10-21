<?php
// Isolated helper tests: no gallery/database is loaded.
define('PHPWG_ROOT_PATH', './');
function bad_request($message) { throw new InvalidArgumentException($message); }
require ($argv[1] ?? dirname(__DIR__)).'/include/functions.inc.php';
$checks = 0;
function verify($ok, $message) { global $checks; if (!$ok) throw new RuntimeException($message); ++$checks; }
foreach (['1', '2147483647'] as $id) verify(gjallarblog_id($id) === (int) $id, 'Valid ID');
foreach ([0, -1, '01', '1 OR 1=1', '2147483648', [], null, '1.0'] as $id) {
    try { gjallarblog_id($id); throw new RuntimeException('Invalid ID accepted'); }
    catch (InvalidArgumentException $e) { ++$checks; }
}
verify(gjallarblog_settings('broken') === gjallarblog_defaults(), 'Malformed configuration fallback');
verify(gjallarblog_settings(serialize((object) ['blog_name'=>'wrong'])) === gjallarblog_defaults(), 'Objects are not configuration arrays');
$settings = gjallarblog_settings(['blog_name'=>str_repeat('北', 100), 'posts_per_page'=>999]);
verify(mb_check_encoding($settings['blog_name'], 'UTF-8') && mb_strlen($settings['blog_name'],'UTF-8') <= 255, 'UTF-8 truncation remains valid');
verify($settings['posts_per_page'] === 100, 'Page size bounded');
verify(gjallarblog_escape('<script>"&') === '&lt;script&gt;&quot;&amp;', 'HTML context escaped');
verify(gjallarblog_xml_escape("A\x01B\tC") === "AB\tC", 'Invalid XML controls removed');
verify(gjallarblog_xml_escape("北 🌄 & <") === '北 🌄 &amp; &lt;', 'Unicode XML preserved');
verify(count(gjallarblog_page_links(50000, 100000)) <= 9, 'Pagination memory bounded');
verify(gjallarblog_page_links(1, 1) === [1], 'Single page navigation');
$_POST['field'] = "\xff";
try { gjallarblog_post_string('field', 100); throw new RuntimeException('Invalid UTF-8 accepted'); }
catch (InvalidArgumentException $e) { ++$checks; }
$_POST['field'] = ['array'];
try { gjallarblog_post_string('field', 100); throw new RuntimeException('Array accepted'); }
catch (InvalidArgumentException $e) { ++$checks; }
echo "PASS: $checks helper checks on PHP ".PHP_VERSION."\n";
