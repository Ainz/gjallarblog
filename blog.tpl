	{$TITLE}
		<ul class="evil_blog_posts">
			{foreach from=$row item=value} 
				<li class="thumbnailCategory">
						<span class="post_title">
							<span class="post_number">
								{if isset($show_number) and $show_number}{$value.id} * {/if} 
							</span>
							<span class="post_header">
								<a href="{$ROOT_URL}?/blog/id/{$value.id}">{$value.header}</a>
							</span>
							<span class="post_date">
								{if isset($show_date) and $show_date}{$value.date}{/if}
							</span>
						</span>
						<div class="post_description">
							{$value.text}
						</div>
				</li>
			{/foreach}
		</ul>
		
		{if $pages>1}
		<div class="navigationBar">
			{if $npage!=1}
				{if $show_on_main_page}
					<a href="{$ROOT_URL}?/" rel="first">{'First'|@translate}</a> |
					{else}
					<a href="{$ROOT_URL}?/blog/1" rel="first">{'First'|@translate}</a> |
					{/if}
				{if $show_on_main_page and $npage==2}
					<a href="{$ROOT_URL}?/" rel="prev">{'Previous'|@translate}</a> |
					{else}
					<a href="{$ROOT_URL}?/blog/{$npage-1}" rel="prev">{'Previous'|@translate}</a> |
					{/if}
				{/if}
			{foreach from=$pagi item=v}
				{if $v!=$npage}
					<a class='blog_pagination' href="{$ROOT_URL}?/blog/{$v}">{$v}</a> |
					{else}
					<span class='pageNumberSelected'>{$v}</span> |
					{/if}
			{/foreach}
			{if $npage!=$pages}
				<a href="{$ROOT_URL}?/blog/{$npage+1}" rel="next">{'Next'|@translate}</a> |
				<a href="{$ROOT_URL}?/blog/{$pages}" rel="last">{'Last'|@translate}</a>
				{/if}
		</div>
		{/if}
{$TITLE_END}
