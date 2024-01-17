<?php
// Chech whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

	//узнаем номер страницы на которой находит пост, чтобы пользователь мог к ней вернутся через ссылку
	$row = pwg_db_fetch_assoc(pwg_query("SELECT COUNT(*) FROM `".Evil_Blog_Table."`"));
		if ($row["COUNT(*)"] % $page['evil_blog']['posts_per_page'] != 0) {
			$pages = floor($row["COUNT(*)"] / $page['evil_blog']['posts_per_page'])+1;
			} else {$pages = floor($row["COUNT(*)"] / $page['evil_blog']['posts_per_page']);}
			
	$npage = floor($tokens[2] / $page['evil_blog']['posts_per_page'])+1;
	$npage = $pages-$npage+1;
	/////
	$query = "SELECT id, UNIX_TIMESTAMP(date) AS date, header, text
				FROM `".Evil_Blog_Table."`
				WHERE `id`=".$tokens[2];
	$row = pwg_db_fetch_assoc(pwg_query($query));
	if (!empty($row)){
		$row['date'] = date($page['evil_blog']['date_format'], $row['date']); 
		if($ExDesc['state'] == 'active') {
			$row['text'] = get_extended_desc($row['text']);
			$row['header'] = get_user_language_desc($row['header']);
			}
		$template->assign('row', $row);
		$template->assign('npage', $npage);
		//отсылаем в СМАРТИ настройки внешнего вида
		$template->assign('show_number', $page['evil_blog']['show_number']);
		$template->assign('show_date', $page['evil_blog']['show_date']);
		$template->assign('show_on_main_page', $page['evil_blog']['show_on_main_page']);
		$page['title'] = '<a href="'.get_gallery_home_url().'">'.l10n('Home').'</a>'.$conf['level_separator'];
		if ( ($npage==1) and ($page['evil_blog']['show_on_main_page']) ) {
				$page['title'] .= '<a href="'.get_gallery_home_url().'">'.$page['evil_blog']['blog_name'].'</a>';
			}else{
				$page['title'] .= '<a href="'.get_root_url().'?/blog/'.$npage.'">'.$page['evil_blog']['blog_name'].'</a>';
			}
		$page['title'] .= 
			$conf['level_separator'].'<a href="'.get_root_url().'?/blog/id/'.$row['id'].'">'.$row['header'].'</a>'.
			'<a id="blog_rss_icon" href="'.get_gallery_home_url().'?/blog/feed" title="'.l10n("Notification").'"><img src="'.get_gallery_home_url().'plugins/Evil_Blog/rss.png"></a>';
		
		//определяем адрес шаблона плагина
		$template->set_template_dir(Evil_Blog_PATH);
		$template->set_filename('post', 'post.tpl');
		//подключение стиля
		$template->func_combine_css(array('path' => Evil_Blog_PATH.'posts.css',));
		
		//объявляем где конкретно на странице должен вывестись наш шаблон плагина. Вся суть в 'PLUGIN_INDEX_CONTENT_BEFORE'.
		$template->concat('PLUGIN_INDEX_CONTENT_END',  $template->parse('post', true));
	}
?>
