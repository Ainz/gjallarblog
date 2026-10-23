  <div class="gb-editor" data-gb-editor data-image-label="{'Image'|translate|escape}" data-image-action-label="{'Edit image %s'|translate|escape}" data-image-edit-message="{'Editing image %s.'|translate|escape}" data-uploading-message="{'Uploading and inserting image…'|translate|escape}" data-image-inserted-message="{'Image inserted. Select it under Images in this entry to add a caption or change its layout.'|translate|escape}" data-updating-message="{'Updating image layout…'|translate|escape}" data-update-image-label="{'Save image layout'|translate|escape}" data-limit-message="{'Use no more than %s characters.'|translate|escape}" data-unsaved="{'You have unsaved changes.'|translate|escape}">
  <label for="gb-title-{$editor_id}">{'Title'|translate}</label>
  <input id="gb-title-{$editor_id}" type="text" name="{$title_name}" value="{$editor.header|escape:'html':'UTF-8'}" required data-max="{$GB_TITLE_LIMIT}" aria-describedby="gb-title-count-{$editor_id}{if isset($GB_ERRORS[$title_name]) && (!$editor_id || $GB_OPEN_ID==$editor_id)} gb-title-error-{$editor_id}{/if}" {if isset($GB_ERRORS[$title_name]) && (!$editor_id || $GB_OPEN_ID==$editor_id)}aria-invalid="true"{/if}>
  <span id="gb-title-count-{$editor_id}" class="gb-counter" data-count-for="gb-title-{$editor_id}">{$editor.header|count_characters:true} / {$GB_TITLE_LIMIT}</span>
  {if isset($GB_ERRORS[$title_name]) && (!$editor_id || $GB_OPEN_ID==$editor_id)}<p id="gb-title-error-{$editor_id}" class="gb-error">{$GB_ERRORS[$title_name]|escape}</p>{/if}
  <p class="gb-help">{'Entry titles are plain text. HTML formatting is removed; the active theme styles the title.'|translate}</p>
  <label for="gb-body-{$editor_id}">{'Content (HTML)'|translate}</label>
  <div class="gb-format-tools" role="group" aria-label="{'Insert formatting'|translate|escape}" hidden>
    <button type="button" data-format="p">{'Paragraph'|translate}</button>
    <button type="button" data-format="strong">{'Bold'|translate}</button>
    <button type="button" data-format="em">{'Italic'|translate}</button>
    <button type="button" data-format="h2">{'Heading'|translate}</button>
    <button type="button" data-format="ul">{'List'|translate}</button>
    <button type="button" data-format="blockquote">{'Quote'|translate}</button>
    <button type="button" data-image-open>{'Insert image'|translate}</button>
    <button type="button" data-format="a" data-prompt="{'Link address (https://… or a gallery path)'|translate|escape}">{'Link'|translate}</button>
  </div>
  <input type="file" accept="image/png,image/jpeg,image/webp" data-image-file hidden>
  <dialog class="gb-image-tools" data-image-tools aria-label="{'Image layout controls'|translate|escape}" aria-describedby="gb-image-dialog-help-{$editor_id}">
    <div class="gb-image-dialog-heading"><strong>{'Image layout'|translate}</strong><button type="button" class="gb-image-close" data-image-cancel aria-label="{'Close image layout controls'|translate|escape}">&times;</button></div>
    <p id="gb-image-dialog-help-{$editor_id}" class="gb-help">{'The image is already in the entry. Adjust its alternative text, caption, placement or width here.'|translate}</p>
    <p class="gb-help">{'Images are separate blocks by default so text does not wrap unexpectedly. Inline images are opt-in.'|translate}</p>
    <img class="gb-image-preview" data-image-preview alt="" hidden>
    <label>{'Alternative text'|translate}<input type="text" data-image-alt maxlength="255"></label>
    <label>{'Caption (optional)'|translate}<input type="text" data-image-caption maxlength="255"></label>
    <label>{'Placement'|translate}<select data-image-layout><option value="center">{'Own row · centered'|translate}</option><option value="left">{'Own row · left'|translate}</option><option value="right">{'Own row · right'|translate}</option><option value="inline">{'Inline with text'|translate}</option></select></label>
    <label data-image-size-label>{'Image width'|translate}<select data-image-size><option value="small">{'Small'|translate}</option><option value="medium" selected>{'Medium'|translate}</option><option value="full">{'Full width'|translate}</option></select></label>
    <div class="gb-actions"><button type="button" data-image-insert>{'Save image layout'|translate}</button><button type="button" data-image-cancel>{'Cancel'|translate}</button></div>
    <p class="gb-image-status" data-image-status role="status" aria-live="polite"></p>
  </dialog>
  <label for="gb-date-{$editor_id}">{'Publication date and time'|translate}</label>
  <input id="gb-date-{$editor_id}" type="datetime-local" name="{$date_name}" value="{$editor.input_date|escape:'html':'UTF-8'}" max="{$GB_NOW|escape}" required aria-describedby="gb-date-help-{$editor_id}{if isset($GB_ERRORS[$date_name]) && (!$editor_id || $GB_OPEN_ID==$editor_id)} gb-date-error-{$editor_id}{/if}" {if isset($GB_ERRORS[$date_name]) && (!$editor_id || $GB_OPEN_ID==$editor_id)}aria-invalid="true"{/if}>
  <p id="gb-date-help-{$editor_id}" class="gb-help">{'Publication time uses the gallery timezone:'|translate} {$GB_TIMEZONE|escape}. {'Choose a past date to republish older news; future dates are not supported.'|translate}</p>
  {if isset($GB_ERRORS[$date_name]) && (!$editor_id || $GB_OPEN_ID==$editor_id)}<p id="gb-date-error-{$editor_id}" class="gb-error">{$GB_ERRORS[$date_name]|escape}</p>{/if}
  <textarea id="gb-body-{$editor_id}" name="{$body_name}" rows="10" required data-max="{$GB_BODY_LIMIT}" aria-describedby="gb-body-count-{$editor_id} gb-body-help-{$editor_id}{if isset($GB_ERRORS[$body_name]) && (!$editor_id || $GB_OPEN_ID==$editor_id)} gb-body-error-{$editor_id}{/if}" {if isset($GB_ERRORS[$body_name]) && (!$editor_id || $GB_OPEN_ID==$editor_id)}aria-invalid="true"{/if}>{$editor.text|escape:'html':'UTF-8'}</textarea>
  <span id="gb-body-count-{$editor_id}" class="gb-counter" data-count-for="gb-body-{$editor_id}">{$editor.text|count_characters:true} / {$GB_BODY_LIMIT}</span>
  {if isset($GB_ERRORS[$body_name]) && (!$editor_id || $GB_OPEN_ID==$editor_id)}<p id="gb-body-error-{$editor_id}" class="gb-error">{$GB_ERRORS[$body_name]|escape}</p>{/if}
  <p id="gb-body-help-{$editor_id}" class="gb-help">{'Characters include HTML markup. Blank lines alone do not create paragraphs. Scripts, embedded frames and unsupported markup are removed.'|translate}</p>
  <div class="gb-existing-images" data-existing-images hidden><strong>{'Images in this entry'|translate}</strong><p class="gb-help">{'Images are inserted immediately with a centered, medium-width layout. Select an image below to add a caption or change its layout.'|translate}</p><div data-image-list></div></div>
  <p class="gb-image-insert-status gb-help" data-image-insert-status role="status" aria-live="polite"></p>
  <details class="gb-help"><summary>{'Formatting help'|translate}</summary>
    <p>{'Use the buttons or HTML. Headings use h2–h4; the journal page title is h1. Inside code samples, write angle brackets as HTML entities so tags display as literal text. Preview shows sanitized content without saving it. Publishing a new entry or saving edits makes the entry public immediately.'|translate}</p>
    <pre>&lt;h2&gt;{'Section heading'|translate}&lt;/h2&gt;
&lt;h3&gt;{'Subheading'|translate}&lt;/h3&gt;
&lt;hr&gt;
&lt;blockquote&gt;{'A quoted passage.'|translate}&lt;/blockquote&gt;
&lt;p&gt;{'Inline code:'|translate} &lt;code&gt;safe_snippet()&lt;/code&gt;&lt;/p&gt;
&lt;pre&gt;&lt;code&gt;&amp;lt;strong&amp;gt;{'Literal code sample'|translate}&amp;lt;/strong&amp;gt;&lt;/code&gt;&lt;/pre&gt;
&lt;p&gt;{'A paragraph of text.'|translate}&lt;/p&gt;
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
