<div class="gb-editor" data-gb-editor data-limit-message="{'Use no more than %s characters.'|translate|escape}" data-unsaved="{'You have unsaved changes.'|translate|escape}">
  <label for="gb-title-{$editor_id}">{'Title'|translate}</label>
  <input id="gb-title-{$editor_id}" type="text" name="{$title_name}" value="{$editor.header|escape:'html':'UTF-8'}" required data-max="{$GB_TITLE_LIMIT}" aria-describedby="gb-title-count-{$editor_id}{if isset($GB_ERRORS[$title_name]) && (!$editor_id || $GB_OPEN_ID==$editor_id)} gb-title-error-{$editor_id}{/if}" {if isset($GB_ERRORS[$title_name]) && (!$editor_id || $GB_OPEN_ID==$editor_id)}aria-invalid="true"{/if}>
  <span id="gb-title-count-{$editor_id}" class="gb-counter" data-count-for="gb-title-{$editor_id}">{$editor.header|count_characters:true} / {$GB_TITLE_LIMIT}</span>
  {if isset($GB_ERRORS[$title_name]) && (!$editor_id || $GB_OPEN_ID==$editor_id)}<p id="gb-title-error-{$editor_id}" class="gb-error">{$GB_ERRORS[$title_name]|escape}</p>{/if}
  <label for="gb-body-{$editor_id}">{'Content (HTML)'|translate}</label>
  <div class="gb-format-tools" role="group" aria-label="{'Insert formatting'|translate|escape}" hidden>
    <button type="button" data-format="p">{'Paragraph'|translate}</button>
    <button type="button" data-format="strong">{'Bold'|translate}</button>
    <button type="button" data-format="em">{'Italic'|translate}</button>
    <button type="button" data-format="h2">{'Heading'|translate}</button>
    <button type="button" data-format="ul">{'List'|translate}</button>
    <button type="button" data-format="blockquote">{'Quote'|translate}</button>
    <button type="button" data-format="a" data-prompt="{'Link address (https://… or a gallery path)'|translate|escape}">{'Link'|translate}</button>
  </div>
  <textarea id="gb-body-{$editor_id}" name="{$body_name}" rows="10" required data-max="{$GB_BODY_LIMIT}" aria-describedby="gb-body-count-{$editor_id} gb-body-help-{$editor_id}{if isset($GB_ERRORS[$body_name]) && (!$editor_id || $GB_OPEN_ID==$editor_id)} gb-body-error-{$editor_id}{/if}" {if isset($GB_ERRORS[$body_name]) && (!$editor_id || $GB_OPEN_ID==$editor_id)}aria-invalid="true"{/if}>{$editor.text|escape:'html':'UTF-8'}</textarea>
  <span id="gb-body-count-{$editor_id}" class="gb-counter" data-count-for="gb-body-{$editor_id}">{$editor.text|count_characters:true} / {$GB_BODY_LIMIT}</span>
  {if isset($GB_ERRORS[$body_name]) && (!$editor_id || $GB_OPEN_ID==$editor_id)}<p id="gb-body-error-{$editor_id}" class="gb-error">{$GB_ERRORS[$body_name]|escape}</p>{/if}
  <p id="gb-body-help-{$editor_id}" class="gb-help">{'Characters include HTML markup. Blank lines alone do not create paragraphs. Scripts, embedded frames and unsupported markup are removed.'|translate}</p>
  <details class="gb-help"><summary>{'Formatting help'|translate}</summary>
    <p>{'Select text and use a formatting button, or write HTML. Preview checks the result without publishing. There are no drafts in this alpha.'|translate}</p>
    <pre>&lt;p&gt;{'A paragraph of text.'|translate}&lt;/p&gt;
&lt;a href="index.php?/categories"&gt;{'Browse albums'|translate}&lt;/a&gt;
&lt;img src="_data/example.jpg" alt="{'Describe the image'|translate}"&gt;</pre>
  </details>
  <div class="gb-actions">
    <button class="gb-primary" type="submit" name="submit_post" value="1">{$submit_label|escape}</button>
    <button type="submit" name="preview" value="1">{'Preview'|translate}</button>
    {if $editor_id}<a href="{$ROOT_URL}index.php?/blog/id/{$editor_id}">{'View published entry'|translate}</a>{/if}
  </div>
  <p class="gb-help">{'Publishing or saving makes these changes visible immediately.'|translate}</p>
</div>
