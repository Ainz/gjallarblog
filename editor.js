/* Gjallarblog: dependency-free HTML formatting aids and Unicode counters. */
(() => {
  'use strict';
  const length = value => Array.from(value.replace(/\r\n?/g, '\n')).length;
  const escapeAttribute = value => value.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
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
