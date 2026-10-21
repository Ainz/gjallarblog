<section class="gjallarblog-journal" aria-label="{$GB_NAME|escape:'html':'UTF-8'}">
  <header class="gjallarblog-heading">
    <div><span class="gjallarblog-kicker">{'Gallery journal'|translate}</span>
    {if $GB_EMBEDDED}<h2>{$GB_NAME|escape:'html':'UTF-8'}</h2>{else}<h1>{$GB_NAME|escape:'html':'UTF-8'}</h1>{/if}
    <p>{$GB_DESCRIPTION|escape:'html':'UTF-8'}</p></div>
  </header>
  <ul class="gjallarblog_posts">
  {foreach from=$row item=value}
    <li class="gjallarblog-post">
      {if $show_number||$show_date}<div class="gjallarblog-meta">{if $show_number}{'Entry'|translate} {$value.id}{if $show_date} · {/if}{/if}{if $show_date}{$value.date|escape:'html':'UTF-8'}{/if}</div>{/if}
      {if $GB_EMBEDDED}<h3>{else}<h2>{/if}<a href="{$ROOT_URL}?/blog/id/{$value.id}">{$value.header|escape:'html':'UTF-8'}</a>{if $GB_EMBEDDED}</h3>{else}</h2>{/if}
      <div class="post_description">{$value.text}</div>
      <a class="gjallarblog-permalink" href="{$ROOT_URL}?/blog/id/{$value.id}" aria-label="{$value.open_label|escape:'html':'UTF-8'}">{'Open entry'|translate} →</a>
    </li>
  {foreachelse}<li class="gjallarblog-post">{'No journal entries have been published yet.'|translate}</li>{/foreach}
  </ul>
  {if $pages>1}<nav class="navigationBar gjallarblog-pagination" aria-label="{'Journal pages'|translate|escape}">{foreach from=$pagi item=v}{if $v==$npage}<span class="pageNumberSelected" aria-current="page" aria-label="{'Page'|translate|escape} {$v}">{$v}</span>{else}<a href="{$ROOT_URL}?/blog/{$v}" aria-label="{'Page'|translate|escape} {$v}">{$v}</a>{/if}{/foreach}</nav>{/if}
</section>
