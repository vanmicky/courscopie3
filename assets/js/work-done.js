/* Travail à faire : cocher ce qui est fait, mémorisé sur l'appareil.
   Remplace l'ancien cahier statique (pages/cahier/) par un suivi léger,
   sans compte élève ni donnée envoyée au serveur. */
(function () {
  const STORAGE_KEY = "travail-fait";

  function load() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      const parsed = raw ? JSON.parse(raw) : [];
      return new Set(Array.isArray(parsed) ? parsed : []);
    } catch (error) {
      return new Set();
    }
  }

  function save(set) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify([...set]));
    } catch (error) {
      /* Navigation privée : le suivi reste valable pour la session. */
    }
  }

  function refreshCounter(root, done) {
    const counter = root.querySelector("[data-work-done-count]");
    if (!counter) return;
    const boxes = [...root.querySelectorAll("[data-work-done]")];
    const checked = boxes.filter(box => done.has(box.dataset.workDone)).length;
    counter.textContent = checked + " travail" + (checked > 1 ? "x" : "") + " marqué" + (checked > 1 ? "s" : "") + " comme fait" + (checked > 1 ? "s" : "") + " sur " + boxes.length;
  }

  document.addEventListener("DOMContentLoaded", function () {
    const root = document.querySelector("[data-work-global]");
    if (!root) return;
    const done = load();

    root.querySelectorAll("[data-work-done]").forEach(function (box) {
      const key = box.dataset.workDone;
      box.checked = done.has(key);
      box.closest("[data-work-card]")?.classList.toggle("is-done", box.checked);
      box.addEventListener("change", function () {
        if (box.checked) done.add(key);
        else done.delete(key);
        save(done);
        box.closest("[data-work-card]")?.classList.toggle("is-done", box.checked);
        refreshCounter(root, done);
      });
    });

    refreshCounter(root, done);
  });
})();
