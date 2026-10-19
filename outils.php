<?php
require_once __DIR__ . '/includes/repository.php';

/* Chaque outil peut etre ouvert seul, en grand, pour le videoprojecteur :
   outils.php?projeter=minuteur (idem groupes, tirage, consignes, animation). */
$outilsProjetables = [
    'minuteur'  => 'Minuteur',
    'groupes'   => 'Formation de groupes',
    'tirage'    => 'Tirage au sort',
    'consignes' => 'Consignes',
    'animation' => 'Dés & chrono',
];
$outilProjete = strtolower(trim((string)($_GET['projeter'] ?? '')));
$modeProjection = isset($outilsProjetables[$outilProjete]);

page_head(
    $modeProjection ? $outilsProjetables[$outilProjete] . ' — projection' : 'Outils de classe',
    $modeProjection
        ? 'Affichage plein écran pour le vidéoprojecteur.'
        : 'Minuteur, formation de groupes, tirage au sort, consignes à projeter et dés pour animer la séance.',
    [
        'math' => false,
        'robots' => !$modeProjection,
        'body_class' => $modeProjection ? ['outils-projection'] : [],
        'body_attrs' => $modeProjection ? ['data-projection' => $outilProjete] : [],
    ]
);
if (!$modeProjection) site_header();
?>
<main id="main-content" class="page-container student-page outils-page">
<?php if ($modeProjection): ?>
  <div class="projection-bar">
    <span class="projection-title"><?= icon('expand', 'projection-icon', 18) ?><?= h($outilsProjetables[$outilProjete]) ?></span>
    <span class="projection-actions">
      <button type="button" class="btn-secondary projection-fs" data-projection-fullscreen>Plein écran</button>
      <a class="btn-secondary" href="<?= h(base_path('outils.php#' . $outilProjete)) ?>">Quitter</a>
    </span>
  </div>
<?php else: ?>
  <header class="outils-hero">
    <span class="badge">Salle de classe</span>
    <h1 class="page-title mt-4">Outils de classe</h1>
    <p class="chapter-hero-summary">Minuteur, groupes, tirage au sort, consignes à projeter et dés : tout pour animer une séance, pensé pour le vidéoprojecteur.</p>
  </header>

<?php endif; ?>
<?php if (!$modeProjection): ?>
  <nav class="chapter-tabs outils-tabs" data-tool-tabs aria-label="Outils de classe">
    <a class="chapter-tab is-active" href="#minuteur" data-tool-target="minuteur"><?= icon('clock', 'chapter-tab-icon', 18) ?><span>Minuteur</span></a>
    <a class="chapter-tab" href="#groupes" data-tool-target="groupes"><?= icon('users', 'chapter-tab-icon', 18) ?><span>Groupes</span></a>
    <a class="chapter-tab" href="#tirage" data-tool-target="tirage"><?= icon('dice', 'chapter-tab-icon', 18) ?><span>Tirage au sort</span></a>
    <a class="chapter-tab" href="#consignes" data-tool-target="consignes"><?= icon('sign', 'chapter-tab-icon', 18) ?><span>Consignes</span></a>
    <a class="chapter-tab" href="#animation" data-tool-target="animation"><?= icon('activity', 'chapter-tab-icon', 18) ?><span>Dés & chrono</span></a>
  </nav>
<?php endif; ?>

  <div class="outils-panels mt-6" data-tool-panels>

    <!-- ================= Minuteur ================= -->
    <section id="minuteur" class="outil-panel" data-tool-panel aria-label="Minuteur de classe">
      <a class="outil-project" href="<?= h(base_path('outils.php?projeter=minuteur')) ?>" target="_blank" rel="noopener"><?= icon('expand', 'btn-icon', 16) ?>Projeter cet outil</a>
      <div class="timer-board">
        <div class="timer-stage" id="timerStage">
          <div class="timer-mode-row">
            <div class="timer-title">
              <p class="timer-kicker">Temps et rythme</p>
              <h2 id="timer-title">Minuteur de classe</h2>
            </div>
            <button type="button" class="timer-btn secondary" data-fullscreen><?= icon('expand', 'btn-icon', 16) ?>Plein écran</button>
          </div>

          <div class="timer-display-wrap" aria-live="polite">
            <div>
              <p class="main-display" id="mainDisplay">05:00</p>
              <p class="display-caption" id="displayCaption">Travail individuel silencieux</p>
            </div>
          </div>

          <div>
            <div class="timer-progress" aria-hidden="true"><span id="progressBar"></span></div>
            <div class="timer-controls" aria-label="Commandes du minuteur">
              <button type="button" class="timer-btn" id="startPauseBtn">Démarrer</button>
              <button type="button" class="timer-btn secondary" id="resetBtn">Réinitialiser</button>
              <button type="button" class="timer-btn sound" id="soundBtn" aria-label="Jouer la sonnerie">Sonnerie</button>
            </div>
          </div>
        </div>

        <aside class="timer-side" aria-label="Réglages et signal de classe">
          <section class="timer-card">
            <h2>Durée</h2>
            <div class="timer-input-row">
              <label for="minutesInput">Minutes
                <input id="minutesInput" type="number" min="0" max="180" value="5" inputmode="numeric">
              </label>
              <label for="secondsInput">Secondes
                <input id="secondsInput" type="number" min="0" max="59" value="0" inputmode="numeric">
              </label>
              <label for="signalTextInput">Consigne affichée
                <input id="signalTextInput" type="text" value="Silence">
              </label>
            </div>
            <div class="preset-grid" role="group" aria-label="Durées rapides">
              <button type="button" class="preset-btn" data-minutes="2">2 min</button>
              <button type="button" class="preset-btn" data-minutes="5">5 min</button>
              <button type="button" class="preset-btn" data-minutes="10">10 min</button>
              <button type="button" class="preset-btn" data-minutes="15">15 min</button>
              <button type="button" class="preset-btn" data-minutes="20">20 min</button>
              <button type="button" class="preset-btn" data-minutes="30">30 min</button>
            </div>
          </section>

          <section class="timer-card traffic-panel">
            <div class="traffic-light" id="trafficLight" data-active="green" role="group" aria-label="Feu de niveau sonore">
              <button type="button" class="signal-btn" data-signal="red" aria-label="Feu rouge : trop de bruit" aria-pressed="false"></button>
              <button type="button" class="signal-btn" data-signal="orange" aria-label="Feu orange : vigilance" aria-pressed="false"></button>
              <button type="button" class="signal-btn" data-signal="green" aria-label="Feu vert : niveau sonore correct" aria-pressed="true"></button>
            </div>
            <div class="signal-copy">
              <strong id="signalTitle">Feu vert</strong>
              <p id="signalDescription">Niveau sonore correct, la classe peut continuer.</p>
              <span class="signal-badge" id="signalBadge">Ambiance stable</span>
            </div>
          </section>

          <section class="timer-card">
            <h2>Projeter</h2>
            <p class="timer-footer-note">Le minuteur et le feu sont pensés pour le vidéoprojecteur. La sonnerie ne se lance qu’après un clic, comme l’imposent les navigateurs. Le raccourci <kbd>Maj</kbd> + <kbd>F</kbd> bascule en plein écran.</p>
          </section>
        </aside>
      </div>
    </section>

    <!-- ================= Groupes ================= -->
    <section id="groupes" class="outil-panel" data-tool-panel aria-label="Formation de groupes">
      <a class="outil-project" href="<?= h(base_path('outils.php?projeter=groupes')) ?>" target="_blank" rel="noopener"><?= icon('expand', 'btn-icon', 16) ?>Projeter cet outil</a>
      <div class="outil-head">
        <h2 class="section-title">Former des groupes</h2>
        <p class="muted">Colle la liste des prénoms (un par ligne ou séparés par des virgules), choisis le nombre de groupes, puis lance la répartition.</p>
      </div>

      <div class="outil-grid">
        <div class="outil-card">
          <label class="outil-label" for="groupNames">Prénoms des élèves</label>
          <textarea id="groupNames" class="outil-textarea" rows="10" data-names-input placeholder="Awa&#10;Basile&#10;Chloé&#10;Diego&#10;Emma&#10;Farid"></textarea>
          <p class="outil-hint"><span data-names-count>0</span> élève(s) · liste enregistrée dans ce navigateur</p>
        </div>

        <div class="outil-card">
          <fieldset class="outil-fieldset">
            <legend>Répartition</legend>
            <label class="outil-radio"><input type="radio" name="groupMode" value="count" checked> Nombre de groupes</label>
            <label class="outil-radio"><input type="radio" name="groupMode" value="size"> Taille des groupes</label>
          </fieldset>

          <label class="outil-label" for="groupValue">Valeur</label>
          <input id="groupValue" class="outil-input" type="number" min="1" max="20" value="4" inputmode="numeric">

          <label class="outil-switch"><input type="checkbox" id="groupShuffle" checked> Mélanger les élèves</label>

          <div class="outil-actions">
            <button type="button" class="btn-primary" id="buildGroups">Former les groupes</button>
            <button type="button" class="btn-secondary" id="copyGroups"><?= icon('copy', 'btn-icon', 16) ?>Copier</button>
            <button type="button" class="btn-secondary" id="printGroups"><?= icon('printer', 'btn-icon', 16) ?>Imprimer</button>
          </div>
        </div>
      </div>

      <div class="group-output" id="groupOutput" aria-live="polite"></div>
    </section>

    <!-- ================= Tirage au sort ================= -->
    <section id="tirage" class="outil-panel" data-tool-panel aria-label="Tirage au sort">
      <a class="outil-project" href="<?= h(base_path('outils.php?projeter=tirage')) ?>" target="_blank" rel="noopener"><?= icon('expand', 'btn-icon', 16) ?>Projeter cet outil</a>
      <div class="outil-head">
        <h2 class="section-title">Tirage au sort</h2>
        <p class="muted">Tire un élève au hasard, ou affiche l’ordre de passage de toute la classe. La liste est la même que dans l’onglet Groupes.</p>
      </div>

      <div class="tirage-stage" id="tirageStage">
        <p class="tirage-display" id="tirageDisplay">—</p>
        <p class="tirage-caption" id="tirageCaption">Prêt à tirer</p>
      </div>

      <div class="outil-actions">
        <button type="button" class="btn-primary" id="drawOne">Tirer un élève</button>
        <button type="button" class="btn-secondary" id="drawAll">Ordre de passage</button>
        <button type="button" class="btn-secondary" id="drawReset">Effacer</button>
        <button type="button" class="btn-secondary" data-fullscreen><?= icon('expand', 'btn-icon', 16) ?>Plein écran</button>
      </div>

      <ol class="tirage-order" id="tirageOrder"></ol>

      <div class="outil-card mt-6">
        <label class="outil-label" for="drawNames">Prénoms utilisés pour le tirage</label>
        <textarea id="drawNames" class="outil-textarea" rows="6" data-names-input placeholder="La liste se remplit depuis l’onglet Groupes."></textarea>
        <p class="outil-hint"><span data-names-count>0</span> élève(s)</p>
      </div>
    </section>

    <!-- ================= Consignes ================= -->
    <section id="consignes" class="outil-panel" data-tool-panel aria-label="Consignes à projeter">
      <a class="outil-project" href="<?= h(base_path('outils.php?projeter=consignes')) ?>" target="_blank" rel="noopener"><?= icon('expand', 'btn-icon', 16) ?>Projeter cet outil</a>
      <div class="outil-head">
        <h2 class="section-title">Consignes à projeter</h2>
        <p class="muted">Affiche une consigne en très grand sur le vidéoprojecteur : symbole de travail, mode d’activité ou message personnalisé.</p>
      </div>

      <div class="signal-grid" role="group" aria-label="Consignes rapides">
        <button type="button" class="signal-tile" data-consigne="silence" data-icon="🤫" data-tone="calm">Silence</button>
        <button type="button" class="signal-tile" data-consigne="chuchoter" data-icon="🗣️" data-tone="calm">Chuchoter</button>
        <button type="button" class="signal-tile" data-consigne="individuel" data-icon="✍️" data-tone="work">Travail individuel</button>
        <button type="button" class="signal-tile" data-consigne="groupe" data-icon="👥" data-tone="work">Travail de groupe</button>
        <button type="button" class="signal-tile" data-consigne="question" data-icon="✋" data-tone="ask">J’ai une question</button>
        <button type="button" class="signal-tile" data-consigne="oral" data-icon="🎤" data-tone="work">Prise de parole</button>
        <button type="button" class="signal-tile" data-consigne="ecoute" data-icon="👂" data-tone="calm">Écoute</button>
        <button type="button" class="signal-tile" data-consigne="rangement" data-icon="🎒" data-tone="end">Rangement</button>
      </div>

      <div class="consigne-stage" id="consigneStage" data-tone="neutral">
        <span class="consigne-icon" id="consigneIcon" aria-hidden="true">🪧</span>
        <p class="consigne-text" id="consigneText">Choisis une consigne</p>
      </div>

      <div class="outil-card mt-5">
        <label class="outil-label" for="consigneCustom">Consigne personnalisée</label>
        <div class="outil-inline">
          <input id="consigneCustom" class="outil-input" type="text" placeholder="Ex. : Ouvrez le manuel page 42">
          <button type="button" class="btn-primary" id="consigneShow">Afficher en grand</button>
          <button type="button" class="btn-secondary" data-fullscreen>⛶ Plein écran</button>
        </div>
      </div>
    </section>

    <!-- ================= Dés & chrono ================= -->
    <section id="animation" class="outil-panel" data-tool-panel aria-label="Dés, chronomètre et horloge">
      <a class="outil-project" href="<?= h(base_path('outils.php?projeter=animation')) ?>" target="_blank" rel="noopener"><?= icon('expand', 'btn-icon', 16) ?>Projeter cet outil</a>
      <div class="outil-head">
        <h2 class="section-title">Dés, chronomètre et horloge</h2>
        <p class="muted">Pour les activités ludiques : lancer de dés, chronomètre et horloge en grand.</p>
      </div>

      <div class="outil-grid">
        <div class="outil-card">
          <h3>Dés</h3>
          <div class="outil-inline">
            <label class="outil-label" for="diceCount">Nombre de dés</label>
            <input id="diceCount" class="outil-input is-narrow" type="number" min="1" max="4" value="2" inputmode="numeric">
          </div>
          <div class="dice-row" id="diceRow" aria-live="polite"></div>
          <p class="dice-total" id="diceTotal" aria-live="polite"></p>
          <div class="outil-actions">
            <button type="button" class="btn-primary" id="rollDice">Lancer les dés</button>
          </div>
        </div>

        <div class="outil-card">
          <h3>Chronomètre</h3>
          <p class="chrono-display" id="chronoDisplay">00:00.0</p>
          <div class="outil-actions">
            <button type="button" class="btn-primary" id="chronoStart">Démarrer</button>
            <button type="button" class="btn-secondary" id="chronoReset">Remettre à zéro</button>
            <button type="button" class="btn-secondary" data-fullscreen>⛶ Plein écran</button>
          </div>
        </div>

        <div class="outil-card">
          <h3>Horloge</h3>
          <p class="clock-display" id="clockDisplay">--:--</p>
        </div>
      </div>
    </section>

  </div>
</main>
<script src="<?= h(base_path('assets/js/timer-classe.js')) ?>" defer></script>
<script src="<?= h(base_path('assets/js/outils.js')) ?>" defer></script>
<script src="<?= h(base_path('assets/js/outils-tirage.js')) ?>" defer></script>
<script src="<?= h(base_path('assets/js/outils-consignes.js')) ?>" defer></script>
<script src="<?= h(base_path('assets/js/outils-animation.js')) ?>" defer></script>
<?php site_footer(); ?>
