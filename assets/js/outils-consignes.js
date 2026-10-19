/* Consignes a projeter : symboles de travail et message personnalise. */
(function () {
  const stage = document.getElementById("consigneStage");
  const icon = document.getElementById("consigneIcon");
  const text = document.getElementById("consigneText");
  if (!stage || !text) return;

  function show(label, symbol, tone) {
    icon.textContent = symbol || "🪧";
    text.textContent = label;
    stage.dataset.tone = tone || "neutral";
  }

  document.querySelectorAll("[data-consigne]").forEach(tile => {
    tile.addEventListener("click", () => {
      document.querySelectorAll("[data-consigne]").forEach(other => other.classList.remove("is-active"));
      tile.classList.add("is-active");
      show(tile.textContent.trim(), tile.dataset.icon, tile.dataset.tone);
      const custom = document.getElementById("consigneCustom");
      if (custom) custom.value = "";
    });
  });

  const custom = document.getElementById("consigneCustom");
  const submit = document.getElementById("consigneShow");
  function showCustom() {
    const value = (custom.value || "").trim();
    if (!value) return;
    document.querySelectorAll("[data-consigne]").forEach(other => other.classList.remove("is-active"));
    show(value, "📌", "custom");
  }
  if (submit) submit.addEventListener("click", showCustom);
  if (custom) {
    custom.addEventListener("keydown", event => {
      if (event.key === "Enter") { event.preventDefault(); showCustom(); }
    });
  }
})();
