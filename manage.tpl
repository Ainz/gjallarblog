<h2>Gjallarblog · {'Entries'|translate}</h2>
<div class="gjallarblog-admin gb-theme-{$GB_ADMIN_THEME}">
  {if $GB_NOTICE}<p class="gb-notice" role="status">{$GB_NOTICE|escape}</p>{/if}
  {if $GB_ERRORS}<div class="gb-error-summary" role="alert" tabindex="-1"><p>{'Please correct the highlighted fields. Your text has been kept.'|translate}</p><ul>{foreach from=$GB_ERRORS item=error}<li>{$error|escape}</li>{/foreach}</ul></div>{/if}
  <nav class="gb-shortcuts" aria-label="{'Journal administration'|translate|escape}"><a href="#published-entries">{'Jump to published entries'|translate}</a> · <a href="{$GB_JOURNAL|escape}">{'View journal'|translate}</a></nav>
  <details class="gb-new-entry" {if $GB_NEW_OPEN}open{/if}>
    <summary>{'New journal entry'|translate}</summary>
    <form method="post" action="{$GB_ADMIN|escape}" data-gb-form>
      <input type="hidden" name="pwg_token" value="{$PWG_TOKEN|escape}">
      {include file=$GB_EDITOR_TEMPLATE editor=$GB_NEW editor_id=0 title_name='post_header' body_name='post_textarea' submit_label='Publish entry'|translate}
    </form>
  </details>
  {if $GB_PREVIEW}<section class="gb-preview" id="gb-preview" tabindex="-1"><h3>{'Preview — not published'|translate}</h3><p>{'Content preview; the gallery theme supplies the final page styling.'|translate}</p><iframe title="{'Entry preview'|translate|escape}" sandbox="" referrerpolicy="no-referrer" srcdoc="{$GB_PREVIEW|escape:'html':'UTF-8'}"></iframe></section>{/if}
  <h3 id="published-entries" tabindex="-1">{'Published entries'|translate}</h3>
  <p class="gb-help">{'Times use the gallery server timezone:'|translate} {$GB_TIMEZONE|escape}</p>
  {foreach from=$GB_POSTS item=entry}
    <details class="gjallarblog-edit" id="entry-{$entry.id}" {if $GB_OPEN_ID==$entry.id}open{/if}>
      <summary><span class="gb-edit-cue">{'Edit'|translate}</span> {$entry.header|escape:'html':'UTF-8'} <small> · {$entry.display_date|escape}</small></summary>
      <form method="post" action="{$GB_ADMIN|escape}" data-gb-form>
        <input type="hidden" name="pwg_token" value="{$PWG_TOKEN|escape}">
        <input type="hidden" name="edit_post_id" value="{$entry.id}">
        {include file=$GB_EDITOR_TEMPLATE editor=$entry editor_id=$entry.id title_name='edit_post_header' body_name='edit_post_text' submit_label='Save changes'|translate}
      </form>
      <form class="gb-delete-form" method="post" action="{$GB_ADMIN|escape}" data-confirm="{$entry.delete_confirm|escape:'html':'UTF-8'}">
        <input type="hidden" name="pwg_token" value="{$PWG_TOKEN|escape}">
        <input type="hidden" name="del" value="{$entry.id}">
        <label><input type="checkbox" required name="confirm_delete" value="1"> {'I understand that deletion is permanent.'|translate}</label>
        <button class="gb-danger" type="submit">{'Delete entry permanently'|translate}</button>
      </form>
    </details>
  {foreachelse}<p>{'No entries yet.'|translate}</p>{/foreach}
  {if $GB_PAGE_COUNT>1}<nav class="gjallarblog-pagination" aria-label="{'Entry list pages'|translate|escape}">{foreach from=$GB_PAGES item=p}{if $p==$GB_PAGE}<span aria-current="page" aria-label="{'Page'|translate|escape} {$p}">{$p}</span>{else}<a href="{$GB_ADMIN|regex_replace:'/&npage=[0-9]+/':''|escape}&amp;npage={$p}#published-entries" aria-label="{'Page'|translate|escape} {$p}">{$p}</a>{/if}{/foreach}</nav>{/if}
</div>
