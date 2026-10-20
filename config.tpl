<div class="titrePage"><h2>Gjallarblog · Settings</h2></div>
<form class="gjallarblog-admin" method="post" action="{$GB_ADMIN|escape:'html':'UTF-8'}">
  <input type="hidden" name="pwg_token" value="{$PWG_TOKEN}">
  <fieldset><legend>Journal</legend>
    <label>Name<input name="blog_name" maxlength="255" value="{$GB_SETTINGS.blog_name|escape:'html':'UTF-8'}" required></label>
    <label>Menu label<input name="menu_label" maxlength="255" value="{$GB_SETTINGS.menu_label|escape:'html':'UTF-8'}"></label>
    <label>Description<input name="blog_description" maxlength="255" value="{$GB_SETTINGS.blog_description|escape:'html':'UTF-8'}"></label>
    <label>Entries per page<input type="number" name="posts_per_page" min="1" max="100" value="{$GB_SETTINGS.posts_per_page}" required></label>
    <label>Date format<input name="date_format" maxlength="80" value="{$GB_SETTINGS.date_format|escape:'html':'UTF-8'}"></label>
    <label><input type="checkbox" name="show_on_main_page" {if $GB_SETTINGS.show_on_main_page}checked{/if}> Show on gallery home</label>
    <label><input type="checkbox" name="show_on_category_page" {if $GB_SETTINGS.show_on_category_page}checked{/if}> Show on album pages</label>
    <label><input type="checkbox" name="show_date" {if $GB_SETTINGS.show_date}checked{/if}> Show dates</label>
    <label><input type="checkbox" name="show_number" {if $GB_SETTINGS.show_number}checked{/if}> Show entry numbers</label>
    <button type="submit" name="submit_config" value="1">Save settings</button>
  </fieldset>
</form>
