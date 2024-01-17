<?php
// Check whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

	include_once(PHPWG_ROOT_PATH.'include/feedcreator.class.php');
	$rss = new UniversalFeedCreator();
	$rss->encoding=get_pwg_charset();
	$rss->title = $conf['gallery_title'];
	$rss->link = get_root_url().'?/blog';
	$rss->description = $page['evil_blog']['blog_description'];
	
	
	$result = pwg_query("
		SELECT id, UNIX_TIMESTAMP(date) AS date, header, text
		FROM `".Evil_Blog_Table."`
		WHERE 1
		ORDER BY `date` DESC
	");
	while ($row = pwg_db_fetch_assoc($result)) {
		if($ExDesc['state'] == 'active') {
				$row['text'] = get_extended_desc($row['text']);
				$row['header'] = get_user_language_desc($row['header']);
				}
		$item = new FeedItem();
		$item->title = $row['header'].' '.date($page['evil_blog']['date_format'], $row['date']);
		$item->link = get_gallery_home_url().'?/blog/id/'.$row['id'];
		$item->description = $row['text'];
		$item->date = $row['date'];
		$item->descriptionHtmlSyndicated = true;
		$item->guid= sprintf('%s', $row['date']);
		$rss->addItem($item);
	}
	
	$fileName= PHPWG_ROOT_PATH.$conf['data_location'].'tmp';
	mkgetdir($fileName); // just in case
	$fileName.='/evil_blog_feed.xml';
	// send XML feed
	echo $rss->saveFeed('RSS2.0', $fileName, true);
	
	/*$item = new FeedItem();
	foreach ($item as $key => $value) {
		echo $key.' = '. $value . '<br>';
	}*/
?>
