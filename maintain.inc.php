<?php
// Check whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

function plugin_install()
{
  global $prefixeTable, $conf;

  $query = 'SHOW TABLES LIKE "' . $prefixeTable . 'evil_blog"';
  $result = pwg_query($query);
  if (!pwg_db_fetch_row($result))
	{
	$query = 
		'CREATE TABLE ' . $prefixeTable . 'evil_blog (
		id INT( 1 ) NOT NULL AUTO_INCREMENT,
		date DATETIME NOT NULL,
		header VARCHAR( 255 ) NOT NULL ,
		text LONGTEXT NOT NULL ,
		PRIMARY KEY (id))
		DEFAULT CHARACTER SET utf8';
	pwg_query($query);
	}else{
	$query = 
		'SHOW FIELDS FROM"' . $prefixeTable . 'evil_blog"';
	$result = pwg_query($query);
	}
	
$config = array(
      'blog_name' => 'News',
      'posts_per_page' => 3,
	  'blog_description' => 'News Blog',
	  'show_on_main_page' => 'on',
	  'show_on_category_page' => 'on',
	  'show_number' => 'on',
	  'show_date' => 'on',
	  'date_format' => 'H.i  d/m/Y'
	);
	
if (isset($conf['evil_blog']))
  {
	$query =
	'UPDATE '.CONFIG_TABLE.' SET value = "'.pwg_db_real_escape_string(serialize($config)).'" WHERE param = "evil_blog"';
	
  } else {
    $query =
		'INSERT INTO ' . CONFIG_TABLE . ' (param,value,comment)
		VALUES ("evil_blog" , "'.pwg_db_real_escape_string(serialize($config)).'" , "Evil_Blog configuration");';
    }
	pwg_query($query);
}
 
function plugin_activate()
{
	global $prefixeTable, $conf;
	
	$query = 'SHOW TABLES LIKE "' . $prefixeTable . 'evil_blog"';
	$result = pwg_query($query);
  if (!pwg_db_fetch_row($result))
	{
	$query = 
		'CREATE TABLE ' . $prefixeTable . 'evil_blog (
		id INT( 1 ) NOT NULL AUTO_INCREMENT,
		date DATETIME NOT NULL,
		header VARCHAR( 255 ) NOT NULL ,
		text LONGTEXT NOT NULL ,
		PRIMARY KEY (id))
		DEFAULT CHARACTER SET utf8';
	pwg_query($query.';');
	}/*else{
		$result = pwg_query("SHOW FIELDS FROM " . $prefixeTable . "evil_blog;");
		$description = false;
		While ($row = pwg_db_fetch_assoc($result)) {
			if ($row['Field']=='description') {$description=true; break;}
		}
		if (!$description) {
			$result = pwg_query("ALTER TABLE " . $prefixeTable . "evil_blog CHANGE `text` `description` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
			$result = pwg_query("ALTER TABLE " . $prefixeTable . "evil_blog ADD `text` LONGTEXT NOT NULL ;");
		}
	}*/
	
$config = array(
      'blog_name' => 'News',
      'posts_per_page' => 3,
	  'blog_description' => 'News Blog',
	  'show_on_main_page' => 'on',
	  'show_on_category_page' => 'on',
	  'show_number' => 'on',
	  'show_date' => 'on',
	  'date_format' => 'H.i  d/m/Y'
	);
	
if (isset($conf['evil_blog']))
  {
	$query =
	'UPDATE '.CONFIG_TABLE.' SET value = "'.pwg_db_real_escape_string(serialize($config)).'" WHERE param = "evil_blog"';
	
  } else {
    $query =
		'INSERT INTO ' . CONFIG_TABLE . ' (param,value,comment)
		VALUES ("evil_blog" , "'.pwg_db_real_escape_string(serialize($config)).'" , "Evil_Blog configuration");';
    }
	pwg_query($query);
}
 
function plugin_uninstall()
{
  global $prefixeTable;
  pwg_query('DROP TABLE ' . $prefixeTable . 'evil_blog;');
  pwg_query('DELETE FROM ' . CONFIG_TABLE . ' WHERE '.CONFIG_TABLE.'.param="evil_blog" LIMIT 1;');
  unset($conf['evil_blog']);
}
?>
