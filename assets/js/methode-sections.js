/* Fiche méthode longue (« Apprendre à apprendre »).
   ----------------------------------------------------------------------------
   Le sommaire déjà présent dans le contenu (.learn2-nav + .learn2-section) est
   réorganisé en index latéral groupé :
     • regroupement par « kicker » (Fondations, Mémoire, En classe, …) ;
     • numérotation 01, 02… pour scanner vite ;
     • section en cours surlignée pendant le défilement ;
     • sur téléphone, l'index devient un bloc repliable « Sommaire ».

   Amélioration progressive : sans JavaScript, le sommaire reste une liste de
   liens d'ancre et toutes les sections restent visibles. */
(function () {
  const racine = document.querySelector(".method-detail");
  if (!racine) return;

  const nav = racine.querySelector(".learn2-nav");
  const sidebar = racine.querySelector(".learn2-sidebar");
  if (!nav || !sidebar) return;

  const liens = [...nav.querySelectorAll('a[href^="#"]')];
  const sections = liens
    .map(lien => document.getElementById(lien.getAttribute("href").slice(1)))
    .filter(el => el && el.classList.contains("learn2-section"));

  if (liens.length < 2 || sections.length < 2) return;

  /* --- 1. Regroupement par thème ---------------------------------------
     Les sections portent chacune un « kicker » propre (Fondations, Mémoire,
     Classe, Maison…). Tel quel, on obtenait dix groupes pour douze sections :
     illisible. On les ramène à trois entrées de lecture. */
  const THEMES = [
    { cle: "principes", titre: "Les principes", kickers: ["fondations", "mémoire", "comprendre", "piloter", "attention"] },
    { cle: "quotidien", titre: "Au quotidien", kickers: ["classe", "maison", "santé", "routine"] },
    { cle: "matiere", titre: "Par matière", kickers: ["matière", "matiere"] }
  ];
  const nettoyer = texte => texte.replace(/^[^\p{L}\p{N}]+/u, "").trim();

  const groupes = new Map(THEMES.map(t => [t.titre, []]));
  liens.forEach((lien, index) => {
    const id = lien.getAttribute("href").slice(1);
    const section = document.getElementById(id);
    if (!section) return;
    const kicker = section.querySelector(".learn2-kicker");
    const brut = (kicker ? kicker.textContent : "").trim().toLocaleLowerCase("fr");
    const theme = THEMES.find(t => t.kickers.includes(brut)) || THEMES[0];
    groupes.get(theme.titre).push({ lien, index: index + 1, id });
  });

  nav.innerHTML = "";
  nav.setAttribute("role", "list");
  groupes.forEach((entrees, theme) => {
    if (!entrees.length) return;
    const bloc = document.createElement("div");
    bloc.className = "learn2-index-group";

    const titre = document.createElement("p");
    titre.className = "learn2-index-label";
    titre.textContent = theme;
    bloc.append(titre);

    entrees.forEach(({ lien, index, id }) => {
      const numero = document.createElement("span");
      numero.className = "learn2-index-num";
      numero.textContent = String(index).padStart(2, "0");
      numero.setAttribute("aria-hidden", "true");
      const libelle = document.createElement("span");
      libelle.className = "learn2-index-text";
      libelle.textContent = nettoyer(lien.textContent);
      lien.replaceChildren(numero, libelle);
      lien.dataset.section = id;
      bloc.append(lien);
    });

    nav.append(bloc);
  });

  /* --- 2. Index repliable sur téléphone -------------------------------- */
  const details = document.createElement("details");
  details.className = "learn2-index";
  const resume = document.createElement("summary");
  resume.className = "learn2-index-summary";
  resume.innerHTML = "<span>Sommaire</span><span class=\"learn2-index-count\">" + liens.length + " sections</span>";
  sidebar.replaceChildren(details);
  details.append(resume, nav);

  const mobile = window.matchMedia("(max-width: 1023px)");
  const syncOuverture = () => { details.open = !mobile.matches; };
  syncOuverture();
  if (mobile.addEventListener) mobile.addEventListener("change", syncOuverture);

  /* --- 3. Section en cours --------------------------------------------- */
  const parId = new Map(liens.map(lien => [lien.dataset.section, lien]));
  const surligner = id => {
    liens.forEach(lien => lien.classList.toggle("is-active", lien.dataset.section === id));
  };

  /* Calcul direct plutôt qu'IntersectionObserver : avec une bande étroite,
     le repère restait bloqué sur la section précédente. Ici, la section active
     est simplement la dernière dont le titre est passé au-dessus de la ligne
     de lecture. */
  let positions = [];
  const mesurer = () => {
    positions = sections.map(section => ({
      id: section.id,
      top: section.getBoundingClientRect().top + window.scrollY
    }));
  };
  const surlignerCourant = () => {
    if (!positions.length) return;
    const ligne = window.scrollY + 160;
    let courant = positions[0].id;
    positions.forEach(p => { if (p.top <= ligne) courant = p.id; });
    surligner(courant);
  };

  mesurer();
  surlignerCourant();
  window.addEventListener("load", () => { mesurer(); surlignerCourant(); });
  window.addEventListener("resize", () => { mesurer(); surlignerCourant(); });

  let planifie = false;
  window.addEventListener("scroll", () => {
    if (planifie) return;
    planifie = true;
    requestAnimationFrame(() => { surlignerCourant(); planifie = false; });
  }, { passive: true });

  /* --- 4. Fermer l'index mobile après un choix ------------------------- */
  liens.forEach(lien => {
    lien.addEventListener("click", () => {
      if (mobile.matches) details.open = false;
    });
  });
})();
