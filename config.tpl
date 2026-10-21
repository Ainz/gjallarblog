<h2>Gjallarblog · {'Settings'|translate}</h2>
<form method="post" action="{$GB_ADMIN|escape}" class="gjallarblog-admin gb-theme-{$GB_ADMIN_THEME}" data-gb-form data-limit-message="{'Use no more than %s characters.'|translate|escape}">
  <input type="hidden" name="pwg_token" value="{$PWG_TOKEN|escape}">
  {if $GB_NOTICE}<p class="gb-notice" role="status">{$GB_NOTICE|escape}</p>{/if}
  {if $GB_ERRORS}<div class="gb-error-summary" role="alert" tabindex="-1"><p>{'Please correct the highlighted fields. Your text has been kept.'|translate}</p><ul>{foreach from=$GB_ERRORS item=error}<li>{$error|escape}</li>{/foreach}</ul></div>{/if}
  <fieldset><legend>{'Journal'|translate}</legend>
    {foreach from=['blog_name'=>'Journal title','menu_label'=>'Menu label','blog_description'=>'Description','date_format'=>'Date format'] key=field item=label}
      <label for="gb-{$field}">{$label|translate}</label>
      <input type="text" id="gb-{$field}" name="{$field}" value="{$GB_SETTINGS[$field]|escape:'html':'UTF-8'}" data-max="{if $field=='date_format'}80{else}255{/if}" {if $field=='blog_name'||$field=='date_format'}required{/if} aria-describedby="gb-help-{$field}{if isset($GB_ERRORS[$field])} gb-error-{$field}{/if}" {if isset($GB_ERRORS[$field])}aria-invalid="true"{/if}>
      {if isset($GB_ERRORS[$field])}<p id="gb-error-{$field}" class="gb-error">{$GB_ERRORS[$field]|escape}</p>{/if}
      <p id="gb-help-{$field}" class="gb-help">
        {if $field=='blog_name'}{'Shown at the top of the journal. Up to 255 characters.'|translate}
        {elseif $field=='menu_label'}{'Shown in gallery navigation. Leave blank to use the journal title.'|translate}
        {elseif $field=='blog_description'}{'Shown below the journal title. Up to 255 characters.'|translate}
        {else}{'Use j F Y for a localized date, or Y-m-d for a numeric date.'|translate} {'Example:'|translate} <strong>{$GB_DATE_EXAMPLE|escape}</strong>. {'Timezone:'|translate} {$GB_TIMEZONE|escape}.{/if}
      </p>
    {/foreach}
    <p><a href="{$GB_MENU_SETTINGS|escape}">{'Move or hide the Blog menu item in Configuration → Menus.'|translate}</a></p>
    <label for="gb-page-size">{'Entries per page'|translate}</label>
    <input type="number" id="gb-page-size" name="posts_per_page" min="1" max="100" value="{$GB_SETTINGS.posts_per_page|escape}" required {if isset($GB_ERRORS.posts_per_page)}aria-invalid="true" aria-describedby="gb-error-page-size"{/if}>
    {if isset($GB_ERRORS.posts_per_page)}<p class="gb-error" id="gb-error-page-size">{$GB_ERRORS.posts_per_page|escape}</p>{/if}
    <label class="gb-check"><input type="checkbox" name="show_on_main_page" {if $GB_SETTINGS.show_on_main_page}checked{/if}> {'Show recent entries above the gallery home content'|translate}</label>
    <label class="gb-check"><input type="checkbox" name="show_on_category_page" {if $GB_SETTINGS.show_on_category_page}checked{/if}> {'Show recent entries above album content'|translate}</label>
    <p class="gb-help">{'Full entries appear before the photos or albums. Long entries increase scrolling. These options do not hide the Blog menu item.'|translate}</p>
    <label class="gb-check"><input type="checkbox" name="show_date" {if $GB_SETTINGS.show_date}checked{/if}> {'Show dates'|translate}</label>
    <label class="gb-check"><input type="checkbox" name="show_number" {if $GB_SETTINGS.show_number}checked{/if}> {'Show entry numbers'|translate}</label>
    <p class="gb-help">{'Entry numbers are permanent identifiers, not a reading order.'|translate}</p>
    <button class="gb-primary" type="submit" name="submit_config" value="1">{'Save settings'|translate}</button>
  </fieldset>
</form>
