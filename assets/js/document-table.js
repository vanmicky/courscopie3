(function () {
  function initTable(root) {
    const rows = Array.from(root.querySelectorAll('[data-document-row]'));
    const filters = document.querySelector('[data-document-filters]');
    const search = filters && filters.querySelector('[data-table-search]');
    const level = filters && filters.querySelector('[data-table-level]');
    const chapter = filters && filters.querySelector('[data-table-chapter]');
    const type = filters && filters.querySelector('[data-table-type]');
    const status = filters && filters.querySelector('[data-table-status]');
    const count = root.querySelector('[data-table-count]');
    const empty = root.querySelector('[data-table-empty]');
    const previous = root.querySelector('[data-page-prev]');
    const next = root.querySelector('[data-page-next]');
    const pageLabel = root.querySelector('[data-page-label]');
    const pageSize = Number(root.dataset.pageSize) || 10;
    const mobileSort = root.querySelector('[data-sort-mobile]');
    let page = 1;
    let sortKey = 'date';
    let sortDirection = -1;

    function normalized(value) {
      return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('fr');
    }

    function filteredRows() {
      const query = normalized(search && search.value);
      return rows.filter(function (row) {
        const haystack = normalized([row.dataset.name, row.dataset.type, row.dataset.level, row.dataset.chapter].join(' '));
        return (!query || haystack.includes(query)) &&
          (!level || !level.value || row.dataset.level === level.value) &&
          (!chapter || !chapter.value || row.dataset.chapterId === chapter.value) &&
          (!type || !type.value || row.dataset.type === type.value) &&
          (!status || !status.value || row.dataset.status === status.value);
      }).sort(function (a, b) {
        const av = normalized(a.dataset[sortKey]);
        const bv = normalized(b.dataset[sortKey]);
        return av.localeCompare(bv, 'fr', { numeric: true }) * sortDirection;
      });
    }

    function closeEditors() {
      root.querySelectorAll('[data-edit-row]').forEach(function (editRow) { editRow.hidden = true; });
      root.querySelectorAll('[data-edit-document]').forEach(function (button) { button.setAttribute('aria-expanded', 'false'); });
    }

    function render() {
      root.querySelectorAll('[data-sort]').forEach(function (button) {
        const active = button.dataset.sort === sortKey;
        button.closest('th').setAttribute('aria-sort', active ? (sortDirection === 1 ? 'ascending' : 'descending') : 'none');
        button.querySelector('span').textContent = active ? (sortDirection === 1 ? '↑' : '↓') : '↕';
      });
      if (mobileSort) mobileSort.value = sortKey + (sortDirection === 1 ? ':asc' : ':desc');
      closeEditors();
      const visible = filteredRows();
      const totalPages = Math.max(1, Math.ceil(visible.length / pageSize));
      page = Math.min(page, totalPages);
      rows.forEach(function (row) { row.hidden = true; });
      visible.slice((page - 1) * pageSize, page * pageSize).forEach(function (row) {
        row.hidden = false;
        row.parentNode.appendChild(row);
        const edit = root.querySelector('[data-edit-row="' + row.querySelector('[data-edit-document]').dataset.editDocument + '"]');
        if (edit) row.parentNode.insertBefore(edit, row.nextSibling);
      });
      count.textContent = visible.length + ' document' + (visible.length > 1 ? 's' : '');
      empty.hidden = visible.length !== 0;
      previous.disabled = page <= 1;
      next.disabled = page >= totalPages;
      pageLabel.textContent = 'Page ' + page + ' / ' + totalPages;
    }

    [search, level, chapter, type, status].filter(Boolean).forEach(function (control) {
      control.addEventListener(control === search ? 'input' : 'change', function () { page = 1; render(); });
    });
    root.querySelectorAll('[data-sort]').forEach(function (button) {
      button.addEventListener('click', function () {
        const key = button.dataset.sort;
        sortDirection = sortKey === key ? sortDirection * -1 : 1;
        sortKey = key;
        page = 1;
        render();
      });
    });
    root.querySelectorAll('[data-edit-document]').forEach(function (button) {
      button.addEventListener('click', function () {
        const editRow = root.querySelector('[data-edit-row="' + button.dataset.editDocument + '"]');
        const opening = editRow.hidden;
        closeEditors();
        editRow.hidden = !opening;
        button.setAttribute('aria-expanded', opening ? 'true' : 'false');
      });
    });
    previous.addEventListener('click', function () { if (page > 1) { page -= 1; render(); } });
    if (mobileSort) mobileSort.addEventListener('change', function () {
      const parts = mobileSort.value.split(':');
      sortKey = parts[0]; sortDirection = parts[1] === 'asc' ? 1 : -1; page = 1; render();
    });
    next.addEventListener('click', function () { page += 1; render(); });
    render();
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-document-table]').forEach(initTable);
  });
})();
