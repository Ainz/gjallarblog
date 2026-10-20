<article class="gjallarblog-journal gjallarblog-post gjallarblog-single">
  <div class="gjallarblog-meta">{if $show_number}No. {$row.id} · {/if}{if $show_date}{$row.date|escape:'html':'UTF-8'}{/if}</div>
  <h2>{$row.header|escape:'html':'UTF-8'}</h2>
  <div class="post_description">{$row.text}</div>
  <a class="gjallarblog-permalink" href="{$ROOT_URL}?/blog/{$npage}">← Back to the journal</a>
</article>
