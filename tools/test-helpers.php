<?php
// Isolated helper tests: no gallery/database is loaded.
define('PHPWG_ROOT_PATH', './');
function bad_request($message) { throw new InvalidArgumentException($message); }
$pluginRoot = $argv[1] ?? dirname(__DIR__);
require $pluginRoot.'/include/functions.inc.php';
$checks = 0;
function verify($ok, $message) { global $checks; if (!$ok) throw new RuntimeException($message); ++$checks; }
foreach (['1', '2147483647'] as $id) verify(gjallarblog_id($id) === (int) $id, 'Valid ID');
foreach ([0, -1, '01', '1 OR 1=1', '2147483648', [], null, '1.0'] as $id) {
    try { gjallarblog_id($id); throw new RuntimeException('Invalid ID accepted'); }
    catch (InvalidArgumentException $e) { ++$checks; }
}
verify(gjallarblog_settings('broken') === gjallarblog_defaults(), 'Malformed configuration fallback');
verify(gjallarblog_settings(serialize((object) ['blog_name'=>'wrong'])) === gjallarblog_defaults(), 'Objects are not configuration arrays');
$settings = gjallarblog_settings(['blog_name'=>str_repeat('北', 100), 'posts_per_page'=>999, 'show_number'=>true]);
verify(mb_check_encoding($settings['blog_name'], 'UTF-8') && mb_strlen($settings['blog_name'],'UTF-8') <= 255, 'UTF-8 truncation remains valid');
verify($settings['posts_per_page'] === 5, 'Page size bounded to five');
verify($settings['show_number'] === false, 'Legacy entry-number setting is always hidden');
verify($settings['date_format'] === 'j F Y', 'Unsupported legacy date format falls back to localized default');
verify(gjallarblog_escape('<script>"&') === '&lt;script&gt;&quot;&amp;', 'HTML context escaped');
verify(gjallarblog_plain_title('<H2>News</H2> &amp; notes') === 'News & notes', 'Title formatting is removed while text and entities are preserved');
verify(gjallarblog_xml_escape("A\x01B\tC") === "AB\tC", 'Invalid XML controls removed');
verify(gjallarblog_xml_escape("北 🌄 & <") === '北 🌄 &amp; &lt;', 'Unicode XML preserved');
verify(count(gjallarblog_page_links(50000, 100000)) <= 9, 'Pagination memory bounded');
verify(gjallarblog_page_links(1, 1) === [1], 'Single page navigation');
verify(gjallarblog_parse_post_date('2000-02-29T12:34') === '2000-02-29 12:34:00', 'Parse historical date using gallery timezone');
verify(gjallarblog_parse_post_date('2023-02-29T12:34') === false, 'Reject impossible calendar date');
verify(gjallarblog_parse_post_date(date('Y-m-d\\TH:i', time()+86400)) === false, 'Reject future publication time');
verify(gjallarblog_parse_post_date(['2020-01-01T12:00']) === false, 'Reject array-shaped publication date');
verify(str_contains(gjallarblog_sanitize_html('<figure class="gb-image gb-image--center gb-image--medium"><img src="index.php?/blog/media/'.str_repeat('a',32).'.png" alt="Ship"><figcaption>Caption</figcaption></figure>'), '<figure class="gb-image gb-image--center gb-image--medium">'), 'Allow controlled image layout classes and captions');
verify(!str_contains(gjallarblog_sanitize_html('<figure class="gb-image dangerous"><img src="x" alt="a"></figure>'), 'dangerous'), 'Strip unapproved image layout classes');
$_POST['field'] = "\xff";
try { gjallarblog_post_string('field', 100); throw new RuntimeException('Invalid UTF-8 accepted'); }
catch (InvalidArgumentException $e) { ++$checks; }
$_POST['field'] = ['array'];
try { gjallarblog_post_string('field', 100); throw new RuntimeException('Array accepted'); }
catch (InvalidArgumentException $e) { ++$checks; }
echo "PASS: $checks helper checks on PHP ".PHP_VERSION."\n";
