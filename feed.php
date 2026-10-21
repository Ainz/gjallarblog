<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');
// Retired journal feed: leave feed generation and access to Piwigo itself.
header('Location: '.get_root_url().'feed.php', true, 302);
exit;
