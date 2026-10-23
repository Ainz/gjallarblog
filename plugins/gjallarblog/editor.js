/* Gjallarblog: dependency-free HTML formatting aids and Unicode counters. */
(() => {
  'use strict';
  const length = value => Array.from(value.replace(/\r\n?/g, '\n')).length;
  const escapeAttribute = value => value.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
  const escapeText = value => value.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  const imageMarkup = (url, alt, caption, layout, size) => {
    const src = escapeAttribute(url);
    const description = escapeAttribute(alt.trim());
    if (layout === 'inline') return `<img class="gb-image--inline" src="${src}" alt="${description}">`;
    const note = caption.trim() ? `<figcaption>${escapeText(caption.trim())}</figcaption>` : '';
    return `<figure class="gb-image gb-image--${layout} gb-image--${size}"><img src="${src}" alt="${description}">${note}</figure>`;
  };
  let submitting = false;
  const forms = [...document.querySelectorAll('[data-gb-form]')];
  const original = new Map(forms.map(form => [form, new URLSearchParams(new FormData(form)).toString()]));
  document.querySelectorAll('[data-max]').forEach(field => {
    const limit = Number(field.dataset.max);
    const counter = document.querySelector(`[data-count-for="${field.id}"]`);
    const update = () => {
      const count = length(field.value);
      const message = field.closest('[data-gb-editor]')?.dataset.limitMessage || field.closest('form')?.dataset.limitMessage || 'Use no more than %s characters.';
      field.setCustomValidity(count > limit ? message.replace('%s', String(limit)) : '');
      if (counter) {
        counter.textContent = `${count.toLocaleString()} / ${limit.toLocaleString()}`;
        counter.classList.toggle('gb-over-limit', count > limit);
      }
    };
    field.addEventListener('input', update);
    update();
  });
  document.querySelectorAll('[data-gb-editor]').forEach(editor => {
    const textarea = editor.querySelector('textarea');
    const toolbar = editor.querySelector('.gb-format-tools');
    const form = editor.closest('form');
    const fileInput = editor.querySelector('[data-image-file]');
    const imageTools = editor.querySelector('[data-image-tools]');
    const imageList = editor.querySelector('[data-image-list]');
    const imageListWrap = editor.querySelector('[data-existing-images]');
    const status = editor.querySelector('[data-image-status]');
    const insertStatus = editor.querySelector('[data-image-insert-status]');
    const altInput = editor.querySelector('[data-image-alt]');
    const captionInput = editor.querySelector('[data-image-caption]');
    const layoutInput = editor.querySelector('[data-image-layout]');
    const sizeInput = editor.querySelector('[data-image-size]');
    const sizeLabel = editor.querySelector('[data-image-size-label]');
    const insertImageButton = editor.querySelector('[data-image-insert]');
    let pendingImage = null;
    toolbar.hidden = false;
    toolbar.addEventListener('click', event => {
      const button = event.target.closest('button[data-format]');
      if (!button) return;
      const tag = button.dataset.format;
      const start = textarea.selectionStart;
      const end = textarea.selectionEnd;
      const selected = textarea.value.slice(start, end);
      let before = `<${tag}>`, after = `</${tag}>`;
      if (tag === 'ul') { before = '<ul>\n<li>'; after = '</li>\n</ul>'; }
      if (tag === 'a') {
        const address = window.prompt(button.dataset.prompt, 'https://');
        if (!address) return;
        // Server sanitation is authoritative; do not generate active URL schemes here.
        if (/^[\s]*[a-z][a-z\d+.-]*:/i.test(address) && !/^(https?:|mailto:)/i.test(address.trim())) return;
        before = `<a href="${escapeAttribute(address.trim())}">`;
      }
      textarea.setRangeText(before + selected + after, start, end, 'preserve');
      textarea.focus();
      textarea.setSelectionRange(start + before.length, start + before.length + selected.length);
      textarea.dispatchEvent(new Event('input', {bubbles:true}));
    });
    const closeImageTools = () => {
      if (pendingImage?.objectUrl) URL.revokeObjectURL(pendingImage.objectUrl);
      pendingImage = null;
      textarea.readOnly = false;
      if (imageTools.open) imageTools.close();
      fileInput.value = '';
      const preview = editor.querySelector('[data-image-preview]');
      preview.hidden = true;
      preview.removeAttribute('src');
    };
    const parseBody = () => new DOMParser().parseFromString(textarea.value, 'text/html');
    const mediaImages = body => [...body.querySelectorAll('img[src*="/blog/media/"]')];
    const updateImageList = () => {
      const images = mediaImages(parseBody().body);
      imageList.replaceChildren();
      imageListWrap.hidden = images.length === 0;
      images.forEach((image, index) => {
        const figure = image.closest('figure.gb-image');
        const layout = image.classList.contains('gb-image--inline') ? 'inline' :
          (['left','center','right'].find(value => figure?.classList.contains(`gb-image--${value}`)) || 'center');
        const size = ['small','medium','full'].find(value => figure?.classList.contains(`gb-image--${value}`)) || 'medium';
        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.imageEditIndex = String(index);
        const editLabel = editor.dataset.imageActionLabel || 'Edit image %s';
        button.textContent = `${editLabel.replace('%s', String(index + 1))} · ${layout} · ${size}`;
        imageList.append(button);
      });
    };
    const showImageTools = (imageIndex) => {
      if (pendingImage?.objectUrl) URL.revokeObjectURL(pendingImage.objectUrl);
      pendingImage = {imageIndex};
      const images = mediaImages(parseBody().body);
      const image = images[imageIndex];
      if (!image) return closeImageTools();
      const preview = editor.querySelector('[data-image-preview]');
      preview.src = image.getAttribute('src');
      preview.hidden = false;
      const figure = image.closest('figure.gb-image');
      altInput.value = image.getAttribute('alt') || '';
      captionInput.value = figure?.querySelector('figcaption')?.textContent || '';
      layoutInput.value = image.classList.contains('gb-image--inline') ? 'inline' :
        (['left','center','right'].find(value => figure?.classList.contains(`gb-image--${value}`)) || 'center');
      sizeInput.value = ['small','medium','full'].find(value => figure?.classList.contains(`gb-image--${value}`)) || 'medium';
      status.textContent = `${editor.dataset.imageEditMessage || 'Editing image %s.'}`.replace('%s', String(imageIndex + 1));
      insertImageButton.textContent = editor.dataset.updateImageLabel || 'Save image layout';
      const inline = layoutInput.value === 'inline';
      sizeLabel.hidden = inline;
      captionInput.disabled = inline;
      if (!imageTools.open) imageTools.showModal();
      altInput.focus();
    };
    const selection = () => ({start:textarea.selectionStart, end:textarea.selectionEnd});
    const uploadAndInsert = async (file) => {
      const savedSelection = selection();
      const savedContent = textarea.value;
      insertStatus.textContent = editor.dataset.uploadingMessage || 'Uploading and inserting image…';
      textarea.readOnly = true;
      try {
        const data = new FormData();
        data.append('pwg_token', form.querySelector('[name="pwg_token"]').value);
        data.append('upload_image', '1');
        data.append('image', file, file.name || 'pasted-image');
        const response = await fetch(form.dataset.uploadUrl, {method:'POST', body:data, credentials:'same-origin'});
        const result = await response.json();
        if (!response.ok || !result.url) throw new Error(result.error || 'The image could not be uploaded.');
        if (textarea.value !== savedContent) throw new Error('The entry changed while the image was uploading. The image was uploaded but not inserted; choose it again to insert it.');
        const markup = imageMarkup(result.url, '', '', 'center', 'medium');
        const candidate = savedContent.slice(0, savedSelection.start) + markup + savedContent.slice(savedSelection.end);
        if (length(candidate) > Number(textarea.dataset.max)) throw new Error('This image would exceed the entry character limit. Shorten the content and try again.');
        textarea.setRangeText(markup, savedSelection.start, savedSelection.end, 'end');
        textarea.dispatchEvent(new Event('input', {bubbles:true}));
        updateImageList();
        insertStatus.textContent = editor.dataset.imageInsertedMessage || 'Image inserted. Select it under “Images in this entry” to add a caption or change its layout.';
        textarea.focus();
      } catch (error) {
        insertStatus.textContent = error.message || 'The image could not be inserted.';
      } finally {
        textarea.readOnly = false;
        fileInput.value = '';
      }
    };
    toolbar.addEventListener('click', event => {
      if (event.target.closest('[data-image-open]')) { fileInput.click(); }
    });
    fileInput.addEventListener('change', () => {
      const file = fileInput.files?.[0];
      if (file) uploadAndInsert(file);
    });
    textarea.addEventListener('paste', event => {
      const imageItem = [...(event.clipboardData?.items || [])].find(item => item.kind === 'file' && /^image\/(png|jpeg|webp)$/i.test(item.type));
      const file = imageItem?.getAsFile();
      if (!file) return;
      event.preventDefault();
      uploadAndInsert(file);
    });
    layoutInput.addEventListener('change', () => {
      const inline = layoutInput.value === 'inline';
      sizeLabel.hidden = inline;
      captionInput.disabled = inline;
    });
    editor.querySelectorAll('[data-image-cancel]').forEach(button => button.addEventListener('click', closeImageTools));
    imageTools.addEventListener('cancel', event => { event.preventDefault(); closeImageTools(); });
    imageList.addEventListener('click', event => {
      const button = event.target.closest('[data-image-edit-index]');
      if (button) showImageTools(Number(button.dataset.imageEditIndex));
    });
    insertImageButton.addEventListener('click', async () => {
      if (!pendingImage) return;
      status.textContent = editor.dataset.updatingMessage || 'Updating image layout…';
      insertImageButton.disabled = true;
      try {
        const doc = parseBody();
        const images = mediaImages(doc.body);
        const image = images[pendingImage.imageIndex];
        if (!image) throw new Error('That image is no longer in the entry.');
        image.setAttribute('alt', altInput.value);
        const currentFigure = image.closest('figure.gb-image');
        if (layoutInput.value === 'inline') {
          image.className = 'gb-image--inline';
          if (currentFigure) currentFigure.replaceWith(image);
        } else {
          image.removeAttribute('class');
          const figure = currentFigure || doc.createElement('figure');
          figure.className = `gb-image gb-image--${layoutInput.value} gb-image--${sizeInput.value}`;
          if (!currentFigure) { image.before(figure); figure.append(image); }
          else figure.className = `gb-image gb-image--${layoutInput.value} gb-image--${sizeInput.value}`;
          let caption = figure.querySelector('figcaption');
          if (captionInput.value.trim()) {
            if (!caption) { caption = doc.createElement('figcaption'); figure.append(caption); }
            caption.textContent = captionInput.value.trim();
          } else if (caption) caption.remove();
        }
        textarea.value = doc.body.innerHTML;
        textarea.dispatchEvent(new Event('input', {bubbles:true}));
        closeImageTools();
        updateImageList();
      } catch (error) {
        status.textContent = error.message || 'The image could not be inserted.';
      } finally { insertImageButton.disabled = false; }
    });
    updateImageList();
  });
  forms.forEach(form => form.addEventListener('submit', () => { submitting = true; }));
  document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', event => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    else submitting = true;
  }));
  window.addEventListener('beforeunload', event => {
    if (!submitting && forms.some(form => new URLSearchParams(new FormData(form)).toString() !== original.get(form))) {
      event.preventDefault();
      event.returnValue = '';
    }
  });
  document.body.classList.add('gb-admin-page');
  const preview = document.querySelector('.gb-preview');
  if (preview) { preview.focus(); preview.scrollIntoView({block:'start'}); }
  const errors = document.querySelector('.gb-error-summary');
  if (errors) errors.focus();
})();
