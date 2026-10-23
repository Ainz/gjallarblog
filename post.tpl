<article class="gjallarblog-journal gjallarblog-post gjallarblog-single">
  {if $show_date}<div class="gjallarblog-meta">{$row.date|escape:'html':'UTF-8'}</div>{/if}
  <h1>{$row.header|escape:'html':'UTF-8'}</h1>
  <div class="post_description">{$row.text}</div>
  <nav class="gjallarblog-entry-navigation" aria-label="{'Entry navigation'|translate|escape}">
    {if $older}<a rel="prev" href="{$ROOT_URL}?/blog/id/{$older.id}">← {'Previous entry'|translate}</a>{else}<span></span>{/if}
    <a class="gjallarblog-back" href="{$ROOT_URL}?/blog/{$npage}">{$GB_BACK_LABEL|escape:'html':'UTF-8'}</a>
    {if $newer}<a rel="next" href="{$ROOT_URL}?/blog/id/{$newer.id}">{'Next entry'|translate} →</a>{else}<span></span>{/if}
  </nav>
</article>
