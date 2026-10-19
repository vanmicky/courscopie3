/* Outils de classe : onglets, plein ecran, liste d'eleves partagee,
   formation de groupes. Amelioration progressive : sans JavaScript, les liens
   d'onglet font defiler la page et les panneaux restent visibles. */
(function () {
  /* ---------------- Onglets ---------------- */
  const bar = document.querySelector("[data-tool-tabs]");
  const panels = document.querySelector("[data-tool-panels]");
  const tabs = bar ? [...bar.querySelectorAll("[data-tool-target]")] : [];
  const sections = panels ? [...panels.querySelectorAll("[data-tool-panel]")] : [];

  function activateTool(id, options) {
    const settings = options || {};
    const known = tabs.some(tab => tab.dataset.toolTarget === id);
    const target = known ? id : (tabs[0] && tabs[0].dataset.toolTarget);
    if (!target) return;

    tabs.forEach(tab => {
      const on = tab.dataset.toolTarget === target;
      tab.classList.toggle("is-active", on);
      tab.setAttribute("aria-selected", on ? "true" : "false");
      tab.tabIndex = on ? 0 : -1;
      if (on && settings.focus) tab.focus();
    });
    sections.forEach(section => { section.hidden = section.id !== target; });

    if (settings.scroll && window.scrollY > 0) {
      const offset = bar.getBoundingClientRect().top + window.scrollY - 12;
      window.scrollTo({ top: offset, behavior: "smooth" });
    }
    if (settings.history !== false && window.history.replaceState && known) {
      window.history.replaceState(null, "", "#" + target);
    }
    document.dispatchEvent(new CustomEvent("outil:change", { detail: { id: target } }));
  }

  if (bar && sections.length > 1) {
    bar.setAttribute("role", "tablist");
    tabs.forEach(tab => {
      tab.setAttribute("role", "tab");
      tab.setAttribute("aria-controls", tab.dataset.toolTarget);
      tab.id = "tab-" + tab.dataset.toolTarget;
    });
    sections.forEach(section => {
      section.setAttribute("role", "tabpanel");
      section.setAttribute("aria-labelledby", "tab-" + section.id);
    });
    bar.addEventListener("click", event => {
      const tab = event.target.closest("[data-tool-target]");
      if (!tab) return;
      event.preventDefault();
      activateTool(tab.dataset.toolTarget, { scroll: true });
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
      activateTool(next.dataset.toolTarget, { focus: true });
    });

    let initial = (window.location.hash || "").replace("#", "");
    if (!tabs.some(tab => tab.dataset.toolTarget === initial)) {
      initial = tabs[0].dataset.toolTarget;
    }
    activateTool(initial, { history: false });
    window.addEventListener("hashchange", () => {
      const id = (window.location.hash || "").replace("#", "");
      if (id) activateTool(id, { history: false });
    });
  }

  /* ---------------- Plein ecran ---------------- */
  document.querySelectorAll("[data-fullscreen]").forEach(button => {
    button.addEventListener("click", () => {
      const scope = button.closest("[data-tool-panel]") || document.documentElement;
      const active = document.fullscreenElement;
      if (active) { document.exitFullscreen(); return; }
      if (scope.requestFullscreen) scope.requestFullscreen().catch(() => {});
    });
  });

  /* ---------------- Liste d'eleves partagee ---------------- */
  const STORE = "outils-eleves";
  const inputs = [...document.querySelectorAll("[data-names-input]")];

  function parseNames(value) {
    return String(value || "")
      .split(/[\n,;]+/)
      .map(name => name.trim())
      .filter(Boolean);
  }

  function currentNames() {
    return inputs.length ? parseNames(inputs[0].value) : [];
  }

  function refreshCounts() {
    const total = currentNames().length;
    document.querySelectorAll("[data-names-count]").forEach(el => { el.textContent = total; });
  }

  function loadNames() {
    let stored = "";
    try { stored = localStorage.getItem(STORE) || ""; } catch (error) { stored = ""; }
    if (!stored) return;
    inputs.forEach(input => { input.value = stored; });
  }

  if (inputs.length) {
    loadNames();
    inputs.forEach(input => {
      input.addEventListener("input", () => {
        inputs.forEach(other => { if (other !== input) other.value = input.value; });
        try { localStorage.setItem(STORE, input.value); } catch (error) { /* stockage indisponible */ }
        refreshCounts();
      });
    });
    refreshCounts();
  }

  window.outilsNames = { list: currentNames, parse: parseNames, refresh: refreshCounts };

  /* ---------------- Formation de groupes ---------------- */
  const output = document.getElementById("groupOutput");
  const buildButton = document.getElementById("buildGroups");
  if (!output || !buildButton) return;

  function shuffle(list) {
    const copy = list.slice();
    for (let i = copy.length - 1; i > 0; i -= 1) {
      const j = Math.floor(Math.random() * (i + 1));
      [copy[i], copy[j]] = [copy[j], copy[i]];
    }
    return copy;
  }

  function distribute(names, mode, value, mix) {
    const list = mix ? shuffle(names) : names.slice();
    const size = Math.max(1, Math.min(20, parseInt(value, 10) || 1));
    const count = mode === "size"
      ? Math.max(1, Math.ceil(list.length / size))
      : Math.max(1, Math.min(size, list.length));

    const groups = Array.from({ length: count }, () => []);
    list.forEach((name, index) => { groups[index % count].push(name); });
    return groups.filter(group => group.length);
  }

  function renderGroups(groups) {
    output.innerHTML = "";
    groups.forEach((group, index) => {
      const card = document.createElement("article");
      card.className = "group-card";

      const head = document.createElement("header");
      const title = document.createElement("strong");
      title.textContent = "Groupe " + (index + 1);
      const count = document.createElement("span");
      count.textContent = group.length + (group.length > 1 ? " élèves" : " élève");
      head.append(title, count);

      const list = document.createElement("ol");
      group.forEach(name => {
        const item = document.createElement("li");
        item.textContent = name;
        list.append(item);
      });

      card.append(head, list);
      output.append(card);
    });
  }

  function groupsAsText() {
    return [...output.querySelectorAll(".group-card")].map((card, index) => {
      const names = [...card.querySelectorAll("li")].map(li => li.textContent);
      return "Groupe " + (index + 1) + " : " + names.join(", ");
    }).join("\n");
  }

  buildButton.addEventListener("click", () => {
    const names = currentNames();
    if (names.length < 2) {
      output.innerHTML = '<p class="chapter-empty">Ajoute au moins deux prénoms pour former des groupes.</p>';
      return;
    }
    const mode = (document.querySelector('input[name="groupMode"]:checked') || {}).value || "count";
    const value = (document.getElementById("groupValue") || {}).value || "4";
    const mix = !!(document.getElementById("groupShuffle") || {}).checked;
    renderGroups(distribute(names, mode, value, mix));
  });

  const copyButton = document.getElementById("copyGroups");
  if (copyButton) {
    copyButton.addEventListener("click", async () => {
      const text = groupsAsText();
      if (!text) return;
      try {
        await navigator.clipboard.writeText(text);
        copyButton.textContent = "Copié !";
      } catch (error) {
        copyButton.textContent = "Copie impossible";
      }
      setTimeout(() => { copyButton.textContent = "Copier"; }, 1600);
    });
  }

  const printButton = document.getElementById("printGroups");
  if (printButton) {
    printButton.addEventListener("click", () => window.print());
  }
})();

/* --------------------------------------------------------------------------
   Mode projection : plein écran et sortie au clavier
   --------------------------------------------------------------------------
   Chaque outil peut être ouvert seul (outils.php?projeter=...). En mode
   projection, on propose un vrai plein écran et la touche Échap pour sortir.
   -------------------------------------------------------------------------- */
(function () {
  const enProjection = document.body.classList.contains("outils-projection");
  if (!enProjection) return;

  const bouton = document.querySelector("[data-projection-fullscreen]");

  function mettreAJour() {
    if (!bouton) return;
    const actif = document.fullscreenElement !== null;
    bouton.textContent = actif ? "Quitter le plein écran" : "Plein écran";
  }

  if (bouton) {
    bouton.addEventListener("click", () => {
      const doc = document.documentElement;
      if (document.fullscreenElement) {
        document.exitFullscreen?.();
      } else if (doc.requestFullscreen) {
        doc.requestFullscreen().catch(() => {});
      }
    });
    document.addEventListener("fullscreenchange", mettreAJour);
    mettreAJour();
  }

  // Échap : quitte le plein écran, puis revient à la page des outils.
  document.addEventListener("keydown", event => {
    if (event.key !== "Escape") return;
    if (document.fullscreenElement) {
      document.exitFullscreen?.();
      event.preventDefault();
      return;
    }
    const sortie = document.querySelector(".projection-actions a");
    if (sortie) window.location.href = sortie.href;
  });
})();
