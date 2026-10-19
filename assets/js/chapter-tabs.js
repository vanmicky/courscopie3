/* Onglets horizontaux des pages chapitre.
   Amelioration progressive : sans JavaScript, les liens de la barre restent des
   ancres qui font defiler la page (comportement precedent). Avec JavaScript, la
   barre devient un vrai jeu d'onglets : une seule section visible a la fois. */
(function () {
  const bar = document.querySelector("[data-chapter-tabs]");
  const panels = document.querySelector("[data-chapter-panels]");
  if (!bar || !panels) return;

  const tabs = [...bar.querySelectorAll("[data-tab-target]")];
  const sections = tabs
    .map(tab => document.getElementById(tab.dataset.tabTarget))
    .filter(Boolean);

  // Un seul onglet : rien a basculer, on laisse les ancres tranquilles.
  if (tabs.length < 2 || sections.length < 2) return;

  bar.setAttribute("role", "tablist");
  bar.classList.add("is-enhanced");
  panels.classList.add("is-tabbed");

  tabs.forEach(tab => {
    tab.setAttribute("role", "tab");
    tab.setAttribute("aria-controls", tab.dataset.tabTarget);
    tab.id = "tab-" + tab.dataset.tabTarget;
    tab.removeAttribute("aria-current");
  });
  sections.forEach((section, index) => {
    section.setAttribute("role", "tabpanel");
    section.setAttribute("aria-labelledby", "tab-" + section.id);
    section.setAttribute("tabindex", "0");
    if (index > 0) section.hidden = true;
  });

  let current = tabs[0].dataset.tabTarget;

  function activate(id, options) {
    const settings = options || {};
    const target = tabs.find(tab => tab.dataset.tabTarget === id) ? id : tabs[0].dataset.tabTarget;
    current = target;

    tabs.forEach(tab => {
      const on = tab.dataset.tabTarget === target;
      tab.classList.toggle("is-active", on);
      tab.setAttribute("aria-selected", on ? "true" : "false");
      tab.tabIndex = on ? 0 : -1;
      if (on && settings.focus) tab.focus();
    });

    sections.forEach(section => {
      section.hidden = section.id !== target;
    });

    if (settings.scroll && window.scrollY > 0) {
      const offset = bar.getBoundingClientRect().top + window.scrollY - 12;
      window.scrollTo({ top: offset, behavior: "smooth" });
    }

    if (settings.history !== false && window.history.replaceState) {
      window.history.replaceState(null, "", "#" + target);
    }
  }

  bar.addEventListener("click", event => {
    const tab = event.target.closest("[data-tab-target]");
    if (!tab) return;
    event.preventDefault();
    activate(tab.dataset.tabTarget, { scroll: true });
  });

  bar.addEventListener("keydown", event => {
    const index = tabs.indexOf(document.activeElement);
    if (index === -1) return;
    let next = null;
    if (event.key === "ArrowRight") next = tabs[(index + 1) % tabs.length];
    else if (event.key === "ArrowLeft") next = tabs[(index - 1 + tabs.length) % tabs.length];
    else if (event.key === "Home") next = tabs[0];
    else if (event.key === "End") next = tabs[tabs.length - 1];
    if (!next) return;
    event.preventDefault();
    activate(next.dataset.tabTarget, { focus: true });
  });

  // Ouverture directe : depuis un lien externe (#videos) ou un parametre ?tab=.
  let initial = (window.location.hash || "").replace("#", "");
  if (!tabs.some(tab => tab.dataset.tabTarget === initial)) {
    const fromQuery = new URLSearchParams(window.location.search).get("tab") || "";
    initial = tabs.some(tab => tab.dataset.tabTarget === fromQuery) ? fromQuery : tabs[0].dataset.tabTarget;
  }
  activate(initial, { history: false });

  // Le bouton « precedent » du navigateur doit revenir sur le bon onglet.
  window.addEventListener("hashchange", () => {
    const id = (window.location.hash || "").replace("#", "");
    if (id && id !== current) activate(id, { history: false });
  });
})();
