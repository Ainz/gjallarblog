<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');
require_once __DIR__.'/include/functions.inc.php';
class gjallarblog_maintain extends PluginMaintain
{
    function install($plugin_version, &$errors=[])
    {
        global $prefixeTable,$conf;
        gjallarblog_query('CREATE TABLE IF NOT EXISTS '.$prefixeTable.'gjallarblog (id INT UNSIGNED NOT NULL AUTO_INCREMENT,date DATETIME NOT NULL,header VARCHAR(255) NOT NULL,text LONGTEXT NOT NULL,PRIMARY KEY(id),KEY chronological(date,id)) DEFAULT CHARACTER SET utf8mb4');
        conf_update_param('gjallarblog',json_encode(gjallarblog_settings($conf['gjallarblog']??null),JSON_THROW_ON_ERROR),true,'pwg_db_real_escape_string');
    }
    function activate($plugin_version, &$errors=[]) { $this->install($plugin_version,$errors); }
    function update($old_version,$new_version,&$errors=[]) { $this->install($new_version,$errors); }
    // Disabling/uninstalling the code preserves journal data. Purge/import is a separate future operation.
    function deactivate() {}
    function uninstall() {}
}
