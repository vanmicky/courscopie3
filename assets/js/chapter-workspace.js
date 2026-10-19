(function () {
  let dirty = false;
  document.querySelectorAll('[data-workspace-form]').forEach(function (form) {
    form.addEventListener('input', function () { dirty = true; });
    form.addEventListener('change', function () { dirty = true; });
  });
  // Tout envoi de formulaire lève l'avertissement, y compris les boutons
  // « Modifier », « Supprimer » et « Publier » : sinon le navigateur demandait
  // « quitter le site ? » et l'action semblait ne pas fonctionner.
  document.addEventListener('submit', function () { dirty = false; }, true);
  window.addEventListener('beforeunload', function (event) {
    if (dirty) { event.preventDefault(); event.returnValue = ''; }
  });

  var selectAll = document.querySelector('[data-select-all]');
  var selections = Array.prototype.slice.call(document.querySelectorAll('[data-select-item]'));
  if (selectAll) {
    selectAll.addEventListener('change', function () {
      selections.forEach(function (checkbox) { checkbox.checked = selectAll.checked; });
    });
    selections.forEach(function (checkbox) {
      checkbox.addEventListener('change', function () {
        selectAll.checked = selections.length > 0 && selections.every(function (item) { return item.checked; });
        selectAll.indeterminate = selections.some(function (item) { return item.checked; }) && !selectAll.checked;
      });
    });
  }

  var list = document.querySelector('[data-sortable-list]');
  var reorderForm = document.getElementById('workspace-reorder');
  function allowReorder() {
    if (!dirty) return true;
    if (!window.confirm('Réordonner maintenant abandonnera les modifications non enregistrées du formulaire. Continuer ?')) return false;
    dirty = false;
    return true;
  }
  function saveOrder() {
    if (!list || !reorderForm) return;
    reorderForm.querySelector('[data-ordered-ids]').value = Array.prototype.map.call(list.querySelectorAll('[data-sort-item]'), function (item) { return item.dataset.id; }).join(',');
    dirty = false;
    reorderForm.submit();
  }
  if (list && reorderForm) {
    var dragged = null;
    list.addEventListener('dragstart', function (event) {
      if (!allowReorder()) { event.preventDefault(); return; }
      dragged = event.target.closest('[data-sort-item]');
      if (dragged) dragged.classList.add('is-dragging');
    });
    list.addEventListener('dragover', function (event) {
      if (!dragged) return;
      event.preventDefault();
      var target = event.target.closest('[data-sort-item]');
      if (!target || target === dragged) return;
      var box = target.getBoundingClientRect();
      list.insertBefore(dragged, event.clientY < box.top + box.height / 2 ? target : target.nextSibling);
    });
    list.addEventListener('dragend', function () {
      if (!dragged) return;
      dragged.classList.remove('is-dragging');
      dragged = null;
      saveOrder();
    });
    list.addEventListener('click', function (event) {
      var button = event.target.closest('[data-move]');
      if (!button) return;
      if (!allowReorder()) return;
      var item = button.closest('[data-sort-item]');
      if (button.dataset.move === 'up' && item.previousElementSibling) list.insertBefore(item, item.previousElementSibling);
      if (button.dataset.move === 'down' && item.nextElementSibling) list.insertBefore(item.nextElementSibling, item);
      saveOrder();
    });
  }
})();
