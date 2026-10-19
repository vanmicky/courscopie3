(function () {
  function initialiseChecklist(root) {
    const key = root.dataset.storageKey;
    const inputs = Array.from(root.querySelectorAll('.skill-check input[type="checkbox"]'));
    const progress = root.querySelector('[data-skill-progress]');
    let saved = [];
    try {
      saved = JSON.parse(localStorage.getItem(key) || '[]');
      if (!Array.isArray(saved)) saved = [];
    } catch (error) {
      saved = [];
    }

    function update() {
      const checked = inputs.filter(input => input.checked).map(input => input.value);
      inputs.forEach(input => input.closest('.skill-check').classList.toggle('is-checked', input.checked));
      if (progress) progress.textContent = `${checked.length} / ${inputs.length}`;
      try { localStorage.setItem(key, JSON.stringify(checked)); } catch (error) {}
    }

    inputs.forEach(input => {
      input.checked = saved.includes(input.value);
      input.addEventListener('change', update);
    });
    update();
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-chapter-skills]').forEach(initialiseChecklist);
  });
})();
