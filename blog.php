<?php
// Chech whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

		$post_per_page = $page['evil_blog']['posts_per_page'];
		//óçíàåì êîëè÷åñòâî çàïèñåé â áëîãå
		$row = pwg_db_fetch_assoc(pwg_query("SELECT COUNT(*) FROM `".Evil_Blog_Table."`"));

		if ($row["COUNT(*)"] % $post_per_page != 0) {
			$pages = floor($row["COUNT(*)"] / $post_per_page)+1;
			} else {$pages = floor($row["COUNT(*)"] / $post_per_page);}
		if ((empty($tokens[1])) or ($tokens[1]<=1)) {
			$number = 0;
			$npage = 1;
			} else {
				$npage = $tokens[1];
				if ($npage<=$pages) {
					$number=($npage-1)*$post_per_page;
					} else {
					$number=($pages-1)*$post_per_page;
					$npage=$pages;
					}
			}
	
		$query = 	"SELECT id, UNIX_TIMESTAMP(date) AS date, header, text
					FROM `".Evil_Blog_Table."`
					WHERE 1
					ORDER BY `date` DESC
					LIMIT ".$number.",".$post_per_page;
		$result = pwg_query($query);
		$qwe = 0;
		while ($row = pwg_db_fetch_assoc($result)) {//áîëåå âìåíÿåìàÿ îáðàáîòêà ðåçóëüòàòà èç áàçû, íóæíî äëÿ íîðìàëüíîé ðàáîòû SMARTY
			$row1[$qwe] = $row;
			$row1[$qwe]['date'] = date($page['evil_blog']['date_format'], $row['date']); 
			if($ExDesc['state'] == 'active') {
				$row1[$qwe]['text'] = get_extended_desc($row['text']);
				$row1[$qwe]['header'] = get_user_language_desc($row['header']);
				}
			$qwe++;
			}
		if (!empty($row1)) {
			$template->assign('row', $row1);
			}
		$template->assign('npage', $npage);
		$template->assign('pages', $pages);
		for ($it=1; $it<=$pages; $it++) {
			$pagi[$it] = $it;
			}
		if (!empty($pagi)) { 
			$template->assign('pagi', $pagi);
			}
		//îïðåäåëÿåì çàãîëîâîê
		if ((($page['evil_blog']['show_on_main_page']) or ($page['evil_blog']['show_on_category_page'])) and ($tokens[0]!='blog')) {
			$place = 'PLUGIN_INDEX_CONTENT_BEFORE';
			$template->assign('TITLE', 
				'<div id="content" class="content contentWithMenu"><div class="titrePage"><h2>'.$page['evil_blog']['blog_name'].
				'<a id="blog_rss_icon" href="'.get_gallery_home_url().'?/blog/feed" title="'.l10n("Notification").'"><img src="'.get_gallery_home_url().'plugins/Evil_Blog/rss.png"></a></h2></div>');
			$template->assign('TITLE_END', '</div>');
			}else{//õëåáíûå êðîøêè
			$place = 'PLUGIN_INDEX_CONTENT_END';
			$page['title'] = 
				'<a href="'.get_gallery_home_url().'">'.l10n('Home').'</a>'.$conf['level_separator'].$page['evil_blog']['blog_name'].
				'<a id="blog_rss_icon" href="'.get_gallery_home_url().'?/blog/feed" title="'.l10n("Notification").'"><img src="'.get_gallery_home_url().'plugins/Evil_Blog/rss.png"></a>';
			$template->assign('TITLE_END', ' ');
			}
		//îòñûëàåì â ÑÌÀÐÒÈ íàñòðîéêè âíåøíåãî âèäà
		$template->assign('show_number', $page['evil_blog']['show_number']);
		$template->assign('show_date', $page['evil_blog']['show_date']);
		$template->assign('show_on_main_page', $page['evil_blog']['show_on_main_page']);
		
		//îïðåäåëÿåì àäðåñ øàáëîíà ïëàãèíà
		$template->set_template_dir(Evil_Blog_PATH);
		$template->set_filename('blog', 'blog.tpl');
		//ïîäêëþ÷åíèå ñòèëÿ
		$template->func_combine_css(array('path' => Evil_Blog_PATH.'posts.css',));
		
		//îáúÿâëÿåì ãäå êîíêðåòíî íà ñòðàíèöå äîëæåí âûâåñòèñü íàø øàáëîí ïëàãèíà. Âñÿ ñóòü â 'PLUGIN_INDEX_CONTENT_BEFORE'.
		$template->concat($place,  $template->parse('blog', true));
?>
