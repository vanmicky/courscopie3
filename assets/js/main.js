const root = document.documentElement;
const btn = document.querySelector("[data-theme-toggle]");
const icon = document.querySelector("[data-theme-icon]");
const themeLabel = document.querySelector("[data-theme-label]");
const main = document.querySelector("main");
if (main && !main.id) main.id = "main-content";

function setTheme(theme) {
  const isDark = theme === "dark";
  root.classList.toggle("dark", isDark);
  try { localStorage.setItem("theme", theme); } catch (_) { /* Private browsing can disable storage. */ }
  if (icon) {
    icon.innerHTML = '<svg class="icon theme-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><use href="#i-' + (isDark ? "sun" : "moon") + '"></use></svg>';
  }
  if (themeLabel) themeLabel.textContent = isDark ? "Thème clair" : "Thème sombre";
  if (btn) {
    btn.setAttribute("aria-pressed", isDark ? "true" : "false");
    btn.setAttribute("aria-label", isDark ? "Activer le thème clair" : "Activer le thème sombre");
    btn.setAttribute("title", isDark ? "Passer au thème clair" : "Passer au thème sombre");
  }
}

// Mode clair par défaut
let savedTheme = "light";
try { savedTheme = localStorage.getItem("theme") || "light"; } catch (_) {}
setTheme(savedTheme);

if (btn) {
  btn.setAttribute("type", "button");
  btn.addEventListener("click", () => {
    setTheme(root.classList.contains("dark") ? "light" : "dark");
  });
}

const search = document.querySelector("#searchInput");
if (search) {
  const cards = [...document.querySelectorAll("[data-card]")];
  const normalize = value => value.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLocaleLowerCase("fr");
  const status = document.querySelector("[data-search-status]");
  const empty = document.querySelector("[data-search-empty]");
  const filterCards = () => {
    const q = normalize(search.value.trim());
    let count = 0;
    cards.forEach(card => {
      const matches = normalize(card.textContent).includes(q);
      card.hidden = !matches;
      card.classList.toggle("hidden", !matches);
      if (matches) count++;
    });
    if (status) status.textContent = `${count} résultat${count === 1 ? "" : "s"}`;
    if (empty) empty.hidden = count !== 0;
  };
  search.addEventListener("input", filterCards);
  document.querySelector("[data-search-reset]")?.addEventListener("click", () => {
    search.value = "";
    filterCards();
    search.focus();
  });
}


// Sous-menus : hover + clic. Évite que le menu disparaisse trop vite.
document.querySelectorAll(".menu-group").forEach(group => {
  const button = group.querySelector(".nav-button");
  const submenu = group.querySelector(".submenu");
  if (!button) return;
  button.setAttribute("type", "button");
  button.setAttribute("aria-haspopup", "true");
  button.setAttribute("aria-expanded", "false");
  if (submenu) {
    submenu.setAttribute("role", "menu");
    submenu.querySelectorAll("a").forEach(link => link.setAttribute("role", "menuitem"));
  }

  button.addEventListener("click", event => {
    event.preventDefault();
    event.stopPropagation();
    document.querySelectorAll(".menu-group.open").forEach(other => {
      if (other !== group) other.classList.remove("open");
    });
    group.classList.toggle("open");
    document.querySelectorAll(".menu-group").forEach(item => {
      const itemButton = item.querySelector(".nav-button");
      if (itemButton) itemButton.setAttribute("aria-expanded", item.classList.contains("open") ? "true" : "false");
    });
  });
});

document.addEventListener("click", event => {
  if (!event.target.closest(".menu-group")) {
    document.querySelectorAll(".menu-group.open").forEach(group => group.classList.remove("open"));
    document.querySelectorAll(".nav-button[aria-expanded='true']").forEach(button => button.setAttribute("aria-expanded", "false"));
  }
});

document.addEventListener("keydown", event => {
  if (event.key === "Escape") {
    document.querySelectorAll(".menu-group.open").forEach(group => group.classList.remove("open"));
    document.querySelectorAll(".nav-button[aria-expanded='true']").forEach(button => button.setAttribute("aria-expanded", "false"));
  }
});

document.querySelectorAll("a[href], button, summary, input, select, textarea").forEach(element => {
  element.addEventListener("pointerdown", () => element.classList.add("is-pressing"), { passive: true });
  element.addEventListener("pointerup", () => element.classList.remove("is-pressing"), { passive: true });
  element.addEventListener("pointercancel", () => element.classList.remove("is-pressing"), { passive: true });
  element.addEventListener("blur", () => element.classList.remove("is-pressing"));
});

/* Messages de confirmation : disparition automatique après 6 secondes. */
(function () {
  const toast = document.querySelector("[data-admin-toast]");
  if (!toast) return;
  const fermer = () => {
    toast.classList.add("is-leaving");
    setTimeout(() => toast.remove(), 200);
  };
  setTimeout(fermer, 6000);
  toast.addEventListener("mouseenter", () => clearTimeout(toast._minuteur));
})();
