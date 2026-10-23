<h2>Gjallarblog · {'Settings'|translate}</h2>
<form method="post" action="{$GB_ADMIN|escape}" class="gjallarblog-admin gb-theme-{$GB_ADMIN_THEME}" data-gb-form data-limit-message="{'Use no more than %s characters.'|translate|escape}">
  <input type="hidden" name="pwg_token" value="{$PWG_TOKEN|escape}">
  {if $GB_NOTICE}<p class="gb-notice" role="status">{$GB_NOTICE|escape}</p>{/if}
  {if $GB_ERRORS}<div class="gb-error-summary" role="alert" tabindex="-1"><p>{'Please correct the highlighted fields. Your text has been kept.'|translate}</p><ul>{foreach from=$GB_ERRORS item=error}<li>{$error|escape}</li>{/foreach}</ul></div>{/if}
  <fieldset><legend>{'Journal identity'|translate}</legend>
    <label for="gb-blog_name">{'Journal name'|translate}</label>
    <input type="text" id="gb-blog_name" name="blog_name" value="{$GB_SETTINGS.blog_name|escape:'html':'UTF-8'}" data-max="255" required aria-describedby="gb-help-blog_name{if isset($GB_ERRORS.blog_name)} gb-error-blog_name{/if}" {if isset($GB_ERRORS.blog_name)}aria-invalid="true"{/if}>
    {if isset($GB_ERRORS.blog_name)}<p id="gb-error-blog_name" class="gb-error">{$GB_ERRORS.blog_name|escape}</p>{/if}
    <p id="gb-help-blog_name" class="gb-help">{'Used for the menu link, journal heading and return links. Up to 255 plain-text characters; HTML formatting is removed and the active theme styles it.'|translate}</p>
    <label for="gb-blog_description">{'Description'|translate}</label>
    <input type="text" id="gb-blog_description" name="blog_description" value="{$GB_SETTINGS.blog_description|escape:'html':'UTF-8'}" data-max="255"  aria-describedby="gb-help-blog_description{if isset($GB_ERRORS.blog_description)} gb-error-blog_description{/if}" {if isset($GB_ERRORS.blog_description)}aria-invalid="true"{/if}>
    {if isset($GB_ERRORS.blog_description)}<p id="gb-error-blog_description" class="gb-error">{$GB_ERRORS.blog_description|escape}</p>{/if}
    <p id="gb-help-blog_description" class="gb-help">{'Shown below the journal title. Up to 255 characters.'|translate}</p>
  </fieldset>
  <fieldset><legend>{'Layout and placement'|translate}</legend>
    <label for="gb-page-size">{'Entries per page'|translate}</label>
    <input type="number" id="gb-page-size" name="posts_per_page" min="1" max="5" value="{$GB_SETTINGS.posts_per_page|escape}" required aria-describedby="gb-help-page-size{if isset($GB_ERRORS.posts_per_page)} gb-error-page-size{/if}" {if isset($GB_ERRORS.posts_per_page)}aria-invalid="true"{/if}>
    {if isset($GB_ERRORS.posts_per_page)}<p class="gb-error" id="gb-error-page-size">{$GB_ERRORS.posts_per_page|escape}</p>{/if}
    <p class="gb-help" id="gb-help-page-size">{'Choose 1 to 5 entries. This also sets the number of full, recent entries shown on home and album pages, so a small number helps limit scrolling.'|translate}</p>
    <label for="gb-home_display">{'Gallery home'|translate}</label>
    <select id="gb-home_display" name="home_display" aria-describedby="gb-help-home_display{if isset($GB_ERRORS.home_display)} gb-error-home_display{/if}" {if isset($GB_ERRORS.home_display)}aria-invalid="true"{/if}>
      {foreach from=['hidden'=>'Hidden','before'=>'Before content','after'=>'After content'] key=value item=label}
      <option value="{$value}" {if $GB_SETTINGS.home_display==$value}selected{/if}>{$label|translate}</option>
      {/foreach}
    </select>
    {if isset($GB_ERRORS.home_display)}<p class="gb-error" id="gb-error-home_display">{$GB_ERRORS.home_display|escape}</p>{/if}
    <p class="gb-help" id="gb-help-home_display">{'Shows the latest entries on the gallery home page. Before content places them above the home-page title and albums; After content places them below the home-page content. Hidden affects this page only. The entry limit above applies.'|translate}</p>
    <label for="gb-album_display">{'Album pages'|translate}</label>
    <select id="gb-album_display" name="album_display" aria-describedby="gb-help-album_display{if isset($GB_ERRORS.album_display)} gb-error-album_display{/if}" {if isset($GB_ERRORS.album_display)}aria-invalid="true"{/if}>
      {foreach from=['hidden'=>'Hidden','before'=>'Before content','after'=>'After content'] key=value item=label}
      <option value="{$value}" {if $GB_SETTINGS.album_display==$value}selected{/if}>{$label|translate}</option>
      {/foreach}
    </select>
    {if isset($GB_ERRORS.album_display)}<p class="gb-error" id="gb-error-album_display">{$GB_ERRORS.album_display|escape}</p>{/if}
    <p class="gb-help" id="gb-help-album_display">{'Shows the same latest entries on every album page. Before content places them above the album title and thumbnails; After content places them below the album content. Hidden affects album pages only. The entry limit above applies.'|translate}</p>
  </fieldset>
  <fieldset><legend>{'Entry details'|translate}</legend>
    <label class="gb-check"><input type="checkbox" name="show_date" aria-describedby="gb-help-show_date" {if $GB_SETTINGS.show_date}checked{/if}> {'Show dates'|translate}</label>
    <p id="gb-help-show_date" class="gb-help">{'Show the publication date alongside each entry on journal, home and album pages.'|translate}</p>
    <label for="gb-date_format">{'Date format'|translate}</label>
    <select id="gb-date_format" name="date_format" aria-describedby="gb-help-date_format{if isset($GB_ERRORS.date_format)} gb-error-date_format{/if}" {if isset($GB_ERRORS.date_format)}aria-invalid="true"{/if}>
      {foreach from=['j F Y'=>'Localized: 22 October 2026','Y-m-d'=>'ISO: 2026-10-22','d/m/Y'=>'Day/month/year: 22/10/2026'] key=value item=label}
      <option value="{$value}" {if $GB_SETTINGS.date_format==$value}selected{/if}>{$label|translate}</option>
      {/foreach}
    </select>
    {if isset($GB_ERRORS.date_format)}<p id="gb-error-date_format" class="gb-error">{$GB_ERRORS.date_format|escape}</p>{/if}
    <p id="gb-help-date_format" class="gb-help">{'Current setting:'|translate} <strong>{$GB_DATE_EXAMPLE|escape}</strong>. {'Timezone:'|translate} {$GB_TIMEZONE|escape}. <a href="https://www.php.net/manual/en/datetime.format.php" target="_blank" rel="noopener noreferrer">{'Date and time format reference'|translate}</a>.</p>
  </fieldset>
  <fieldset><legend>{'Site navigation'|translate}</legend>
    <label class="gb-check"><input type="checkbox" name="show_menu" aria-describedby="gb-help-show_menu" {if $GB_SETTINGS.show_menu}checked{/if}> {'Show journal link'|translate}</label>
    <p id="gb-help-show_menu" class="gb-help">{'Show the journal menu block. Hiding it does not make entries private or disable the journal address.'|translate}</p>
    <p class="gb-help">{'The theme controls the appearance of the link. Piwigo menu visibility settings also apply.'|translate}</p>
    <p><a href="{$GB_MENU_SETTINGS|escape}">{'Position the journal link in Configuration → Menus.'|translate}</a></p>
  </fieldset>
  <button class="gb-primary" type="submit" name="submit_config" value="1">{'Save settings'|translate}</button>
</form>
