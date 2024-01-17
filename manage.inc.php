<?php
// Check whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Fetch the template.
global $template, $conf, $page;
	
	if (isset($_POST['submit_post'])) {// Write new post in db
		$result = pwg_query("insert into ".Evil_Blog_Table." values (NULL, NOW() , '". $_POST['post_header']."', '".$_POST['post_textarea']."')");
		array_push($page['infos'], l10n('post_is_saved'));
		}
		
	if (!empty($_POST['del']))	{// Deleting post from db
		$result = pwg_query("DELETE FROM ".Evil_Blog_Table." WHERE ".Evil_Blog_Table.".id = ".$_POST['del']." LIMIT 1");
		array_push($page['infos'], l10n('post_is_deleted'));
		}

	if (!empty($_POST['edit_post_id'])) {
		$result = pwg_query("UPDATE ".Evil_Blog_Table." 
		SET ".Evil_Blog_Table.".header='".$_POST['edit_post_header']."', 
			".Evil_Blog_Table.".text='".$_POST['edit_post_text']."'
		WHERE ".Evil_Blog_Table.".id='".$_POST['edit_post_id']."' LIMIT 1");
		array_push($page['infos'], l10n('post_modified'));
		}
		
	$eb_conf = @unserialize($conf['evil_blog']);	
	$post_per_page = $eb_conf['posts_per_page'];
	
		$row = pwg_db_fetch_assoc(pwg_query("SELECT COUNT(*) FROM `".Evil_Blog_Table."`"));;	
		
		// $pages - number of pages in all blog
		// $npage - page on which we look
		if ($row["COUNT(*)"] % $post_per_page != 0) {
			$pages = floor($row["COUNT(*)"] / $post_per_page)+1;
			} else {$pages = floor($row["COUNT(*)"] / $post_per_page);}
		if ((empty($_POST['npage'])) or ($_POST['npage']<=1)) {
			$number = 0;
			$npage = 1;
			} else {
				$npage = $_POST['npage'];
				if ($npage<=$pages) {
					$number=($npage-1)*$post_per_page;
					} else {
					$number=($pages-1)*$post_per_page;
					$npage=$pages;
					}
			}
	
		$query = 	"SELECT *
					FROM `".Evil_Blog_Table."`
					WHERE 1
					ORDER BY `date` DESC
					LIMIT ".$number.",".$post_per_page;
		$result = pwg_query($query);
		$qwe = 0;
		while ($row = pwg_db_fetch_assoc($result)) {// UNKNOWN COMMENT
			$row1[$qwe] = $row;
			$qwe++;
			}
		if (!empty($row1)) {
			$template->assign('row', $row1);
			}
		$template->assign('npage', $npage);
		$template->assign('pages', $pages);
		for ($it=1; $it<=$pages; $it++) {// Create array with numbers of pages
			$pagi[$it] = $it;
			}
		if (!empty($pagi)) { 
			$template->assign('pagi', $pagi);
			}
		$template->assign('blog_name', $eb_conf['blog_name']);	
			
		// FCKeditor
		$FCKEA = pwg_db_fetch_assoc(pwg_query("SELECT state FROM " . PLUGINS_TABLE . " WHERE id = 'FCKEditor';"));
		if($FCKEA['state'] == 'active') set_fckeditor_instance($areas='post_textarea', $toolbar='Full', $width='900px', $height='200px');
			
$template->set_filenames( array('plugin_admin_content' => dirname(__FILE__).'/manage.tpl') );
$template->assign_var_from_handle( 'ADMIN_CONTENT', 'plugin_admin_content');

?>
