/* Tirage au sort : un eleve au hasard, ou l'ordre de passage de la classe. */
(function () {
  const display = document.getElementById("tirageDisplay");
  if (!display) return;

  const caption = document.getElementById("tirageCaption");
  const order = document.getElementById("tirageOrder");
  const stage = document.getElementById("tirageStage");
  let rolling = false;

  function names() {
    return window.outilsNames ? window.outilsNames.list() : [];
  }

  function randomOf(list) {
    return list[Math.floor(Math.random() * list.length)];
  }

  function shuffle(list) {
    const copy = list.slice();
    for (let i = copy.length - 1; i > 0; i -= 1) {
      const j = Math.floor(Math.random() * (i + 1));
      [copy[i], copy[j]] = [copy[j], copy[i]];
    }
    return copy;
  }

  function drawOne() {
    const list = names();
    if (!list.length) {
      display.textContent = "—";
      caption.textContent = "Ajoute d’abord des prénoms dans l’onglet Groupes.";
      return;
    }
    if (rolling) return;
    rolling = true;
    if (stage) stage.classList.add("is-rolling");

    // Defilement rapide, puis arret sur un nom tire au hasard.
    const finalName = randomOf(list);
    let ticks = 0;
    const timer = setInterval(() => {
      display.textContent = randomOf(list);
      ticks += 1;
      if (ticks > 14) {
        clearInterval(timer);
        display.textContent = finalName;
        caption.textContent = list.length + " élève" + (list.length > 1 ? "s" : "") + " dans le tirage";
        rolling = false;
        if (stage) stage.classList.remove("is-rolling");
      }
    }, 70);
  }

  function drawAll() {
    const list = names();
    if (list.length < 2) {
      caption.textContent = "Ajoute au moins deux prénoms pour un ordre de passage.";
      return;
    }
    const shuffled = shuffle(list);
    display.textContent = "Ordre de passage";
    caption.textContent = shuffled.length + " élèves";
    if (!order) return;
    order.innerHTML = "";
    shuffled.forEach((name, index) => {
      const item = document.createElement("li");
      const rank = document.createElement("span");
      rank.className = "tirage-rank";
      rank.textContent = String(index + 1);
      const label = document.createElement("strong");
      label.textContent = name;
      item.append(rank, label);
      order.append(item);
    });
  }

  const one = document.getElementById("drawOne");
  const all = document.getElementById("drawAll");
  const reset = document.getElementById("drawReset");
  if (one) one.addEventListener("click", drawOne);
  if (all) all.addEventListener("click", drawAll);
  if (reset) {
    reset.addEventListener("click", () => {
      display.textContent = "—";
      if (caption) caption.textContent = "Prêt à tirer";
      if (order) order.innerHTML = "";
    });
  }
})();
