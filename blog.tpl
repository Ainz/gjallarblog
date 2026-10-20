<section class="gjallarblog-journal" aria-label="{$GB_NAME|escape:'html':'UTF-8'}">
  <header class="gjallarblog-heading">
    <div><span class="gjallarblog-kicker">GALLERY JOURNAL</span><h2>{$GB_NAME|escape:'html':'UTF-8'}</h2><p>{$GB_DESCRIPTION|escape:'html':'UTF-8'}</p></div>
    <a class="gjallarblog-feed" href="{$GB_FEED|escape:'html':'UTF-8'}">RSS feed</a>
  </header>
  <ul class="gjallarblog_posts">
  {foreach from=$row item=value}
    <li class="gjallarblog-post">
      <div class="gjallarblog-meta">{if $show_number}No. {$value.id} · {/if}{if $show_date}{$value.date|escape:'html':'UTF-8'}{/if}</div>
      <h3><a href="{$ROOT_URL}?/blog/id/{$value.id}">{$value.header|escape:'html':'UTF-8'}</a></h3>
      <div class="post_description">{$value.text}</div>
      <a class="gjallarblog-permalink" href="{$ROOT_URL}?/blog/id/{$value.id}">Open entry →</a>
    </li>
  {foreachelse}<li class="gjallarblog-post">The journal is ready for its first entry.</li>{/foreach}
  </ul>
  {if $pages>1}<nav class="navigationBar gjallarblog-pagination" aria-label="Journal pages">{foreach from=$pagi item=v}{if $v==$npage}<span class="pageNumberSelected">{$v}</span>{else}<a href="{$ROOT_URL}?/blog/{$v}">{$v}</a>{/if}{/foreach}</nav>{/if}
</section>
