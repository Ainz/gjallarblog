		<div class="titrePage">
			<h2>{'Customize'|@translate} Evil Blog</h2>
		</div>
		<form name="config_form" action="#" method="POST">
			<input type="checkbox" name="modified">
			<fieldset>
			<table>
				<tr>
					<td><b>{'blog_name'|@translate}</b></td>
					<td><input type="text" name="blog_name" value="{$blog_name}" onChange="document.config_form.modified.checked=true;"></td>
				</tr>
				<tr>
					<td><b>{'blog_description'|@translate}</b></td>
					<td><input type="text" name="blog_description" value="{$blog_description}" onChange="document.config_form.modified.checked=true;"></td>
				</tr>
				<tr>
					<td><b>{'posts_per_pages'|@translate}</b></td>
					<td><input type="text" name="posts_per_page" value="{$posts_per_page}" onChange="document.config_form.modified.checked=true;"></td>
				</tr>
				<tr>
					<td><b>{'show_on_main_page'|@translate}</b></td>
					<td><input type="checkbox" name="show_on_main_page" {if isset($show_on_main_page) and $show_on_main_page}checked="checked"{/if} onChange="document.config_form.modified.checked=true;"></td>
				</tr>
				<tr>
					<td><b>{'show_on_category_page'|@translate}</b></td>
					<td><input type="checkbox" name="show_on_category_page" {if isset($show_on_category_page) and $show_on_category_page}checked="checked"{/if} onChange="document.config_form.modified.checked=true;"></td>
				</tr>
				<tr><td colspan="2">
				<fieldset><legend>{'post_appearance'|@translate}</legend>
					<table>
					<tr>
						<td><b>{'show_number'|@translate}</b></td>
						<td><input type="checkbox" name="show_number" {if isset($show_number) and $show_number}checked="checked"{/if} onChange="document.config_form.modified.checked=true;"></td>
					</tr>
					<tr>
						<td><b>{'show_date'|@translate}</b></td>
						<td><input type="checkbox" name="show_date" {if isset($show_date) and $show_date}checked="checked"{/if} onChange="document.config_form.modified.checked=true;"></td>
					</tr>
					<tr>
						<td><b>{'date_format'|@translate}</b></td>
						<td>
							<input type="text" name="date_format" value="{$date_format}" onChange="document.config_form.modified.checked=true;"> 
							<a href='http://php.net/manual/ru/function.date.php'>?</a>
						</td>
					</tr>
					</table>
				</fieldset>
				</td></tr>
				<tr>
					<td><input type="submit" name="submit_config" value="{'Submit'|@translate}"></td>
				</tr>
			</table>
			</fieldset>
		</form>
