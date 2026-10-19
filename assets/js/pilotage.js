(function () {
  var all = document.querySelector('[data-pilotage-select-all]');
  var items = Array.prototype.slice.call(document.querySelectorAll('[data-pilotage-select]'));
  if (!all) return;
  all.addEventListener('change', function () { items.forEach(function (item) { item.checked = all.checked; }); });
  items.forEach(function (item) {
    item.addEventListener('change', function () {
      all.checked = items.length > 0 && items.every(function (input) { return input.checked; });
      all.indeterminate = items.some(function (input) { return input.checked; }) && !all.checked;
    });
  });
})();
