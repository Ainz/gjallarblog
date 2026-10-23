<h2>Gjallarblog · {'Remove plugin'|translate}</h2>
<form method="post" action="{$GB_ADMIN|escape}" class="gjallarblog-admin gb-theme-{$GB_ADMIN_THEME}">
  <input type="hidden" name="pwg_token" value="{$PWG_TOKEN|escape}">
  <input type="hidden" name="entry_count" value="{$GB_POST_COUNT}">
  {if $GB_ERRORS}<div class="gb-error-summary" role="alert"><ul>{foreach from=$GB_ERRORS item=error}<li>{$error|escape}</li>{/foreach}</ul></div>{/if}
  <fieldset><legend>{'Review before removing'|translate}</legend>
    <p>{'Piwigo Restore uninstalls and reactivates the plugin without deleting its folder. Gjallarblog preserves entries, images and settings during Restore. Remove plugin below deletes the plugin folder; choose whether to keep or permanently erase that data.'|translate}</p>
    <p>{'Stored entries:'|translate} <strong>{$GB_POST_COUNT}</strong>.</p>
    <p>{'Stored journal images:'|translate} <strong>{$GB_MEDIA_COUNT}</strong>.</p>
    <p>{'Removing the plugin disables the journal and deletes its plugin folder. Gallery photos, albums and site branding are not removed.'|translate}</p>
    <label class="gb-check"><input type="radio" name="data_action" value="keep" {if $GB_DATA_ACTION!='erase'}checked{/if}> {'Keep journal entries, images and settings for reinstalling (recommended)'|translate}</label>
    <label class="gb-check"><input type="radio" name="data_action" value="erase" {if $GB_DATA_ACTION=='erase'}checked{/if}> {'Permanently erase journal entries, images and settings'|translate}</label>
    <p class="gb-help" id="gb-erase-help">{'Permanent erasure cannot be undone. Back up the journal first. The data is erased only after the plugin folder has been removed successfully.'|translate}</p>
    <label for="gb-erase-name">{'If erasing data, type Gjallarblog'|translate}</label>
    <input type="text" id="gb-erase-name" name="erase_name" autocomplete="off" aria-describedby="gb-erase-help">
    <label class="gb-check"><input type="checkbox" name="confirm_remove" value="1" required> {'I have reviewed the entry count and data choice and want to remove this plugin.'|translate}</label>
    <div class="gb-actions"><button type="submit" class="gb-danger">{'Remove plugin'|translate}</button><a href="{$GB_SETTINGS_URL|escape}">{'Cancel and return to settings'|translate}</a></div>
  </fieldset>
</form>
