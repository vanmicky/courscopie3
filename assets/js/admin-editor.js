(() => {
  const menuButton = document.querySelector('[data-admin-menu]');
  const sidebar = document.querySelector('.admin-sidebar');
  const backdrop = document.querySelector('[data-admin-menu-close]');
  const compactMenu = window.matchMedia('(max-width: 1099px)');

  const setMenu = open => {
    document.body.classList.toggle('admin-menu-open', open);
    menuButton?.setAttribute('aria-expanded', String(open));
    if (sidebar) sidebar.inert = compactMenu.matches && !open;
  };
  menuButton?.addEventListener('click', () => setMenu(!document.body.classList.contains('admin-menu-open')));
  backdrop?.addEventListener('click', () => setMenu(false));
  compactMenu.addEventListener('change', () => setMenu(false));
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && document.body.classList.contains('admin-menu-open')) { setMenu(false); menuButton?.focus(); }
  });
  setMenu(false);
  const themeButton = document.querySelector('[data-admin-theme]');
  themeButton?.addEventListener('click', () => {
    const dark = document.documentElement.classList.toggle('dark');
    themeButton.setAttribute('aria-pressed', String(dark));
    try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (error) {}
  });
  themeButton?.setAttribute('aria-pressed', String(document.documentElement.classList.contains('dark')));
  sidebar?.addEventListener('click', event => {
    if (event.target.closest('a')) setMenu(false);
  });

  const escapeHtml = value => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  const inlineMarkup = value => {
    let safe = escapeHtml(value);
    safe = safe.replace(/!\[([^\]]*)\]\((https?:\/\/[^)\s]+|[a-zA-Z0-9_\/.?=&amp;%-]+)\)/g, (_, alt, url) => {
      const previewUrl = /^https?:\/\//i.test(url) ? url : `../${url}`;
      return `<img class="content-inline-image" src="${previewUrl}" alt="${alt}">`;
    });
    safe = safe.replace(/\[([^\]]+)\]\((https?:\/\/[^)\s]+|[a-zA-Z0-9_\/.?=&amp;%-]+)\)/g, (_, label, url) => {
      const previewUrl = /^https?:\/\//i.test(url) ? url : `../${url}`;
      return `<a href="${previewUrl}">${label}</a>`;
    });
    return safe.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>').replace(/`([^`]+)`/g, '<code>$1</code>');
  };

  const renderPreview = value => String(value).split(/\r?\n/).map(line => {
    if (/^##\s+/.test(line)) return `<h3 class="content-heading">${inlineMarkup(line.replace(/^##\s+/, ''))}</h3>`;
    if (/^#\s+/.test(line)) return `<h2 class="content-heading content-heading-large">${inlineMarkup(line.replace(/^#\s+/, ''))}</h2>`;
    if (/^-\s+/.test(line)) return `<div class="content-list-item">${inlineMarkup(line.replace(/^-\s+/, ''))}</div>`;
    if (/^>\s*/.test(line)) return `<blockquote class="content-callout">${inlineMarkup(line.replace(/^>\s*/, ''))}</blockquote>`;
    if (!line.trim()) return '<div class="content-spacer"></div>';
    return `<div class="content-line">${inlineMarkup(line)}</div>`;
  }).join('');

  let activeTextarea = null;
  const insertText = (textarea, before, after = '', fallback = '') => {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selected = textarea.value.slice(start, end) || fallback;
    textarea.setRangeText(before + selected + after, start, end, 'end');
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
    textarea.focus();
  };

  document.querySelectorAll('.admin-area textarea').forEach((textarea, index) => {
    if (textarea.dataset.editor === 'off') return;
    const editor = document.createElement('div');
    editor.className = 'pedagogical-editor';
    const toolbar = document.createElement('div');
    toolbar.className = 'editor-toolbar';
    toolbar.setAttribute('role', 'toolbar');
    toolbar.setAttribute('aria-label', 'Mise en forme');
    const controls = [
      ['Titre', '# ', '', 'Titre'],
      ['Sous-titre', '## ', '', 'Sous-titre'],
      ['Gras', '**', '**', 'texte important'],
      ['Liste', '- ', '', 'élément'],
      ['À retenir', '> ', '', 'information importante'],
      ['Formule', '\\(', '\\)', 'a+b'],
      ['Lien', '[', '](https://)', 'texte du lien'],
      ['Code', '`', '`', 'code'],
    ];
    controls.forEach(([label, before, after, fallback]) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.textContent = label;
      button.title = label;
      button.addEventListener('click', () => insertText(textarea, before, after, fallback));
      toolbar.append(button);
    });
    const mediaButton = document.createElement('button');
    mediaButton.type = 'button';
    mediaButton.textContent = 'Médiathèque';
    mediaButton.addEventListener('click', () => {
      activeTextarea = textarea;
      document.querySelector('[data-media-dialog]')?.showModal();
    });
    toolbar.append(mediaButton);
    const previewButton = document.createElement('button');
    previewButton.type = 'button';
    previewButton.textContent = 'Aperçu';
    previewButton.setAttribute('aria-expanded', 'false');
    toolbar.append(previewButton);

    const preview = document.createElement('div');
    preview.className = 'editor-preview lesson-content';
    preview.hidden = true;
    preview.id = `editor-preview-${index}`;
    previewButton.setAttribute('aria-controls', preview.id);
    const refresh = () => {
      preview.innerHTML = renderPreview(textarea.value);
      if (window.renderMathInElement) {
        window.renderMathInElement(preview, { delimiters: [{left:'$$',right:'$$',display:true},{left:'\\[',right:'\\]',display:true},{left:'\\(',right:'\\)',display:false}], throwOnError:false });
      }
    };
    previewButton.addEventListener('click', () => {
      preview.hidden = !preview.hidden;
      previewButton.setAttribute('aria-expanded', String(!preview.hidden));
      if (!preview.hidden) refresh();
    });
    textarea.addEventListener('input', () => { if (!preview.hidden) refresh(); });
    const label = textarea.closest('label');
    if (label && label.contains(textarea)) {
      textarea.id ||= `pedagogical-field-${index}`;
      label.setAttribute('for', textarea.id);
      label.parentNode.insertBefore(editor, label.nextSibling);
    } else {
      textarea.parentNode.insertBefore(editor, textarea);
    }
    editor.append(toolbar, textarea, preview);
  });

  const dialog = document.querySelector('[data-media-dialog]');
  document.querySelector('[data-media-close]')?.addEventListener('click', () => dialog?.close());
  document.querySelector('[data-media-search]')?.addEventListener('input', event => {
    const query = event.target.value.toLocaleLowerCase('fr');
    document.querySelectorAll('[data-media-item]').forEach(item => {
      item.hidden = !item.textContent.toLocaleLowerCase('fr').includes(query);
    });
  });
  document.querySelectorAll('[data-media-item]').forEach(item => item.addEventListener('click', () => {
    if (!activeTextarea) return;
    const label = item.dataset.label || 'Fichier';
    const path = item.dataset.path || '';
    const markup = item.dataset.kind === 'image' ? `![${label}](${path})` : `[${label}](${path})`;
    insertText(activeTextarea, markup, '', '');
    dialog?.close();
  }));

  const toast = document.querySelector('[data-admin-toast]');
  if (toast) {
    window.setTimeout(() => {
      toast.classList.add('is-leaving');
      window.setTimeout(() => toast.remove(), 180);
    }, 6000);
  }

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') setMenu(false);
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 's') {
      const form = document.activeElement?.closest('form');
      const submit = form?.querySelector('button[type="submit"]');
      if (submit) { event.preventDefault(); submit.click(); }
    }
  });
})();
