/* QCM public : suivi de progression avant correction. */
(function () {
  const form = document.querySelector("[data-qcm-form]");
  if (!form) return;
  const progress = form.querySelector("[data-qcm-progress]");
  const names = new Set(
    [...form.querySelectorAll('input[type="radio"]')].map(input => input.name)
  );
  const total = names.size;
  const submit = form.querySelector('button[type="submit"]');

  function update() {
    let answered = 0;
    names.forEach(name => {
      if (form.querySelector('input[name="' + CSS.escape(name) + '"]:checked')) answered++;
    });
    if (progress) {
      progress.textContent = answered + " réponse" + (answered > 1 ? "s" : "") + " sur " + total;
      progress.classList.toggle("is-complete", answered === total);
    }
    if (submit) {
      submit.disabled = answered === 0;
      submit.setAttribute("aria-disabled", answered === 0 ? "true" : "false");
    }
  }

  form.addEventListener("change", update);
  update();
})();
