<article class="gjallarblog-journal gjallarblog-post gjallarblog-single">
  <a class="gjallarblog-back" href="{$ROOT_URL}?/blog/{$npage}">← {$GB_BACK_LABEL|escape:'html':'UTF-8'}</a>
  {if $show_number||$show_date}<div class="gjallarblog-meta">{if $show_number}{'Entry'|translate} {$row.id}{if $show_date} · {/if}{/if}{if $show_date}{$row.date|escape:'html':'UTF-8'}{/if}</div>{/if}
  <h1>{$row.header|escape:'html':'UTF-8'}</h1>
  <div class="post_description">{$row.text}</div>
  <a class="gjallarblog-permalink" href="{$ROOT_URL}?/blog/{$npage}">← {$GB_BACK_LABEL|escape:'html':'UTF-8'}</a>
</article>
