(() => {
  const display = document.getElementById("mainDisplay");
  const caption = document.getElementById("displayCaption");
  const stage = document.getElementById("timerStage");
  const progress = document.getElementById("progressBar");
  const startPauseBtn = document.getElementById("startPauseBtn");
  const resetBtn = document.getElementById("resetBtn");
  const soundBtn = document.getElementById("soundBtn");
  const minutesInput = document.getElementById("minutesInput");
  const secondsInput = document.getElementById("secondsInput");
  const signalTextInput = document.getElementById("signalTextInput");
  const trafficLight = document.getElementById("trafficLight");
  const signalTitle = document.getElementById("signalTitle");
  const signalDescription = document.getElementById("signalDescription");
  const signalBadge = document.getElementById("signalBadge");
  const presetButtons = [...document.querySelectorAll("[data-minutes]")];

  let totalSeconds = 300;
  let remainingSeconds = totalSeconds;
  let timerId = null;
  let lastTick = 0;
  let audioContext = null;

  const signalStates = {
    red: {
      color: "#ef4444",
      title: "Feu rouge",
      description: "Stop bruit : on revient au calme maintenant.",
      badge: "Silence attendu",
      caption: "Retour au calme"
    },
    orange: {
      color: "#f59e0b",
      title: "Feu orange",
      description: "Attention, le volume monte : on baisse d'un cran.",
      badge: "Volume a surveiller",
      caption: "On baisse le volume"
    },
    green: {
      color: "#16a34a",
      title: "Feu vert",
      description: "Niveau sonore correct, la classe peut continuer.",
      badge: "Ambiance stable",
      caption: "Travail individuel silencieux"
    }
  };

  const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
  const pad = value => String(value).padStart(2, "0");

  const formatDuration = seconds => {
    const safeSeconds = Math.max(0, Math.ceil(seconds));
    const hours = Math.floor(safeSeconds / 3600);
    const minutes = Math.floor((safeSeconds % 3600) / 60);
    const secs = safeSeconds % 60;
    return hours > 0 ? `${hours}:${pad(minutes)}:${pad(secs)}` : `${pad(minutes)}:${pad(secs)}`;
  };

  const readInputs = () => {
    const minutes = clamp(Number.parseInt(minutesInput.value || "0", 10), 0, 180);
    const seconds = clamp(Number.parseInt(secondsInput.value || "0", 10), 0, 59);
    totalSeconds = Math.max(1, minutes * 60 + seconds);
    remainingSeconds = Math.min(remainingSeconds, totalSeconds);
    minutesInput.value = String(minutes);
    secondsInput.value = String(seconds);
  };

  const syncInputsFromTotal = () => {
    minutesInput.value = String(Math.floor(totalSeconds / 60));
    secondsInput.value = String(totalSeconds % 60);
  };

  const updateDisplay = () => {
    display.textContent = formatDuration(remainingSeconds);
    const ratio = totalSeconds > 0 ? remainingSeconds / totalSeconds : 0;
    progress.style.transform = `scaleX(${clamp(ratio, 0, 1)})`;
  };

  const stopTimer = () => {
    if (timerId) {
      window.clearInterval(timerId);
      timerId = null;
    }
    startPauseBtn.textContent = "Démarrer";
  };

  const tick = () => {
    const now = Date.now();
    const elapsed = (now - lastTick) / 1000;
    lastTick = now;
    remainingSeconds = Math.max(0, remainingSeconds - elapsed);
    updateDisplay();

    if (remainingSeconds <= 0) {
      stopTimer();
      stage.classList.add("is-finished");
      caption.textContent = "Temps terminé";
      playNokiaTone();
    }
  };

  const startTimer = () => {
    if (remainingSeconds <= 0) {
      readInputs();
      remainingSeconds = totalSeconds;
    }
    stage.classList.remove("is-finished");
    lastTick = Date.now();
    timerId = window.setInterval(tick, 200);
    startPauseBtn.textContent = "Pause";
  };

  const resetTimer = () => {
    stopTimer();
    readInputs();
    remainingSeconds = totalSeconds;
    stage.classList.remove("is-finished");
    caption.textContent = signalTextInput.value.trim() || signalStates[trafficLight.dataset.active].caption;
    updateDisplay();
  };

  const getAudioContext = () => {
    const Context = window.AudioContext || window.webkitAudioContext;
    if (!Context) return null;
    if (!audioContext) audioContext = new Context();
    return audioContext;
  };

  const playTone = (frequency, start, duration, gainValue = 0.12) => {
    const context = getAudioContext();
    if (!context) return;
    const oscillator = context.createOscillator();
    const gain = context.createGain();
    oscillator.type = "square";
    oscillator.frequency.setValueAtTime(frequency, start);
    gain.gain.setValueAtTime(0.0001, start);
    gain.gain.exponentialRampToValueAtTime(gainValue, start + 0.015);
    gain.gain.exponentialRampToValueAtTime(0.0001, start + duration);
    oscillator.connect(gain);
    gain.connect(context.destination);
    oscillator.start(start);
    oscillator.stop(start + duration + 0.02);
  };

  function playNokiaTone() {
    const context = getAudioContext();
    if (!context) return;
    if (context.state === "suspended") context.resume();

    const notes = [
      [659, .12], [587, .12], [370, .22], [415, .22],
      [554, .12], [494, .12], [294, .22], [330, .22],
      [494, .12], [440, .12], [277, .22], [330, .22],
      [440, .36]
    ];
    let when = context.currentTime + 0.02;
    notes.forEach(([frequency, duration]) => {
      playTone(frequency, when, duration);
      when += duration + 0.035;
    });
  }

  const setSignal = signal => {
    const state = signalStates[signal];
    if (!state) return;
    trafficLight.dataset.active = signal;
    document.documentElement.style.setProperty("--signal-color", state.color);
    signalTitle.textContent = state.title;
    signalDescription.textContent = state.description;
    signalBadge.textContent = state.badge;
    // État annoncé aux lecteurs d'écran et au style des pastilles.
    trafficLight.querySelectorAll("[data-signal]").forEach(button => {
      button.setAttribute("aria-pressed", button.dataset.signal === signal ? "true" : "false");
    });
    if (!signalTextInput.value.trim() || caption.textContent === signalStates.red.caption || caption.textContent === signalStates.orange.caption || caption.textContent === signalStates.green.caption) {
      caption.textContent = state.caption;
    }
  };

  presetButtons.forEach(button => {
    button.addEventListener("click", () => {
      totalSeconds = Number.parseInt(button.dataset.minutes, 10) * 60;
      remainingSeconds = totalSeconds;
      syncInputsFromTotal();
      resetTimer();
    });
  });

  document.querySelectorAll("[data-signal]").forEach(button => {
    button.addEventListener("click", () => setSignal(button.dataset.signal));
  });

  [minutesInput, secondsInput].forEach(input => {
    input.addEventListener("change", resetTimer);
    input.addEventListener("input", () => {
      if (!timerId) resetTimer();
    });
  });

  signalTextInput.addEventListener("input", () => {
    caption.textContent = signalTextInput.value.trim() || signalStates[trafficLight.dataset.active].caption;
  });

  startPauseBtn.addEventListener("click", () => {
    if (timerId) stopTimer();
    else startTimer();
  });

  resetBtn.addEventListener("click", resetTimer);
  soundBtn.addEventListener("click", playNokiaTone);

  setSignal("green");
  resetTimer();
})();
