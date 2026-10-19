/* Filtrage instantané de la liste des chapitres (administration). */
(function () {
  const form = document.querySelector("[data-chapter-filter]");
  const list = document.querySelector("[data-chapter-list]");
  if (!form || !list) return;
  const search = form.querySelector("[data-chapter-search]");
  const items = [...list.querySelectorAll("[data-chapter-item]")];
  const counter = form.querySelector("[data-chapter-count]");
  const empty = list.querySelector("[data-chapter-empty]");
  if (!search || !items.length) return;

  const normalize = value => value
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase();

  function apply() {
    const query = normalize(search.value.trim());
    let visible = 0;
    items.forEach(item => {
      const match = query === "" || normalize(item.textContent).includes(query);
      item.hidden = !match;
      if (match) visible++;
    });
    if (counter) counter.textContent = visible + " chapitre" + (visible > 1 ? "s" : "") + " affiché" + (visible > 1 ? "s" : "");
    if (empty) empty.hidden = visible !== 0;
  }

  search.addEventListener("input", apply);
  form.addEventListener("submit", event => {
    // La recherche instantanée n'a pas besoin d'un aller-retour serveur.
    if (search.value.trim() !== "") event.preventDefault();
  });
})();
