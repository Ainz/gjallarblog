<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

const GJALLARBLOG_TITLE_LIMIT = 255;
const GJALLARBLOG_BODY_LIMIT = 10000;

function gjallarblog_defaults()
{
    return ['blog_name'=>'Gjallarblog', 'blog_description'=>'Notes from the gallery', 'posts_per_page'=>3,
        'show_on_main_page'=>true, 'show_on_category_page'=>false, 'show_number'=>false,
        'show_date'=>true, 'date_format'=>'j F Y', 'menu_label'=>'Blog', 'show_menu'=>true,
        'home_display'=>'before', 'album_display'=>'hidden'];
}

function gjallarblog_settings($stored)
{
    $value = $stored;
    if (is_string($stored)) {
        $value = json_decode($stored, true);
        if (!is_array($value)) $value = @unserialize($stored, ['allowed_classes'=>false]);
    }
    $result = gjallarblog_defaults();
    if (!is_array($value)) return $result;
    foreach (['blog_name','blog_description','date_format','menu_label'] as $key) {
        if (isset($value[$key]) && is_string($value[$key]) && mb_check_encoding($value[$key], 'UTF-8')) {
            $result[$key] = mb_substr($value[$key], 0, $key==='date_format'?80:255, 'UTF-8');
        }
    }
    if (isset($value['posts_per_page']) && is_scalar($value['posts_per_page']) && preg_match('/^[0-9]{1,3}$/D', (string)$value['posts_per_page'])) {
        $result['posts_per_page'] = max(1, min(100, (int)$value['posts_per_page']));
    }
    foreach (['show_on_main_page','show_on_category_page','show_number','show_date','show_menu'] as $key) {
        if (array_key_exists($key,$value)) $result[$key] = in_array($value[$key],[true,1,'1','on'],true);
    }
    foreach (['home_display'=>'show_on_main_page','album_display'=>'show_on_category_page'] as $key=>$legacy) {
        $result[$key] = isset($value[$key]) && in_array($value[$key],['hidden','before','after'],true)
            ? $value[$key] : ($result[$legacy]?'before':'hidden');
        $result[$legacy] = $result[$key]!=='hidden';
    }
    if (trim($result['menu_label']) === '') $result['menu_label'] = $result['blog_name'] ?: 'Blog';
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

// Kept for callers that need strict request-shape validation.
function gjallarblog_post_string($key, $limit)
{
    if (!isset($_POST[$key]) || !is_string($_POST[$key])) bad_request('Invalid blog field');
    $value = stripslashes($_POST[$key]);
    if (!mb_check_encoding($value, 'UTF-8')) bad_request('Invalid UTF-8');
    if (strlen($value)>$limit) bad_request('Blog field is too long');
    return $value;
}

// User-correctable errors stay in the form. Limits count Unicode code points.
function gjallarblog_input($key, $label, $limit, &$errors, $required=false, $multiline=false)
{
    if (!isset($_POST[$key]) || !is_string($_POST[$key])) {
        $errors[$key] = l10n('%s: enter a text value.', $label);
        return '';
    }
    $value = str_replace(["\r\n", "\r"], "\n", stripslashes($_POST[$key]));
    if (!mb_check_encoding($value, 'UTF-8')) {
        $errors[$key] = l10n('%s: use valid Unicode text.', $label);
        return '';
    }
    if (strlen($value)>1048576) {
        $errors[$key] = l10n('%s: the submitted text is too large.', $label);
        return mb_strcut($value, 0, 1048576, 'UTF-8');
    }
    if (!$multiline) $value = trim($value);
    if (preg_match($multiline?'/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/':'/[\x00-\x1F\x7F]/', $value)) {
        $errors[$key] = l10n('%s: remove unsupported control characters.', $label);
    } elseif (mb_strlen($value, 'UTF-8')>$limit) {
        $errors[$key] = l10n('%s: use no more than %s characters.', $label, $limit);
    } elseif ($required && trim($value)==='') {
        $errors[$key] = l10n('%s is required.', $label);
    }
    return $value;
}

function gjallarblog_sanitize_html($html)
{
    static $purifier;
    if (!$purifier) {
        require_once __DIR__.'/vendor/htmlpurifier/HTMLPurifier.auto.php';
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('Cache.DefinitionImpl', null);
        $config->set('HTML.Allowed', 'p,br,strong,b,em,i,u,s,h2,h3,h4,ul,ol,li,blockquote,pre,code,hr,a[href|title],img[src|alt|width|height|title],table,caption,thead,tbody,tfoot,tr,th[colspan|rowspan|scope],td[colspan|rowspan]');
        $config->set('URI.AllowedSchemes', ['http'=>true, 'https'=>true, 'mailto'=>true]);
        $config->set('HTML.ForbiddenElements', ['script','style','iframe','object','embed','form','input','button','svg','math']);
        $purifier = new HTMLPurifier($config);
    }
    return $purifier->purify($html);
}

function gjallarblog_render_body($html)
{
    if (gjallarblog_extended_description_active()) $html = get_extended_desc($html);
    // Also protect old entries without rewriting their stored source on upgrade.
    return gjallarblog_sanitize_html($html);
}

function gjallarblog_extended_description_active()
{
    global $pwg_loaded_plugins;
    return isset($pwg_loaded_plugins['ExtendedDescription']) && function_exists('get_extended_desc') && function_exists('get_user_language_desc');
}

function gjallarblog_page_links($current, $last)
{
    return array_values(array_unique(array_merge([1], range(max(1, $current-3), min($last, $current+3)), [$last])));
}

function gjallarblog_entry_page($id, $perPage)
{
    $row=pwg_db_fetch_assoc(gjallarblog_query('SELECT date FROM '.Gjallarblog_Table.' WHERE id='.(int)$id));
    if (!$row) return 1;
    $date=pwg_db_real_escape_string($row['date']);
    [$ahead]=pwg_db_fetch_row(gjallarblog_query("SELECT COUNT(*) FROM ".Gjallarblog_Table." WHERE date>'$date' OR (date='$date' AND id>".(int)$id.')'));
    return 1+(int)floor($ahead/$perPage);
}

function gjallarblog_date($timestamp, $format='j F Y')
{
    if ($format==='j F Y') return strip_tags(format_date(date('Y-m-d H:i:s', $timestamp), ['day','month','year']));
    return date($format, $timestamp);
}

function gjallarblog_flash_redirect($message, $url)
{
    $_SESSION['gjallarblog_notice'] = $message;
    if (ob_get_length() !== false) ob_clean();
    header('Location: '.html_entity_decode($url, ENT_QUOTES, 'UTF-8'), true, 303);
    exit;
}

function gjallarblog_admin_assign($values)
{
    global $template;
    $notice=$_SESSION['gjallarblog_notice']??'';
    unset($_SESSION['gjallarblog_notice']);
    $template->assign(array_merge(['GB_NOTICE'=>$notice,'GB_ERRORS'=>[], 'GB_PREVIEW'=>'',
        'GB_ADMIN_THEME'=>userprefs_get_param('admin_theme','clear')==='roma'?'roma':'clear',
        'GB_TITLE_LIMIT'=>GJALLARBLOG_TITLE_LIMIT, 'GB_BODY_LIMIT'=>GJALLARBLOG_BODY_LIMIT,
        'GB_EDITOR_TEMPLATE'=>__DIR__.'/../editor.tpl','PWG_TOKEN'=>get_pwg_token()], $values));
}

function gjallarblog_preview_document($title, $body)
{
    return '<!doctype html><html><head><meta charset="utf-8"><meta http-equiv="Content-Security-Policy" content="default-src &#39;none&#39;; img-src http: https:; style-src &#39;unsafe-inline&#39;; base-uri &#39;none&#39;; form-action &#39;none&#39;"><style>body{font:16px/1.7 system-ui,sans-serif;color:#202124;background:#fff;max-width:72ch;padding:20px;margin:auto;overflow-wrap:anywhere}img{max-width:100%;height:auto}pre{overflow:auto}table{max-width:100%;border-collapse:collapse}td,th{border:1px solid #888;padding:.4em}</style></head><body><h1>'.gjallarblog_escape($title).'</h1>'.$body.'</body></html>';
}

function gjallarblog_xml_escape($value)
{
    $value=preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x{FFFE}\x{FFFF}]/u', '', $value);
    return htmlspecialchars($value??'', ENT_XML1|ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
}

function gjallarblog_query($sql, &$insertId=null)
{
    global $mysqli;
    $previous=$mysqli->character_set_name();
    if ($previous!=='utf8mb4') $mysqli->set_charset('utf8mb4');
    try { $result=pwg_query($sql); $insertId=(int)$mysqli->insert_id; return $result; }
    finally { if ($previous!=='utf8mb4') $mysqli->set_charset($previous); }
}
