/* Des, chronometre et horloge pour les activites de classe. */
(function () {
  /* ---------------- Des ---------------- */
  const row = document.getElementById("diceRow");
  const roll = document.getElementById("rollDice");
  const count = document.getElementById("diceCount");

  function diceCount() {
    return Math.max(1, Math.min(4, parseInt(count && count.value, 10) || 1));
  }

  function renderDice(values) {
    if (!row) return;
    row.innerHTML = "";
    values.forEach(value => {
      const die = document.createElement("span");
      die.className = "die";
      die.textContent = String(value);
      row.append(die);
    });
  }

  if (row && roll) {
    renderDice([1, 1]);
    roll.addEventListener("click", () => {
      const total = diceCount();
      row.classList.add("is-rolling");
      let ticks = 0;
      const timer = setInterval(() => {
        renderDice(Array.from({ length: total }, () => 1 + Math.floor(Math.random() * 6)));
        ticks += 1;
        if (ticks > 8) {
          clearInterval(timer);
          const final = Array.from({ length: total }, () => 1 + Math.floor(Math.random() * 6));
          renderDice(final);
          row.classList.remove("is-rolling");
          const sum = final.reduce((a, b) => a + b, 0);
          const summary = document.getElementById("diceTotal");
          if (summary) summary.textContent = "Total : " + sum;
        }
      }, 80);
    });
    if (count) {
      count.addEventListener("change", () => renderDice(Array.from({ length: diceCount() }, () => 1)));
    }
  }

  /* ---------------- Chronometre ---------------- */
  const chrono = document.getElementById("chronoDisplay");
  if (chrono) {
    let running = false;
    let startedAt = 0;
    let elapsed = 0;
    let raf = null;

    function format(ms) {
      const total = Math.floor(ms / 100) / 10;
      const minutes = String(Math.floor(total / 60)).padStart(2, "0");
      const seconds = (total % 60).toFixed(1).padStart(4, "0");
      return minutes + ":" + seconds;
    }

    function tick() {
      if (!running) return;
      chrono.textContent = format(elapsed + (Date.now() - startedAt));
      raf = requestAnimationFrame(tick);
    }

    const toggle = document.getElementById("chronoStart");
    const reset = document.getElementById("chronoReset");
    if (toggle) {
      toggle.addEventListener("click", () => {
        if (running) {
          running = false;
          elapsed += Date.now() - startedAt;
          if (raf) cancelAnimationFrame(raf);
          toggle.textContent = "Reprendre";
        } else {
          running = true;
          startedAt = Date.now();
          toggle.textContent = "Pause";
          tick();
        }
      });
    }
    if (reset) {
      reset.addEventListener("click", () => {
        running = false;
        elapsed = 0;
        if (raf) cancelAnimationFrame(raf);
        chrono.textContent = "00:00.0";
        if (toggle) toggle.textContent = "Démarrer";
      });
    }
  }

  /* ---------------- Horloge ---------------- */
  const clock = document.getElementById("clockDisplay");
  if (clock) {
    const update = () => {
      clock.textContent = new Date().toLocaleTimeString("fr-FR", { hour: "2-digit", minute: "2-digit" });
    };
    update();
    setInterval(update, 10000);
  }
})();
