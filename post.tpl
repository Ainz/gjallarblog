<ul class="evil_blog_posts">
	<li class="thumbnailCategory">
		<span class="post_title">
			<span class="post_number">
				{if isset($show_number) and $show_number}{$row.id} * {/if} 
			</span>
			<span class="post_header">
				<a href="{$ROOT_URL}?/blog/id/{$row.id}">{$row.header}</a>
			</span>
			<span class="post_date">
				{if isset($show_date) and $show_date}{$row.date}{/if}
			</span>
		</span>
		<div class="post_description">
			{$row.text}
		</div>
	</li>
</ul>
<h2>
	{if $npage==1 and $show_on_main_page}
		<a href="{$ROOT_URL}?/">
		{else}
		<a href="{$ROOT_URL}?/blog/{$npage}">
		{/if}
		{'back'|@translate}</a>
</h2>
