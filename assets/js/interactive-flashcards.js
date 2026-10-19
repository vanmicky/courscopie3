(function () {
  function readCards(root) {
    const dataNode = root.querySelector(".flash-data");
    if (!dataNode) throw new Error("flash-data missing");
    return JSON.parse(dataNode.textContent.trim());
  }

  /* Mélange de Fisher-Yates : ordre réellement aléatoire. */
  function shuffle(list) {
    const copy = list.slice();
    for (let i = copy.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [copy[i], copy[j]] = [copy[j], copy[i]];
    }
    return copy;
  }

  function renderFlash(root, provided) {
    let cards;
    try {
      cards = provided || readCards(root);
    } catch (error) {
      console.error("Erreur flashcards:", error);
      root.innerHTML = '<div class="quiz-feedback bad" style="display:block"><p class="font-black">Erreur de chargement des flashcards.</p></div>';
      return;
    }

    if (!cards.length) return;

    let index = 0;
    let flipped = false;
    let finished = false;
    let results = new Array(cards.length).fill(null);

    root.innerHTML = `
      <div class="interactive-flash">
        <div class="flash-progress" aria-live="polite">
          <span class="quiz-score-pill flash-count"></span>
          <span class="quiz-score-pill flash-score"></span>
        </div>
        <div class="flash-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-label="Progression de la révision"><span class="flash-bar-fill"></span></div>
        <div class="flash-stage">
          <button type="button" class="flash-card-clickable" aria-label="Afficher le verso" aria-pressed="false">
            <span class="flash-card-inner">
              <span class="flash-side front">
                <span class="flash-label">Recto</span>
                <strong class="flash-front-title"></strong>
                <span class="flash-card-image" hidden><img alt="Illustration de la question"></span>
                <span class="mt-3 text-base flash-front"></span>
                <span class="flash-hint">Clique ou appuie sur Entrée pour voir le verso.</span>
              </span>
              <span class="flash-side back">
                <span class="flash-label">Verso</span>
                <strong class="flash-answer-title">Réponse</strong>
                <span class="flash-card-image flash-card-image-back" hidden><img alt="Illustration de la réponse"></span>
                <span class="mt-3 text-base flash-back"></span>
              </span>
            </span>
          </button>
        </div>
        <p class="flash-instruction" aria-live="polite">Retourne la carte avant d'évaluer ta réponse.</p>
        <div class="flash-known-buttons" hidden aria-label="Résultat de cette carte">
          <button type="button" class="unknown"><span class="flash-key">1</span> Pas encore</button>
          <button type="button" class="known"><span class="flash-key">2</span> J'ai réussi</button>
        </div>
        <div class="flash-nav">
          <button type="button" class="secondary prev">← Précédente</button>
          <button type="button" class="flip">Voir le verso</button>
          <button type="button" class="next" disabled>Suivante →</button>
        </div>
        <div class="flash-tools">
          <button type="button" class="flash-shuffle">Mélanger</button>
          <span class="flash-shortcuts">Entrée : retourner · ← → : naviguer · 1 / 2 : évaluer</span>
        </div>
      </div>
    `;

    const cardBtn = root.querySelector(".flash-card-clickable");
    const countEl = root.querySelector(".flash-count");
    const scoreEl = root.querySelector(".flash-score");
    const barEl = root.querySelector(".flash-bar");
    const barFill = root.querySelector(".flash-bar-fill");
    const titleEl = root.querySelector(".flash-front-title");
    const imageEl = root.querySelector(".flash-card-image:not(.flash-card-image-back)");
    const imageImg = imageEl ? imageEl.querySelector("img") : null;
    const imageBackEl = root.querySelector(".flash-card-image-back");
    const imageBackImg = imageBackEl ? imageBackEl.querySelector("img") : null;
    const frontEl = root.querySelector(".flash-front");
    const backEl = root.querySelector(".flash-back");
    const instructionEl = root.querySelector(".flash-instruction");
    const ratingGroup = root.querySelector(".flash-known-buttons");
    const prevBtn = root.querySelector(".prev");
    const nextBtn = root.querySelector(".next");
    const flipBtn = root.querySelector(".flip");
    const knownBtn = root.querySelector(".known");
    const unknownBtn = root.querySelector(".unknown");
    const shuffleBtn = root.querySelector(".flash-shuffle");

    function save() {
      try {
        localStorage.setItem("flash:" + location.pathname + location.search, JSON.stringify({
          succeeded: results.filter(value => value === true).length,
          reviewed: results.filter(value => value !== null).length,
          total: cards.length,
          date: new Date().toISOString()
        }));
      } catch (error) {}
    }

    function renderMathSafe() {
      if (typeof window.renderMath === "function") {
        window.renderMath(root);
        return;
      }
      if (!window.renderMathInElement) return;
      try {
        window.renderMathInElement(root, {
          delimiters: [
            { left: "$$", right: "$$", display: true },
            { left: "\\[", right: "\\]", display: true },
            { left: "$", right: "$", display: false },
            { left: "\\(", right: "\\)", display: false }
          ],
          ignoredTags: ["script", "noscript", "style", "textarea", "pre", "code"],
          throwOnError: false
        });
      } catch (error) {}
    }

    function renderCompletion() {
      const succeeded = results.filter(value => value === true).length;
      const missed = results.filter(value => value === false).length;
      const pourcentage = Math.round((succeeded / cards.length) * 100);
      root.innerHTML = `
        <section class="flash-completion" aria-live="polite">
          <span class="badge">Révision terminée</span>
          <h2>${succeeded} réussite${succeeded > 1 ? "s" : ""} sur ${cards.length}</h2>
          <p class="flash-completion-score">${pourcentage} %</p>
          <p>${missed === 0
            ? "Toutes les cartes sont maîtrisées. Reviens dans quelques jours pour consolider."
            : `${missed} carte${missed > 1 ? "s" : ""} à revoir avant de considérer le chapitre su.`}</p>
          <div class="flash-completion-actions">
            ${missed > 0 ? '<button type="button" class="btn-primary flash-review-missed">Revoir les cartes à revoir</button>' : ""}
            <button type="button" class="${missed > 0 ? "btn-secondary" : "btn-primary"} flash-restart">Recommencer</button>
          </div>
        </section>
      `;
      root.querySelector(".flash-restart").addEventListener("click", () => renderFlash(root, cards));
      const missedBtn = root.querySelector(".flash-review-missed");
      if (missedBtn) {
        missedBtn.addEventListener("click", () => {
          const aRevoir = cards.filter((card, i) => results[i] === false);
          if (aRevoir.length) renderFlash(root, aRevoir);
        });
      }
    }

    function render() {
      if (finished) {
        renderCompletion();
        return;
      }

      const card = cards[index];
      const reviewed = results.filter(value => value !== null).length;
      const succeeded = results.filter(value => value === true).length;
      const pourcentage = cards.length ? Math.round((reviewed / cards.length) * 100) : 0;

      cardBtn.classList.toggle("is-flipped", flipped);
      cardBtn.setAttribute("aria-pressed", flipped ? "true" : "false");
      cardBtn.setAttribute("aria-label", flipped ? "Afficher le recto" : "Afficher le verso");
      countEl.textContent = `Carte ${index + 1} / ${cards.length}`;
      scoreEl.textContent = `Réussies : ${succeeded} · Vues : ${reviewed}`;
      barEl.setAttribute("aria-valuenow", String(pourcentage));
      barFill.style.width = pourcentage + "%";
      titleEl.textContent = card.type || "Question";
      // Illustration facultative, largeur choisie dans l'administration.
      if (imageEl && imageImg) {
        if (card.image) {
          imageImg.src = card.image;
          imageImg.style.width = Math.max(10, Math.min(100, Number(card.imageWidth) || 40)) + "%";
          imageEl.hidden = false;
        } else {
          imageImg.removeAttribute("src");
          imageEl.hidden = true;
        }
      }
      frontEl.innerHTML = card.front;
      backEl.innerHTML = card.back;
      if (imageBackEl && imageBackImg) {
        if (card.imageBack) {
          imageBackImg.src = card.imageBack;
          imageBackImg.style.width = Math.max(10, Math.min(100, Number(card.imageBackWidth) || 40)) + "%";
          imageBackEl.hidden = false;
        } else {
          imageBackImg.removeAttribute("src");
          imageBackEl.hidden = true;
        }
      }
      ratingGroup.hidden = !flipped;
      flipBtn.textContent = flipped ? "Voir le recto" : "Voir le verso";
      prevBtn.disabled = index === 0;
      nextBtn.disabled = results[index] === null;
      nextBtn.textContent = index === cards.length - 1 ? "Terminer" : "Suivante →";
      knownBtn.classList.toggle("is-selected", results[index] === true);
      unknownBtn.classList.toggle("is-selected", results[index] === false);
      instructionEl.textContent = flipped
        ? (results[index] === null ? "Indique maintenant si tu as réussi." : "Résultat enregistré. Tu peux passer à la suite.")
        : "Retourne la carte avant d'évaluer ta réponse.";
      window.setTimeout(renderMathSafe, 30);
    }

    function toggleCard() {
      flipped = !flipped;
      render();
      if (flipped) knownBtn.focus({ preventScroll: true });
    }

    function goTo(nextIndex) {
      index = Math.min(cards.length - 1, Math.max(0, nextIndex));
      flipped = false;
      render();
      cardBtn.focus({ preventScroll: true });
    }

    function rate(value) {
      if (!flipped) return;
      results[index] = value;
      save();
      render();
      nextBtn.focus({ preventScroll: true });
    }

    function avancer() {
      if (results[index] === null) return;
      if (index === cards.length - 1) {
        finished = true;
        render();
      } else {
        goTo(index + 1);
      }
    }

    cardBtn.addEventListener("click", toggleCard);
    flipBtn.addEventListener("click", toggleCard);
    prevBtn.addEventListener("click", () => goTo(index - 1));
    nextBtn.addEventListener("click", avancer);
    knownBtn.addEventListener("click", () => rate(true));
    unknownBtn.addEventListener("click", () => rate(false));
    shuffleBtn.addEventListener("click", () => {
      renderFlash(root, shuffle(cards));
    });

    /* Clavier : écoute au niveau du document, pour que les raccourcis
       fonctionnent sans avoir cliqué dans la carte. On ignore la saisie dans
       un champ de formulaire et les raccourcis système. */
    function onKey(event) {
      if (!document.body.contains(root)) return;
      const tag = (event.target.tagName || "").toLowerCase();
      if (["input", "textarea", "select"].includes(tag) || event.target.isContentEditable) return;
      if (event.metaKey || event.ctrlKey || event.altKey) return;
      if (document.querySelector("dialog[open]")) return;

      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        toggleCard();
      } else if (event.key === "ArrowRight") {
        event.preventDefault();
        avancer();
      } else if (event.key === "ArrowLeft") {
        event.preventDefault();
        if (index > 0) goTo(index - 1);
      } else if (event.key === "1") {
        event.preventDefault();
        rate(false);
      } else if (event.key === "2") {
        event.preventDefault();
        rate(true);
      }
    }
    document.addEventListener("keydown", onKey);
    root._flashCleanup = () => document.removeEventListener("keydown", onKey);

    render();
  }

  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("[data-interactive-flashcards]").forEach(root => renderFlash(root));
  });
})();
