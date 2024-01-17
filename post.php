<?php
// Check whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

	// We find out the page number on which the post is found so that the user can return to it through a link
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
		// Send appearance settings to SMARTI
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
		
		// Determine the address of the plugin template
		$template->set_template_dir(Evil_Blog_PATH);
		$template->set_filename('post', 'post.tpl');
		// Connection to style
		$template->func_combine_css(array('path' => Evil_Blog_PATH.'posts.css',));
		
		// We declare where exactly on the page our plugin template should appear. The whole point is 'PLUGIN_INDEX_CONTENT_BEFORE'.
		$template->concat('PLUGIN_INDEX_CONTENT_END',  $template->parse('post', true));
	}
?>
