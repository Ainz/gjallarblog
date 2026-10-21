<?php
/*
Version: 0.1.0-alpha.2
Plugin Name: Gjallarblog
Author: EvilKant; Ainz (Gjallarblog fork)
Description: A small journal for your Piwigo gallery.
Has Settings: true
*/
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');
global $prefixeTable;
define('Gjallarblog_PATH', PHPWG_PLUGINS_PATH.basename(__DIR__).'/');
define('Gjallarblog_Admin', get_root_url().'admin.php?page=plugin-'.basename(__DIR__));
define('Gjallarblog_Table', $prefixeTable.'gjallarblog');
require_once __DIR__.'/include/functions.inc.php';

add_event_handler('loading_lang', 'Gjallarblog_loading_lang');
function Gjallarblog_loading_lang() { load_language('plugin.lang', Gjallarblog_PATH); }
add_event_handler('blockmanager_register_blocks', 'gjallarblog_register_menu');
add_event_handler('blockmanager_prepare_display', 'gjallarblog_position_menu');
add_event_handler('blockmanager_apply', 'gjallarblog_add_to_menu');

add_event_handler('loc_begin_index', 'Gjallarblog_main');
function Gjallarblog_main()
{
    global $template, $tokens, $conf, $page;
    $page['gjallarblog'] = gjallarblog_settings($conf['gjallarblog']??null);
    $ExDesc = ['state'=>gjallarblog_extended_description_active()?'active':'inactive'];
    if ($ExDesc['state']==='active') {
        foreach (['blog_name','blog_description'] as $key) $page['gjallarblog'][$key]=get_user_language_desc($page['gjallarblog'][$key]);
    }
    $section=$tokens[0]??'';
    if ($section==='blog' && ($tokens[1]??'')==='feed') { include __DIR__.'/feed.php'; return; }
    if ($section==='blog' && ($tokens[1]??'')==='id') {
        gjallarblog_id($tokens[2]??null);
        include __DIR__.'/post.php';
    } elseif ($section==='blog' || ($section==='' && $page['gjallarblog']['show_on_main_page']) ||
        (in_array($section,['category','categories'],true) && $page['gjallarblog']['show_on_category_page'])) {
        include __DIR__.'/blog.php';
    }
}

function gjallarblog_register_menu($refs)
{
    if ($refs[0]->get_id()==='menubar') $refs[0]->register_block(new RegisteredBlock('mbGjallarblog', l10n('Blog'), 'Gjallarblog'));
}

function gjallarblog_position_menu($refs)
{
    global $conf;
    if ($refs[0]->get_id()!=='menubar') return;
    $positions=$conf['blk_menubar']??[];
    if (is_string($positions)) $positions=@unserialize($positions,['allowed_classes'=>false]);
    if (!is_array($positions) || !array_key_exists('mbGjallarblog',$positions)) {
        $albums=$refs[0]->get_block('mbCategories');
        if ($albums!==null) $refs[0]->set_block_position('mbGjallarblog',$albums->get_position()+1);
    }
}

function gjallarblog_add_to_menu($refs)
{
    global $conf;
    if ($refs[0]->get_id()!=='menubar') return;
    $block=$refs[0]->get_block('mbGjallarblog');
    if ($block===null) return;
    $settings=gjallarblog_settings($conf['gjallarblog']??null);
    $block->data=['TITLE'=>$settings['blog_description'], 'NAME'=>$settings['menu_label'], 'URL'=>make_index_url(['section'=>'blog']), 'CURRENT'=>(($GLOBALS['page']['section']??'')==='blog')];
    $block->template=__DIR__.'/menubar_gjallarblog.tpl';
}

add_event_handler('loc_end_section_init', 'section_init_gjallarblog');
function section_init_gjallarblog()
{
    global $page,$tokens;
    if (($tokens[0]??'')==='blog') { $page['section']='blog'; $page['body_id']='theGjallarblogPage'; $page['is_external']=true; }
}
