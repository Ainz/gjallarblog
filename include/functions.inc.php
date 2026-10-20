<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

function gjallarblog_defaults()
{
    return ['blog_name'=>'Gjallarblog', 'blog_description'=>'Notes from the gallery', 'posts_per_page'=>3,
        'show_on_main_page'=>true, 'show_on_category_page'=>false, 'show_number'=>false,
        'show_date'=>true, 'date_format'=>'j F Y', 'menu_label'=>'Blog'];
}

function gjallarblog_settings($stored)
{
    $value = is_array($stored) ? $stored : (is_string($stored) ? @unserialize($stored, ['allowed_classes'=>false]) : []);
    $result = gjallarblog_defaults();
    if (!is_array($value)) return $result;
    foreach (['blog_name','blog_description','date_format','menu_label'] as $key) {
        if (isset($value[$key]) && is_string($value[$key])) $result[$key] = mb_strcut($value[$key], 0, $key==='date_format'?80:255, 'UTF-8');
    }
    if (isset($value['posts_per_page']) && is_scalar($value['posts_per_page']) && preg_match('/^[0-9]{1,3}$/D', (string)$value['posts_per_page'])) {
        $result['posts_per_page'] = max(1, min(100, (int)$value['posts_per_page']));
    }
    foreach (['show_on_main_page','show_on_category_page','show_number','show_date'] as $key) {
        if (array_key_exists($key,$value)) $result[$key] = in_array($value[$key],[true,1,'1','on'],true);
    }
    return $result;
}

function gjallarblog_escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
}

function gjallarblog_id($value)
{
    if (!is_scalar($value) || !preg_match('/^[1-9][0-9]{0,9}$/D', (string)$value) || (float)$value>2147483647) bad_request('Invalid blog identifier');
    return (int)$value;
}

function gjallarblog_post_string($key, $limit)
{
    if (!isset($_POST[$key]) || !is_string($_POST[$key])) bad_request('Invalid blog field');
    // Piwigo common.inc.php adds slashes to incoming request values.
    $value = stripslashes($_POST[$key]);
    if (!mb_check_encoding($value, 'UTF-8')) bad_request('Invalid UTF-8');
    if (strlen($value)>$limit) bad_request('Blog field is too long');
    return $value;
}

function gjallarblog_extended_description_active()
{
    global $pwg_loaded_plugins;
    return isset($pwg_loaded_plugins['ExtendedDescription']) && function_exists('get_extended_desc') && function_exists('get_user_language_desc');
}

// Limit navigation size even when a journal has many years of entries.
function gjallarblog_page_links($current, $last)
{
    return array_values(array_unique(array_merge([1], range(max(1, $current - 3), min($last, $current + 3)), [$last])));
}

function gjallarblog_xml_escape($value)
{
    // XML 1.0 forbids these controls, even when HTML authors paste them into a post.
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x{FFFE}\x{FFFF}]/u', '', (string) $value);
    return htmlspecialchars($value ?? '', ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
// Scope utf8mb4 to this plugin's own table; do not change Piwigo's connection policy.
function gjallarblog_query($sql)
{
    global $mysqli;
    $previous = $mysqli->character_set_name();
    if ($previous !== 'utf8mb4') $mysqli->set_charset('utf8mb4');
    try {
        return pwg_query($sql);
    } finally {
        if ($previous !== 'utf8mb4') $mysqli->set_charset($previous);
    }
}