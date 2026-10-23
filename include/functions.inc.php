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
    $result['blog_name'] = gjallarblog_plain_title($result['blog_name']);
    if ($result['blog_name'] === '') $result['blog_name'] = 'Gjallarblog';
    // Older releases stored a separate menu label. Use one name everywhere.
    $result['menu_label'] = $result['blog_name'];
    if (!in_array($result['date_format'], ['j F Y','Y-m-d','d/m/Y'], true)) $result['date_format']='j F Y';
    if (isset($value['posts_per_page']) && is_scalar($value['posts_per_page']) && preg_match('/^[0-9]{1,3}$/D', (string)$value['posts_per_page'])) {
        $result['posts_per_page'] = max(1, min(5, (int)$value['posts_per_page']));
    }
    foreach (['show_on_main_page','show_on_category_page','show_date','show_menu'] as $key) {
        if (array_key_exists($key,$value)) $result[$key] = in_array($value[$key],[true,1,'1','on'],true);
    }
    $result['show_number']=false;
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

function gjallarblog_plain_title($value)
{
    $decoded = html_entity_decode((string)$value, ENT_QUOTES|ENT_HTML5, 'UTF-8');
    return trim(strip_tags($decoded));
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
    $hasControl = preg_match($multiline?'/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/':'/[\x00-\x1F\x7F]/', $value) === 1;
    if (in_array($key,['blog_name','post_header','edit_post_header'],true)) $value=gjallarblog_plain_title($value);
    if (!$multiline) $value = trim($value);
    if ($hasControl) {
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
        $config->set('HTML.Allowed', 'p,br,strong,b,em,i,u,s,h2,h3,h4,ul,ol,li,blockquote,pre,code,hr,a[href|title],img[src|alt|width|height|title|class],figure[class],figcaption,table,caption,thead,tbody,tfoot,tr,th[colspan|rowspan|scope],td[colspan|rowspan]');
        $config->set('Attr.AllowedClasses', ['gb-image'=>true,'gb-image--inline'=>true,'gb-image--left'=>true,'gb-image--center'=>true,'gb-image--right'=>true,'gb-image--small'=>true,'gb-image--medium'=>true,'gb-image--full'=>true]);
        $config->set('URI.AllowedSchemes', ['http'=>true, 'https'=>true, 'mailto'=>true]);
        $config->set('HTML.ForbiddenElements', ['script','style','iframe','object','embed','form','input','button','svg','math']);
        $definition=$config->getHTMLDefinition(true);
        $definition->addElement('figure','Block','Flow','Common',['class'=>'Class']);
        $definition->addElement('figcaption','Block','Flow','Common');
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
        'GB_TITLE_LIMIT'=>GJALLARBLOG_TITLE_LIMIT, 'GB_BODY_LIMIT'=>GJALLARBLOG_BODY_LIMIT, 'GB_NOW'=>date('Y-m-d\\TH:i'),
        'GB_EDITOR_TEMPLATE'=>__DIR__.'/../editor.tpl','PWG_TOKEN'=>get_pwg_token()], $values));
}

function gjallarblog_preview_document($title, $body)
{
    return '<!doctype html><html><head><meta charset="utf-8"><meta http-equiv="Content-Security-Policy" content="default-src &#39;none&#39;; img-src http: https:; style-src &#39;unsafe-inline&#39;; base-uri &#39;none&#39;; form-action &#39;none&#39;"><style>body{font:16px/1.7 system-ui,sans-serif;color:#202124;background:#fff;max-width:72ch;padding:20px;margin:auto;overflow-wrap:anywhere}img{max-width:100%;height:auto}figure.gb-image{box-sizing:border-box;max-width:100%;margin:1.25em 0}figure.gb-image img{display:block;width:100%;height:auto;border-radius:4px}figure.gb-image.gb-image--small{width:35%}figure.gb-image.gb-image--medium{width:65%}figure.gb-image.gb-image--full{width:100%}figure.gb-image.gb-image--center{margin-left:auto;margin-right:auto}figure.gb-image.gb-image--right{margin-left:auto}figure.gb-image figcaption{font-size:.9em;opacity:.8;margin-top:.4em}img.gb-image--inline{display:inline-block;width:min(12em,40%);height:auto;vertical-align:middle;margin:0 .25em}blockquote{margin:1em 0;padding:.25em 1em;border-left:3px solid #888;opacity:.9}code{font-family:ui-monospace,SFMono-Regular,Consolas,monospace;background:#f1f1f1;padding:.1em .3em}pre{max-width:100%;overflow:auto;padding:.85em 1em;border:1px solid #888;background:#f6f6f6}pre code{background:none;padding:0}hr{border:0;border-top:1px solid #888;margin:1.5em 0}table{max-width:100%;border-collapse:collapse}td,th{border:1px solid #888;padding:.4em}@media(max-width:600px){figure.gb-image.gb-image--small,figure.gb-image.gb-image--medium{width:100%}}</style></head><body><h1>'.gjallarblog_escape($title).'</h1>'.$body.'</body></html>';
}

function gjallarblog_media_directory()
{
    return rtrim(PHPWG_ROOT_PATH, '/\\').DIRECTORY_SEPARATOR.'_data'.DIRECTORY_SEPARATOR.'gjallarblog'.DIRECTORY_SEPARATOR.'media';
}

function gjallarblog_media_url($token)
{
    return get_root_url().'index.php?/blog/media/'.rawurlencode($token);
}

function gjallarblog_serve_media($token)
{
    if (!is_string($token) || !preg_match('/^[a-f0-9]{32}$/D',$token)) page_not_found();
    $token=pwg_db_real_escape_string($token);
    $row=pwg_db_fetch_assoc(gjallarblog_query("SELECT filename FROM ".Gjallarblog_Media_Table." WHERE token='$token'"));
    $filename=$row['filename']??'';
    if (!preg_match('/^[a-f0-9]{32}\\.(?:jpg|png|webp)$/D',$filename)) page_not_found();
    $directory=realpath(gjallarblog_media_directory());
    $path=$directory===false?false:realpath($directory.DIRECTORY_SEPARATOR.$filename);
    if ($path===false || strpos($path,$directory.DIRECTORY_SEPARATOR)!==0 || !is_file($path)) page_not_found();
    $mime=['jpg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp'][pathinfo($filename,PATHINFO_EXTENSION)];
    header('Content-Type: '.$mime);
    header('Content-Length: '.(string)filesize($path));
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: inline; filename="'.basename($filename).'"');
    header('Cache-Control: private, max-age=3600');
    readfile($path);
    exit;
}

function gjallarblog_media_files()
{
    $dir=gjallarblog_media_directory();
    if (!is_dir($dir)) return [];
    $files=[];
    foreach (new DirectoryIterator($dir) as $file) {
        if ($file->isFile() && preg_match('/^[a-f0-9]{32}\\.(?:jpg|png|webp)$/D',$file->getFilename())) $files[]=$file->getFilename();
    }
    return $files;
}

function gjallarblog_format_image($url, $alt='', $caption='', $layout='center', $size='medium')
{
    $url=gjallarblog_escape($url); $alt=gjallarblog_escape($alt); $caption=trim($caption);
    if ($layout==='inline') return '<img class="gb-image--inline" src="'.$url.'" alt="'.$alt.'">';
    if (!in_array($layout,['left','center','right'],true)) $layout='center';
    if (!in_array($size,['small','medium','full'],true)) $size='medium';
    $html='<figure class="gb-image gb-image--'.$layout.' gb-image--'.$size.'"><img src="'.$url.'" alt="'.$alt.'">';
    if ($caption!=='') $html.='<figcaption>'.gjallarblog_escape($caption).'</figcaption>';
    return $html.'</figure>';
}

function gjallarblog_parse_post_date($value)
{
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/D',$value)) return false;
    $zone=new DateTimeZone(date_default_timezone_get());
    $date=DateTimeImmutable::createFromFormat('!Y-m-d\\TH:i',$value,$zone);
    $issues=DateTimeImmutable::getLastErrors();
    if (!$date || ($issues && ($issues['warning_count'] || $issues['error_count'])) || $date->format('Y-m-d\\TH:i')!==$value || $date>new DateTimeImmutable('now',$zone)) return false;
    return $date->format('Y-m-d H:i:s');
}

function gjallarblog_insert_media($file)
{
    $maxBytes=10*1024*1024;
    if (!is_array($file) || !isset($file['error'],$file['tmp_name'],$file['size']) || $file['error']!==UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) throw new RuntimeException(l10n('The image could not be uploaded. Try a smaller PNG, JPEG or WebP image.'));
    if ((int)$file['size']<1 || (int)$file['size']>$maxBytes) throw new RuntimeException(l10n('Images must be 10 MB or smaller.'));
    $finfo=new finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file($file['tmp_name']);
    $extensions=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $image=@getimagesize($file['tmp_name']);
    if (!isset($extensions[$mime]) || !$image || (int)$image[0]<1 || (int)$image[1]<1 || (int)$image[0]>12000 || (int)$image[1]>12000 || (int)$image[0]*(int)$image[1]>16000000) throw new RuntimeException(l10n('This image format or size is not supported. Use PNG, JPEG or WebP up to 16 megapixels.'));
    if (!function_exists('imagecreatefromstring') || ($mime==='image/webp' && !function_exists('imagewebp'))) throw new RuntimeException(l10n('The gallery image library cannot safely process this image format.'));
    $dir=gjallarblog_media_directory();
    if (!is_dir($dir) && !@mkdir($dir,0755,true) && !is_dir($dir)) throw new RuntimeException(l10n('The image folder could not be created. Check gallery permissions.'));
    $denyFile=$dir.DIRECTORY_SEPARATOR.'.htaccess';
    if (!is_file($denyFile) && @file_put_contents($denyFile,"Require all denied\n",LOCK_EX)===false) throw new RuntimeException(l10n('The image folder could not be protected from direct access.'));
    $filename=bin2hex(random_bytes(16)).'.'.$extensions[$mime];
    $temporary=$dir.DIRECTORY_SEPARATOR.'.'.bin2hex(random_bytes(16)).'.tmp';
    $bytes=@file_get_contents($file['tmp_name']);
    $picture=$bytes===false?false:@imagecreatefromstring($bytes);
    unset($bytes);
    if (!$picture) throw new RuntimeException(l10n('The image could not be decoded safely.'));
    if ($mime==='image/jpeg' && function_exists('exif_read_data')) {
        $exif=@exif_read_data($file['tmp_name']);
        $orientation=(int)($exif['Orientation']??1);
        if ($orientation===2) imageflip($picture, IMG_FLIP_HORIZONTAL);
        elseif ($orientation===3) $picture=imagerotate($picture,180,0);
        elseif ($orientation===4) imageflip($picture, IMG_FLIP_VERTICAL);
        elseif ($orientation===5) { $picture=imagerotate($picture,-90,0); if ($picture) imageflip($picture, IMG_FLIP_HORIZONTAL); }
        elseif ($orientation===6) $picture=imagerotate($picture,-90,0);
        elseif ($orientation===7) { $picture=imagerotate($picture,90,0); if ($picture) imageflip($picture, IMG_FLIP_HORIZONTAL); }
        elseif ($orientation===8) $picture=imagerotate($picture,90,0);
        if (!$picture) throw new RuntimeException(l10n('The image orientation could not be corrected.'));
    }
    if ($mime!=='image/jpeg') imagesavealpha($picture,true);
    $saved=$mime==='image/jpeg'?@imagejpeg($picture,$temporary,90):($mime==='image/png'?@imagepng($picture,$temporary,6):@imagewebp($picture,$temporary,88));
    if (!$saved || !@rename($temporary,$dir.DIRECTORY_SEPARATOR.$filename)) { @unlink($temporary); throw new RuntimeException(l10n('The image could not be saved. Check gallery permissions.')); }
    @chmod($dir.DIRECTORY_SEPARATOR.$filename,0644);
    $token=bin2hex(random_bytes(16));
    try {
        $escapedFilename=pwg_db_real_escape_string($filename);
        gjallarblog_query("INSERT INTO ".Gjallarblog_Media_Table." (token,filename,created_at) VALUES ('$token','$escapedFilename',NOW())");
    } catch (Throwable $error) { @unlink($dir.DIRECTORY_SEPARATOR.$filename); throw $error; }
    return $token;
}

function gjallarblog_remove_media()
{
    $dir=gjallarblog_media_directory();
    if (!is_dir($dir)) return true;
    foreach (new DirectoryIterator($dir) as $file) {
        if ($file->isDot()) continue;
        if (!$file->isFile() || ($file->getFilename()!=='.htaccess' && !preg_match('/^[a-f0-9]{32}\\.(?:jpg|png|webp)$/D',$file->getFilename())) || !@unlink($file->getPathname())) return false;
    }
    return @rmdir($dir);
}

function gjallarblog_cleanup_orphan_media()
{
    $result=gjallarblog_query('SELECT token,filename FROM '.Gjallarblog_Media_Table.' WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)');
    $directory=gjallarblog_media_directory();
    while ($row=pwg_db_fetch_assoc($result)) {
        $token=pwg_db_real_escape_string($row['token']);
        [$references]=pwg_db_fetch_row(gjallarblog_query("SELECT COUNT(*) FROM ".Gjallarblog_Table." WHERE LOCATE('?/blog/media/$token', text)>0"));
        if ((int)$references>0) continue;
        $filename=$row['filename'];
        if (!preg_match('/^[a-f0-9]{32}\\.(?:jpg|png|webp)$/D',$filename)) continue;
        $path=$directory.DIRECTORY_SEPARATOR.$filename;
        if (is_file($path) && !@unlink($path)) continue;
        gjallarblog_query("DELETE FROM ".Gjallarblog_Media_Table." WHERE token='$token'");
    }
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
