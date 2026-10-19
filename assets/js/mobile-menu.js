/* Menu mobile en tiroir : ouverture/fermeture, accessibilité, verrouillage. */
(function () {
  const toggle = document.querySelector("[data-menu-toggle]");
  const menu = document.querySelector("[data-mobile-menu]");
  if (!toggle || !menu) return;

  const backdrop = document.querySelector("[data-menu-backdrop]");
  const close = menu.querySelector("[data-menu-close]");
  const links = [...menu.querySelectorAll("a")];

  function setOpen(open) {
    menu.classList.toggle("is-open", open);
    menu.setAttribute("aria-hidden", open ? "false" : "true");
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
    toggle.setAttribute("aria-label", open ? "Fermer le menu" : "Ouvrir le menu");
    document.documentElement.classList.toggle("menu-open", open);
    if (open) {
      const first = menu.querySelector("a, button");
      if (first) first.focus();
    } else {
      toggle.focus();
    }
  }

  toggle.addEventListener("click", () => {
    setOpen(menu.classList.contains("is-open") ? false : true);
  });
  if (close) close.addEventListener("click", () => setOpen(false));
  if (backdrop) backdrop.addEventListener("click", () => setOpen(false));
  links.forEach(link => link.addEventListener("click", () => setOpen(false)));

  document.addEventListener("keydown", event => {
    if (event.key === "Escape" && menu.classList.contains("is-open")) {
      setOpen(false);
    }
  });

  // Fermer si la fenêtre repasse en mode bureau.
  const mq = window.matchMedia("(min-width: 1280px)");
  const onChange = () => { if (mq.matches) setOpen(false); };
  if (mq.addEventListener) mq.addEventListener("change", onChange);
  else if (mq.addListener) mq.addListener(onChange);
})();
