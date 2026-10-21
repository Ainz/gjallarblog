<h2>Gjallarblog · {'Settings'|translate}</h2>
<form method="post" action="{$GB_ADMIN|escape}" class="gjallarblog-admin gb-theme-{$GB_ADMIN_THEME}" data-gb-form data-limit-message="{'Use no more than %s characters.'|translate|escape}">
  <input type="hidden" name="pwg_token" value="{$PWG_TOKEN|escape}">
  {if $GB_NOTICE}<p class="gb-notice" role="status">{$GB_NOTICE|escape}</p>{/if}
  {if $GB_ERRORS}<div class="gb-error-summary" role="alert" tabindex="-1"><p>{'Please correct the highlighted fields. Your text has been kept.'|translate}</p><ul>{foreach from=$GB_ERRORS item=error}<li>{$error|escape}</li>{/foreach}</ul></div>{/if}
  <fieldset><legend>{'Journal identity'|translate}</legend>
    <label for="gb-blog_name">{'Journal title'|translate}</label>
    <input type="text" id="gb-blog_name" name="blog_name" value="{$GB_SETTINGS.blog_name|escape:'html':'UTF-8'}" data-max="255" required aria-describedby="gb-help-blog_name{if isset($GB_ERRORS.blog_name)} gb-error-blog_name{/if}" {if isset($GB_ERRORS.blog_name)}aria-invalid="true"{/if}>
    {if isset($GB_ERRORS.blog_name)}<p id="gb-error-blog_name" class="gb-error">{$GB_ERRORS.blog_name|escape}</p>{/if}
    <p id="gb-help-blog_name" class="gb-help">{'Shown at the top of the journal. Up to 255 characters.'|translate}</p>
    <label for="gb-blog_description">{'Description'|translate}</label>
    <input type="text" id="gb-blog_description" name="blog_description" value="{$GB_SETTINGS.blog_description|escape:'html':'UTF-8'}" data-max="255"  aria-describedby="gb-help-blog_description{if isset($GB_ERRORS.blog_description)} gb-error-blog_description{/if}" {if isset($GB_ERRORS.blog_description)}aria-invalid="true"{/if}>
    {if isset($GB_ERRORS.blog_description)}<p id="gb-error-blog_description" class="gb-error">{$GB_ERRORS.blog_description|escape}</p>{/if}
    <p id="gb-help-blog_description" class="gb-help">{'Shown below the journal title. Up to 255 characters.'|translate}</p>
  </fieldset>
  <fieldset><legend>{'Layout and placement'|translate}</legend>
    <label for="gb-page-size">{'Entries per page'|translate}</label>
    <input type="number" id="gb-page-size" name="posts_per_page" min="1" max="100" value="{$GB_SETTINGS.posts_per_page|escape}" required aria-describedby="gb-help-page-size{if isset($GB_ERRORS.posts_per_page)} gb-error-page-size{/if}" {if isset($GB_ERRORS.posts_per_page)}aria-invalid="true"{/if}>
    {if isset($GB_ERRORS.posts_per_page)}<p class="gb-error" id="gb-error-page-size">{$GB_ERRORS.posts_per_page|escape}</p>{/if}
    <p class="gb-help" id="gb-help-page-size">{'From 1 to 100. Also controls how many recent entries appear on home and album pages. Full entries are shown; long entries increase scrolling.'|translate}</p>
    <label for="gb-home_display">{'Gallery home'|translate}</label>
    <select id="gb-home_display" name="home_display" aria-describedby="gb-help-home_display{if isset($GB_ERRORS.home_display)} gb-error-home_display{/if}" {if isset($GB_ERRORS.home_display)}aria-invalid="true"{/if}>
      {foreach from=['hidden'=>'Hidden','before'=>'Before content','after'=>'After content'] key=value item=label}
      <option value="{$value}" {if $GB_SETTINGS.home_display==$value}selected{/if}>{$label|translate}</option>
      {/foreach}
    </select>
    {if isset($GB_ERRORS.home_display)}<p class="gb-error" id="gb-error-home_display">{$GB_ERRORS.home_display|escape}</p>{/if}
    <p class="gb-help" id="gb-help-home_display">{'Place recent journal entries before or after the home page albums. Hidden removes them from this page only.'|translate}</p>
    <label for="gb-album_display">{'Album pages'|translate}</label>
    <select id="gb-album_display" name="album_display" aria-describedby="gb-help-album_display{if isset($GB_ERRORS.album_display)} gb-error-album_display{/if}" {if isset($GB_ERRORS.album_display)}aria-invalid="true"{/if}>
      {foreach from=['hidden'=>'Hidden','before'=>'Before content','after'=>'After content'] key=value item=label}
      <option value="{$value}" {if $GB_SETTINGS.album_display==$value}selected{/if}>{$label|translate}</option>
      {/foreach}
    </select>
    {if isset($GB_ERRORS.album_display)}<p class="gb-error" id="gb-error-album_display">{$GB_ERRORS.album_display|escape}</p>{/if}
    <p class="gb-help" id="gb-help-album_display">{'Place recent journal entries before or after album content. These are the same recent entries in every album, not album-specific posts.'|translate}</p>
  </fieldset>
  <fieldset><legend>{'Entry details'|translate}</legend>
    <label class="gb-check"><input type="checkbox" name="show_date" aria-describedby="gb-help-show_date" {if $GB_SETTINGS.show_date}checked{/if}> {'Show dates'|translate}</label>
    <p id="gb-help-show_date" class="gb-help">{'Show the publication date alongside each entry on journal, home and album pages.'|translate}</p>
    <label for="gb-date_format">{'Date format'|translate}</label>
    <input type="text" id="gb-date_format" name="date_format" value="{$GB_SETTINGS.date_format|escape:'html':'UTF-8'}" data-max="80" required aria-describedby="gb-help-date_format{if isset($GB_ERRORS.date_format)} gb-error-date_format{/if}" {if isset($GB_ERRORS.date_format)}aria-invalid="true"{/if}>
    {if isset($GB_ERRORS.date_format)}<p id="gb-error-date_format" class="gb-error">{$GB_ERRORS.date_format|escape}</p>{/if}
    <p id="gb-help-date_format" class="gb-help">{'Used when dates are shown. Use j F Y for a localized date, or Y-m-d for a numeric date.'|translate} {'Example:'|translate} <strong>{$GB_DATE_EXAMPLE|escape}</strong>. {'Timezone:'|translate} {$GB_TIMEZONE|escape}.</p>
    <label class="gb-check"><input type="checkbox" name="show_number" aria-describedby="gb-help-show_number" {if $GB_SETTINGS.show_number}checked{/if}> {'Show entry numbers'|translate}</label>
    <p id="gb-help-show_number" class="gb-help">{'Entry numbers are permanent identifiers, not a reading order.'|translate}</p>
  </fieldset>
  <fieldset><legend>{'Site navigation'|translate}</legend>
    <label class="gb-check"><input type="checkbox" name="show_menu" aria-describedby="gb-help-show_menu" {if $GB_SETTINGS.show_menu}checked{/if}> {'Show journal link'|translate}</label>
    <p id="gb-help-show_menu" class="gb-help">{'Show the journal menu block. Hiding it does not make entries private or disable the journal address.'|translate}</p>
    <label for="gb-menu_label">{'Menu label'|translate}</label>
    <input type="text" id="gb-menu_label" name="menu_label" value="{$GB_SETTINGS.menu_label|escape:'html':'UTF-8'}" data-max="255"  aria-describedby="gb-help-menu_label{if isset($GB_ERRORS.menu_label)} gb-error-menu_label{/if}" {if isset($GB_ERRORS.menu_label)}aria-invalid="true"{/if}>
    {if isset($GB_ERRORS.menu_label)}<p id="gb-error-menu_label" class="gb-error">{$GB_ERRORS.menu_label|escape}</p>{/if}
    <p id="gb-help-menu_label" class="gb-help">{'Shown in gallery navigation. Leave blank to use the journal title. Up to 255 characters.'|translate}</p>
    <p class="gb-help">{'The theme controls the appearance of the link. Piwigo menu visibility settings also apply.'|translate}</p>
    <p><a href="{$GB_MENU_SETTINGS|escape}">{'Position the journal link in Configuration → Menus.'|translate}</a></p>
  </fieldset>
  <button class="gb-primary" type="submit" name="submit_config" value="1">{'Save settings'|translate}</button>
</form>
<div class="gjallarblog-admin gb-theme-{$GB_ADMIN_THEME}">
  <fieldset><legend>{'Plugin removal'|translate}</legend>
    <p>{'Stored entries:'|translate} <strong>{$GB_POST_COUNT}</strong>.</p>
    <p class="gb-help">{'Deactivation hides the journal and keeps its entries and settings. Piwigo uninstall and delete actions also keep that data for reinstalling.'|translate}</p>
    {if $GB_CAN_REMOVE}<p><a href="{$GB_REMOVE|escape}">{'Review removal options…'|translate}</a></p>
    {else}<p class="gb-help">{'Only a Piwigo webmaster can remove the plugin or permanently erase its data.'|translate}</p>{/if}
  </fieldset>
</div>
