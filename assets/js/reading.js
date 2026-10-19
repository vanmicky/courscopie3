/* Confort de lecture des fiches méthodes longues :
   barre de progression, sommaire actif, retour en haut. */
(function () {
  const article = document.querySelector(".method-sheet");
  if (!article) return;

  /* --- Barre de progression ------------------------------------------- */
  const bar = document.querySelector("[data-reading-progress]");
  if (bar) {
    const update = () => {
      const rect = article.getBoundingClientRect();
      const total = rect.height - window.innerHeight;
      const done = Math.min(Math.max(-rect.top, 0), Math.max(total, 1));
      bar.style.width = (total > 0 ? (done / total) * 100 : 0) + "%";
    };
    update();
    window.addEventListener("scroll", update, { passive: true });
    window.addEventListener("resize", update);
  }

  /* --- Sommaire : section en cours ------------------------------------ */
  const links = [...document.querySelectorAll(".learn2-nav a[href^='#']")];
  if (links.length && "IntersectionObserver" in window) {
    const byId = new Map();
    links.forEach(link => byId.set(link.getAttribute("href").slice(1), link));
    const sections = [...document.querySelectorAll(".learn2-section[id]")].filter(s => byId.has(s.id));
    const setActive = id => {
      links.forEach(l => l.classList.toggle("is-active", l === byId.get(id)));
    };
    const visible = new Map();
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => visible.set(entry.target.id, entry.isIntersecting));
      const current = sections.find(s => visible.get(s.id));
      if (current) setActive(current.id);
    }, { rootMargin: "-25% 0px -60% 0px", threshold: 0 });
    sections.forEach(section => observer.observe(section));
  }

  /* --- Retour en haut -------------------------------------------------- */
  const top = document.querySelector("[data-reading-top]");
  if (top) {
    top.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
      const back = document.querySelector(".method-detail > .nav-link");
      if (back) back.focus({ preventScroll: true });
    });
  }
})();
