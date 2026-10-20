<div class="titrePage"><h2>Gjallarblog</h2></div>
<div class="gjallarblog-admin">
<form method="post" action="{$GB_ADMIN|escape:'html':'UTF-8'}">
  <input type="hidden" name="pwg_token" value="{$PWG_TOKEN}">
  <fieldset><legend>New journal entry</legend>
    <label>Title<input type="text" name="post_header" maxlength="255" required></label>
    <label>Content<textarea name="post_textarea" rows="8" required></textarea></label>
    <p>HTML is supported for trusted gallery administrators.</p>
    <button type="submit" name="submit_post" value="1">Publish entry</button>
  </fieldset>
</form>
<h3>Published entries</h3>
{foreach from=$GB_POSTS item=entry}
<details class="gjallarblog-edit"><summary>{$entry.header|escape:'html':'UTF-8'} <small> · {$entry.date|escape:'html':'UTF-8'}</small></summary>
  <form method="post" action="{$GB_ADMIN|escape:'html':'UTF-8'}">
    <input type="hidden" name="pwg_token" value="{$PWG_TOKEN}">
    <input type="hidden" name="edit_post_id" value="{$entry.id}">
    <label>Title<input type="text" name="edit_post_header" maxlength="255" value="{$entry.header|escape:'html':'UTF-8'}" required></label>
    <label>Content<textarea name="edit_post_text" rows="8">{$entry.text|escape:'html':'UTF-8'}</textarea></label>
    <button type="submit">Save changes</button>
  </form>
  <form method="post" action="{$GB_ADMIN|escape:'html':'UTF-8'}" onsubmit="return confirm('Delete this entry?');">
    <input type="hidden" name="pwg_token" value="{$PWG_TOKEN}"><input type="hidden" name="del" value="{$entry.id}">
    <button type="submit">Delete entry</button>
  </form>
</details>
{foreachelse}<p>No entries yet.</p>{/foreach}
<nav class="navigationBar">{foreach from=$GB_PAGES item=num}{if $num==$GB_PAGE}<strong>{$num}</strong>{else}<a href="{$GB_ADMIN|escape:'html':'UTF-8'}&amp;npage={$num}">{$num}</a>{/if} {/foreach}</nav>
</div>
