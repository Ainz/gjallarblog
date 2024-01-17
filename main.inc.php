<?php
/*
Version: 1.2.3
Plugin Name: Evil_Blog
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=611
Author: EvilKant
Description: Small blog for your gallery.
Has Settings: true
*/

// Chech whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');
 
global $prefixeTable;
// Определяем директорию плагина
define('Evil_Blog_PATH', PHPWG_PLUGINS_PATH.basename(dirname(__FILE__)).'/');
define('Evil_Blog_Admin', get_root_url() . 'admin.php?page=plugin-' . basename(dirname(__FILE__)));
define('Evil_Blog_Table', $prefixeTable."evil_blog");
//Включаем перевод плагина
add_event_handler('loading_lang', 'Evil_Blog_loading_lang');
function Evil_Blog_loading_lang()
{
  load_language('plugin.lang', Evil_Blog_PATH);
}

function Evil_Blog_main () {
	global $template, $tokens, $conf, $page;	
		
		//выковыриваем из базы настройки блога
		$page['evil_blog'] = @unserialize($conf['evil_blog']);
		
		//включаем Extended Description для пунктов меню
		$ExDesc = pwg_db_fetch_assoc(pwg_query("SELECT state FROM " . PLUGINS_TABLE . " WHERE id = 'ExtendedDescription';"));
		if($ExDesc['state'] == 'active') {
			$page['evil_blog']['blog_name'] = get_user_language_desc($page['evil_blog']['blog_name']);
			$page['evil_blog']['blog_description'] = get_user_language_desc($page['evil_blog']['blog_description']);
			}
		
		//вызываем добавление пункта меню
		add_event_handler('blockmanager_apply', 'add_blog_to_menu');
	
	//RSS
		if ((!empty($tokens[1])) and ($tokens[1]=="feed")) { 
			include_once (Evil_Blog_PATH.'feed.php');
			}
	
	if (((empty($tokens[0])) and ($page['evil_blog']['show_on_main_page'])) or
		(($tokens[0]=='categories') and ($page['evil_blog']['show_on_category_page'])) or 
		(($tokens[0]=='blog') and ((empty($tokens[1]) or ($tokens[1]!='id'))))) {
			include_once (Evil_Blog_PATH.'blog.php');
		}else{
			if ( ($tokens[0]=='blog') and (!empty($tokens[1])) and ($tokens[1]=='id') and (!empty($tokens[2])) ) {
				include_once (Evil_Blog_PATH.'post.php');
			}
		}
}

//описываем действия во время собырия loc_begin_index, ну тобишь выводим плагин на странице
add_event_handler('loc_begin_index', 'Evil_Blog_main');

//функция добавления пункта меню
function add_blog_to_menu($aMenuRefArray) {
	global $conf, $page;

   //хрен его знает что это, какаято проверка на существование меню
    $menu = &$aMenuRefArray[0];
    $block_mbMenu = $menu->get_block('mbMenu');
    if (null == $block_mbMenu) {
      return;
    }
    
    if (!isset($block_mbMenu->data['evil_blog'])) {
      $blog_menu = array(
          'TITLE' => $page['evil_blog']['blog_description'],
          'NAME'  => $page['evil_blog']['blog_name'],
          'URL'   => make_index_url(array('section' => 'blog')),
        );
      $block_mbMenu->data['evil_blog'] = $blog_menu;
    }
  }
 
function section_init_evil_blog() 
{
	global $page, $tokens;
	
	if ($tokens[0]=='blog') {
		$page['section'] = 'blog';
		}
}  
//инициация страницы блога, без этого на ней появляется список с категориями.	
add_event_handler('loc_end_section_init', 'section_init_evil_blog');

?>
