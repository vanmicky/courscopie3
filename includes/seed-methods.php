<?php
// Donnees de depart des fiches methodes (V75).
// Utilisees quand la base n'est pas encore migree : la page /methodes.php
// reste complete avant l'import de database/migrate_v74_to_v75.sql.
return [
    [
        'id' => 1,
        'slug' => 'apprendre-a-apprendre',
        'title' => 'Apprendre à apprendre',
        'summary' => 'Guide clair : mémoire, attention, révisions espacées, téléphone, travail à la maison, maths, physique-chimie et NSI.',
        'domain' => 'Méthode générale',
        'levels' => 'Tous niveaux',
        'accent' => '#2563eb',
        'icon' => '🧠',
        'content' => '<a href="#" class="nav-link">← Toutes les méthodes</a>

  <section class="learn2-hero mt-5 p-6 sm:p-9">
    <div class="learn2-hero-grid">
      <div>
        <span class="badge">Méthode générale</span>
        <h1 class="page-title mt-4">Apprendre à apprendre</h1>
        <p class="mt-4 max-w-3xl text-base leading-8 muted">Depuis 40 ans, la psychologie cognitive a identifié ce qui fait vraiment progresser un élève — et ce qui donne seulement l&#x27; illusion de savoir. La conclusion tient en une phrase : on retient ce que l&#x27;on fait sortir de sa tête (se tester), pas ce que l&#x27;on fait entrer (relire). Et l&#x27;effet est démultiplié quand on espace les révisions, qu&#x27;on mélange les exercices, qu&#x27;on explique avec ses mots et qu&#x27;on dort assez.</p>
        <div class="mt-5">
          <span class="learn2-source-badge">📥 Contenu repris de la page fournie</span>
        </div>
      </div>
      <aside class="learn2-rule-card">
        <p><strong>Règle d’or :</strong> apprendre, ce n’est pas seulement relire. C’est essayer de retrouver, utiliser, expliquer, corriger et recommencer plus tard.</p>
      </aside>
    </div>
  </section>

  <section class="learn2-reading-guide mt-8">
    <strong>🛣️ Comment utiliser cette page ?</strong>
    <p class="muted">Le contenu de la page fournie est conservé, mais il est présenté en une seule colonne : sections lisibles, cartes courtes, checklists, routines et blocs d’action.</p>
  </section>

  <div class="learn2-layout mt-8">
    <aside class="learn2-sidebar" aria-label="Navigation Apprendre à apprendre">
      <span class="learn2-sidebar-title">Sommaire</span>
      <nav class="learn2-nav">
        <a href="#regles">⭐ Les règles d’or</a>
<a href="#ebbinghaus">📈 Oubli et rappels espacés</a>
<a href="#memoires">🧠 Les 3 mémoires</a>
<a href="#metacognition">🧭 Métacognition et auto-évaluation</a>
<a href="#classe">🧑‍🏫 Concentration en classe</a>
<a href="#maison">🏠 Travail à la maison</a>
<a href="#telephone">📱 Téléphone portable</a>
<a href="#sommeil">😴 Sommeil et corps</a>
<a href="#maths">√ Réviser les mathématiques</a>
<a href="#pc">⚗️ Réviser la physique-chimie</a>
<a href="#nsi">💻 Réviser la NSI</a>
<a href="#semaine">🗓️ Plan de semaine</a>
      </nav>
    </aside>

    <div class="grid gap-5">
      
<section id="regles" class="learn2-section" style="--learn2-accent:#2563eb">
  <div class="learn2-section-head">
    <span class="learn2-icon">⭐</span>
    <div>
      <span class="learn2-kicker">Fondations</span>
      <h2 class="learn2-title">Les règles d’or</h2>
      <p class="learn2-summary">Les stratégies les plus importantes du guide fourni, organisées en cartes courtes.</p>
    </div>
  </div>
  <div class="learn2-grid three">
    
<article class="learn2-card">
  <h3>❓ Se tester avant de relire</h3>
  <article class="learn-principle"><span class="num">1</span><p class="mt-2 muted">C\'est le résultat le plus solide de toute la recherche (« effet de test », Roediger &amp; Karpicke, 2006) : essayer de <strong>retrouver</strong> une information de mémoire la renforce beaucoup plus que la relire. Ferme le cahier, écris tout ce que tu sais, puis seulement vérifie. L\'effort de rappel — même quand tu te trompes — est exactement ce qui construit la mémoire.</p></article>
</article>
<article class="learn2-card">
  <h3>🕐 Espacer les révisions</h3>
  <article class="learn-principle"><span class="num">2</span><p class="mt-2 muted">Trois séances de 20 min réparties sur une semaine battent largement une séance de 60 min la veille — à temps de travail <strong>égal</strong>. Réactiver une notion juste avant de l\'oublier oblige le cerveau à la reconsolider plus profondément. Le bachotage marche pour le lendemain, puis s\'effondre.</p></article>
</article>
<article class="learn2-card">
  <h3>🔀 Mélanger les exercices</h3>
  <article class="learn-principle"><span class="num">3</span><p class="mt-2 muted">Faire 10 exercices du même type d\'affilée est confortable mais trompeur : au contrôle, personne ne te dit quelle méthode utiliser. Alterner les types d\'exercices (<em>interleaving</em>) t\'entraîne à <strong>choisir</strong> la bonne méthode, pas seulement à l\'appliquer. C\'est plus difficile — et c\'est pour ça que ça marche.</p></article>
</article>
<article class="learn2-card">
  <h3>🔗 Expliquer et relier</h3>
  <article class="learn-principle"><span class="num">4</span><p class="mt-2 muted">Une connaissance isolée s\'oublie ; une connaissance connectée reste. Demande-toi « pourquoi est-ce vrai ? », « à quoi ça ressemble ? », relie chaque notion à un exemple, un schéma, une formule. Si tu peux l\'expliquer à un camarade avec tes mots, tu la sais vraiment (technique de Feynman).</p></article>
</article>
<article class="learn2-card">
  <h3>🐞 Exploiter ses erreurs</h3>
  <article class="learn-principle"><span class="num">5</span><p class="mt-2 muted">Une erreur corrigée avec explication vaut de l\'or : elle te dit exactement où travailler. Pour chaque erreur, écris <em>pourquoi</em> tu t\'es trompé et <em>quelle règle</em> il fallait appliquer. Tiens un « carnet d\'erreurs » et refais l\'exercice quelques jours plus tard, sans regarder.</p></article>
</article>
<article class="learn2-card">
  <h3>🛡️ Protéger attention et sommeil</h3>
  <article class="learn-principle"><span class="num">6</span><p class="mt-2 muted">La mémoire de travail est minuscule : chaque distraction lui vole de la place pour comprendre. Et c\'est <strong>pendant le sommeil</strong> que le cerveau consolide ce que tu as appris dans la journée. Téléphone loin, nuits complètes : ce sont des méthodes de travail, pas des détails.</p></article>
</article>
<article class="learn2-card">
  <h3>⚠️ Attention aux fausses bonnes méthodes</h3>
  <div class="learn-warning mt-5">
<p class="mt-2 muted">La recherche (Dunlosky et coll., 2013) a comparé les techniques d\'étude : <strong>relire</strong> et <strong>surligner</strong> sont parmi les moins efficaces, alors que ce sont les plus utilisées par les élèves. Elles donnent une sensation de familiarité (« je reconnais le cours ») que le cerveau confond avec la maîtrise (« je saurais le refaire »). Ne confonds jamais les deux.</p>
</div>
</article>
  </div>
</section>

<section id="ebbinghaus" class="learn2-section" style="--learn2-accent:#2563eb">
  <div class="learn2-section-head">
    <span class="learn2-icon">📈</span>
    <div>
      <span class="learn2-kicker">Mémoire</span>
      <h2 class="learn2-title">Oubli et rappels espacés</h2>
      <p class="learn2-summary">Comprendre pourquoi on oublie et comment réactiver les notions au bon moment.</p>
    </div>
  </div>
  <div class="learn2-grid three">
    
<article class="learn2-card">
  <h3>📈 Pourquoi on oublie ?</h3>
  <article class="learn-card">
<p class="mt-2 muted">Dès 1885, Hermann Ebbinghaus a mesuré l\'oubli sur lui-même : sans révision, on perd la majorité d\'une information nouvelle en quelques jours. Ce n\'est pas un défaut — le cerveau trie et efface ce qui ne semble pas servir. <strong>Chaque rappel envoie le signal « ceci est important »</strong> : la courbe de l\'oubli devient alors de plus en plus plate, et la notion finit par tenir des mois.</p>
<div aria-label="Schéma simplifié de la courbe de l\'oubli" class="learn-mini-curve">
<svg role="img" viewbox="0 0 640 260">
<title>Courbe de l\'oubli et révisions espacées</title>
<line stroke="#94a3b8" stroke-width="2" x1="55" x2="610" y1="215" y2="215"></line>
<line stroke="#94a3b8" stroke-width="2" x1="55" x2="55" y1="30" y2="215"></line>
<path d="M60 55 C120 150, 180 188, 250 203 C350 218, 470 218, 600 219" fill="none" stroke="#ef4444" stroke-width="4"></path>
<path d="M60 55 C105 118, 145 145, 180 155 C190 105, 230 85, 270 78 C300 130, 350 148, 400 158 C415 105, 455 84, 500 76 C525 112, 560 132, 600 144" fill="none" stroke="#2563eb" stroke-width="4"></path>
<circle cx="180" cy="155" fill="#2563eb" r="7"></circle>
<circle cx="400" cy="158" fill="#2563eb" r="7"></circle>
<circle cx="500" cy="76" fill="#2563eb" r="7"></circle>
<text x="22" y="45">Mémoire</text>
<text x="560" y="245">Temps</text>
<text x="75" y="235">J0</text>
<text x="158" y="235">J1</text>
<text x="378" y="235">J3</text>
<text x="478" y="235">J7</text>
<text x="245" y="70">Rappels espacés</text>
<text x="330" y="205">Sans rappel</text>
</svg>
</div>
</article>
</article>
<article class="learn2-card">
  <h3>🔁 Le rythme J0 · J1 · J3 · J7</h3>
  <article class="learn-card">
<p class="mt-2 muted">Un plan simple qui suit la courbe : réviser <em>juste avant</em> d\'oublier, et toujours en se testant.</p>
<div class="learn-timeline mt-3">
<div class="learn-timeline-item"><span class="learn-date">J0</span><p>Le soir même : cahier fermé, écrire 5 idées du cours. Vérifier, compléter en couleur.</p></div>
<div class="learn-timeline-item"><span class="learn-date">J1</span><p>QCM court ou 5 flashcards. Dire la réponse à voix haute <em>avant</em> de retourner la carte.</p></div>
<div class="learn-timeline-item"><span class="learn-date">J3</span><p>Refaire un exercice type sans la correction. Comparer, noter l\'erreur éventuelle.</p></div>
<div class="learn-timeline-item"><span class="learn-date">J7</span><p>Exercice mélangé avec les chapitres précédents, ou expliquer le chapitre à quelqu\'un.</p></div>
</div>
</article>
</article>
<article class="learn2-card">
  <h3>👍 À faire</h3>
  <article class="learn-do">
<ul class="learn2-list"><li>Se poser des questions et y répondre de mémoire.</li><li>Refaire un exercice sans la correction sous les yeux.</li><li>Revoir ses erreurs quelques jours plus tard.</li><li>Utiliser les QCM et flashcards du site en cycle espacé.</li><li>Réviser un peu, souvent, sur plusieurs jours.</li></ul>
</article>
</article>
<article class="learn2-card">
  <h3>🚫 À éviter</h3>
  <article class="learn-dont">
<ul class="learn2-list"><li>Relire trois fois le cours sans jamais se tester.</li><li>Surligner presque tout (donc ne rien hiérarchiser).</li><li>Tout réviser la veille du contrôle.</li><li>Confondre « j\'ai compris la correction » et « je sais refaire seul ».</li><li>Recopier le cours au propre en croyant réviser.</li></ul>
</article>
</article>
  </div>
</section>

<section id="memoires" class="learn2-section" style="--learn2-accent:#7c3aed">
  <div class="learn2-section-head">
    <span class="learn2-icon">🧠</span>
    <div>
      <span class="learn2-kicker">Comprendre</span>
      <h2 class="learn2-title">Les 3 mémoires</h2>
      <p class="learn2-summary">Mémoire sensorielle, mémoire de travail et mémoire à long terme : ce que l’élève doit retenir.</p>
    </div>
  </div>
  <div class="learn2-grid three">
    
<article class="learn2-card">
  <h3>👁️ Mémoire sensorielle</h3>
  <article class="memory-box">
<span class="memory-number">1</span>
<p class="mt-2 muted">Elle capte tout ce que tu vois et entends, mais pendant moins d\'une seconde. Sans attention, l\'information disparaît avant même d\'exister pour toi. <strong>L\'attention est la porte d\'entrée de tout apprentissage.</strong></p>
<ul class="learn2-list"><li>Regarder le tableau au moment clé de l\'explication.</li><li>Écouter la consigne jusqu\'au bout avant d\'écrire.</li><li>Ne pas parler pendant l\'explication : on ne peut pas écouter deux choses.</li></ul>
</article>
</article>
<article class="learn2-card">
  <h3>⚙️ Mémoire de travail</h3>
  <article class="memory-box">
<span class="memory-number">2</span>
<p class="mt-2 muted">C\'est ton « plan de travail » mental : elle raisonne ici et maintenant. Mais elle ne tient qu\'environ <strong>4 éléments à la fois</strong> et se vide en quelques secondes. C\'est le goulot d\'étranglement de toute la pensée.</p>
<ul class="learn2-list"><li>Écrire les données au lieu de tout garder en tête.</li><li>Découper un problème en petites étapes.</li><li>Automatiser (tables, formules, syntaxe) pour libérer de la place.</li></ul>
</article>
</article>
<article class="learn2-card">
  <h3>🗄️ Mémoire à long terme</h3>
  <article class="memory-box">
<span class="memory-number">3</span>
<p class="mt-2 muted">Capacité quasi illimitée : définitions, méthodes, automatismes, exemples. Plus elle est riche, plus la mémoire de travail respire — c\'est pour ça qu\'un expert trouve « facile » ce qui te paraît dur. Elle se construit par <strong>rappels répétés, espacés et reliés</strong>.</p>
<ul class="learn2-list"><li>Faire des liens entre notions et chapitres.</li><li>Revoir plusieurs fois, à plusieurs jours d\'écart.</li><li>Expliquer avec ses propres mots.</li></ul>
</article>
</article>
<article class="learn2-card">
  <h3>💡 Conséquence pratique</h3>
  <div class="learn-warning mt-5">
<p class="mt-2 muted">Quand un exercice te semble « impossible », ce n\'est presque jamais un problème d\'intelligence : c\'est ta mémoire de travail qui sature (surcharge cognitive). La parade est toujours la même : <strong>écrire les données, découper en étapes, chercher la première action possible</strong>. Et à long terme : automatiser les bases, car ce qui est automatique ne consomme plus de mémoire de travail.</p>
</div>
</article>
  </div>
</section>

<section id="metacognition" class="learn2-section" style="--learn2-accent:#0f766e">
  <div class="learn2-section-head">
    <span class="learn2-icon">🧭</span>
    <div>
      <span class="learn2-kicker">Piloter</span>
      <h2 class="learn2-title">Métacognition et auto-évaluation</h2>
      <p class="learn2-summary">Apprendre à planifier, surveiller, expliquer et vérifier son propre travail.</p>
    </div>
  </div>
  <div class="learn2-grid three">
    
<article class="learn2-card">
  <h3>🎓 Devenir le pilote de son apprentissage</h3>
  <article class="learn-card">
<p class="mt-2 muted">La métacognition, c\'est « penser sur sa façon de penser » : savoir ce qu\'on sait, repérer ce qu\'on ne sait pas, et choisir la bonne stratégie. Les recherches montrent que c\'est l\'un des facteurs qui distinguent le plus les élèves qui progressent. Le danger n° 1 : <strong>l\'illusion de maîtrise</strong> — le cours relu paraît familier, donc on croit le savoir.</p>
<p class="learn-quote mt-3">« Familier » n\'est pas « su ». Le seul test honnête : cahier fermé, est-ce que je peux le refaire ou l\'expliquer ?</p>
</article>
</article>
<article class="learn2-card">
  <h3>❓ Les 3 questions du pilote</h3>
  <article class="learn-card">
<div class="learn-timeline mt-3">
<div class="learn-timeline-item"><span class="learn-date">Avant</span><p>« Qu\'est-ce que je dois savoir faire à la fin ? Par quoi je commence ? »</p></div>
<div class="learn-timeline-item"><span class="learn-date">Pendant</span><p>« Est-ce que je comprends vraiment, ou est-ce que je fais semblant d\'avancer ? »</p></div>
<div class="learn-timeline-item"><span class="learn-date">Après</span><p>« Qu\'est-ce qui est maîtrisé ? Qu\'est-ce que je dois revoir, et quand ? »</p></div>
</div>
</article>
</article>
<article class="learn2-card">
  <h3>🧑‍🏫 La technique de Feynman</h3>
  <article class="learn-card">
<ul class="learn2-list"><li>Choisir une notion du cours.</li><li>L\'expliquer par écrit ou à voix haute, comme à un élève de 5e.</li><li>Repérer l\'endroit exact où l\'explication bloque : c\'est <strong>là</strong> qu\'il faut retravailler.</li><li>Simplifier, trouver un exemple, recommencer.</li></ul>
</article>
</article>
<article class="learn2-card">
  <h3>⚖️ Auto-évaluation honnête</h3>
  <article class="learn-card">
<p class="mt-2 muted">Après chaque séance, classe chaque notion :</p>
<div class="learn-check mt-3"><label class=""><input type="checkbox"/> 🟢 Je sais le refaire seul et l\'expliquer.</label><label class=""><input type="checkbox"/> 🟠 Je sais avec le cours sous les yeux.</label><label class=""><input type="checkbox"/> 🔴 Je ne sais pas par où commencer.</label></div>
<p class="mt-3 muted">La prochaine séance commence toujours par les 🔴 et 🟠 — pas par les 🟢, même si c\'est plus agréable.</p>
</article>
</article>
  </div>
</section>

<section id="classe" class="learn2-section" style="--learn2-accent:#0891b2">
  <div class="learn2-section-head">
    <span class="learn2-icon">🧑‍🏫</span>
    <div>
      <span class="learn2-kicker">Classe</span>
      <h2 class="learn2-title">Concentration en classe</h2>
      <p class="learn2-summary">Transformer le temps de classe en temps d’apprentissage actif.</p>
    </div>
  </div>
  <div class="learn2-grid three">
    
<article class="learn2-card">
  <h3>🖊️ En classe : être actif</h3>
  <article class="concentration-card"><p class="mt-2 muted">Une heure de cours suivie activement vaut deux heures de rattrapage à la maison. Être actif, ce n\'est pas tout recopier : c\'est <strong>traiter</strong> l\'information.</p><ul class="learn2-list"><li>Noter les mots-clés, les exemples et les « pièges » signalés.</li><li>Anticiper : « quelle va être la prochaine étape ? »</li><li>Reformuler mentalement ce que dit le professeur.</li><li>Demander une reformulation si la consigne n\'est pas claire — c\'est un signe d\'intelligence, pas de faiblesse.</li></ul></article>
</article>
<article class="learn2-card">
  <h3>🛟 Quand tu décroches</h3>
  <article class="concentration-card"><p class="mt-2 muted">Tout le monde décroche : l\'attention fonctionne par vagues. Ce qui compte, c\'est la vitesse à laquelle tu remontes à bord.</p><ul class="learn2-list"><li>Revenir à la question : « que cherche-t-on à trouver ? »</li><li>Copier la prochaine étape, pas tout le tableau en retard.</li><li>Respirer calmement 10 secondes, se redresser.</li><li>Reprendre avec une action simple : encadrer, souligner, calculer, définir.</li></ul></article>
</article>
<article class="learn2-card">
  <h3>❝ Phrase utile</h3>
  <article class="learn-card"><p class="learn-quote mt-3">« Je n\'ai pas besoin de tout comprendre d\'un coup ; je dois trouver la prochaine étape. »</p><p class="mt-3 muted">Le cerveau ne peut pas faire deux tâches réfléchies en même temps : ce qu\'on appelle « multitâche » est en réalité un zapping coûteux qui dégrade les deux tâches.</p></article>
</article>
<article class="learn2-card">
  <h3>📝 Mini-contrat classe</h3>
  <article class="learn-card"><div class="learn-check mt-3"><label class=""><input type="checkbox"/> J\'écoute la consigne jusqu\'au bout.</label><label class=""><input type="checkbox"/> Je commence par une petite action au lieu d\'attendre.</label><label class=""><input type="checkbox"/> Je pose une question précise quand je bloque.</label><label class=""><input type="checkbox"/> Je note mes questions au lieu de les laisser filer.</label></div></article>
</article>
  </div>
</section>

<section id="maison" class="learn2-section" style="--learn2-accent:#16a34a">
  <div class="learn2-section-head">
    <span class="learn2-icon">🏠</span>
    <div>
      <span class="learn2-kicker">Maison</span>
      <h2 class="learn2-title">Travail à la maison</h2>
      <p class="learn2-summary">Une routine courte, active et réaliste pour travailler sans se disperser.</p>
    </div>
  </div>
  <div class="learn2-grid three">
    
<article class="learn2-card">
  <h3>🏠 Travailler à la maison</h3>
  <article class="learn-card"><p class="mt-2 muted">La qualité bat la quantité : 25 minutes de vraie concentration valent mieux que 2 heures de « présence » devant le cahier.</p><ul class="learn2-list"><li>Commencer sans téléphone visible (voir l\'onglet Téléphone).</li><li>Ouvrir la séance par un rappel de 5 minutes, cahier fermé.</li><li>Se fixer une tâche précise (« refaire l\'ex. 12 seul »), jamais « réviser un peu ».</li><li>Corriger en écrivant <em>pourquoi</em> c\'était faux.</li><li>Finir par 2 lignes : maîtrisé / à revoir / prochaine session.</li></ul></article>
</article>
<article class="learn2-card">
  <h3>⏱️ Routine 25 minutes (Pomodoro)</h3>
  <article class="learn-routine"><div class="learn-timeline mt-3"><div class="learn-timeline-item"><span class="learn-date">5 min</span><p>Rappel sans cahier : écrire ce dont tu te souviens.</p></div><div class="learn-timeline-item"><span class="learn-date">15 min</span><p>Exercice, QCM, flashcards ou programme — en te testant.</p></div><div class="learn-timeline-item"><span class="learn-date">5 min</span><p>Correction, carnet d\'erreurs, mini-bilan.</p></div></div><p class="mt-3 muted">Puis 5 minutes de vraie pause (bouger, boire — pas les réseaux, qui relancent le compteur d\'attention à zéro). Deux à trois cycles suffisent pour une soirée efficace.</p></article>
</article>
<article class="learn2-card">
  <h3>🌱 Bon signe</h3>
  <article class="learn-do"><p class="mt-2 muted">Tu hésites, tu cherches, tu fais des erreurs, tu corriges : c\'est exactement l\'effort qui renforce la mémoire (la « difficulté désirable »). Une séance où tout est facile est une séance qui n\'apprend presque rien.</p></article>
</article>
<article class="learn2-card">
  <h3>❌ Mauvais piège</h3>
  <article class="learn-dont"><p class="mt-2 muted">Faire semblant de travailler : cours ouvert, correction ouverte, musique avec paroles, téléphone à côté. On y passe du temps, on a bonne conscience, et rien ne reste. Mieux vaut 25 minutes exigeantes que 2 heures décoratives.</p></article>
</article>
  </div>
</section>

<section id="telephone" class="learn2-section" style="--learn2-accent:#ef4444">
  <div class="learn2-section-head">
    <span class="learn2-icon">📱</span>
    <div>
      <span class="learn2-kicker">Attention</span>
      <h2 class="learn2-title">Téléphone portable</h2>
      <p class="learn2-summary">Comprendre le coût attentionnel du téléphone et poser des règles simples.</p>
    </div>
  </div>
  <div class="learn2-grid two">
    
<article class="learn2-card">
  <h3>📱 Le coût invisible du téléphone</h3>
  <article class="learn-card"><p class="phone-warning mt-3">Le téléphone n\'est pas une simple pause : c\'est une machine conçue par des milliers d\'ingénieurs pour capturer ton attention. Des études (Ward et coll., 2017) montrent que sa <strong>seule présence visible sur la table</strong>, même éteint, réduit les performances de raisonnement : une partie du cerveau reste occupée à résister à l\'envie de le regarder.</p><ul class="learn2-list"><li>Après chaque interruption, il faut plusieurs minutes pour retrouver une concentration profonde.</li><li>Mode avion ou « ne pas déranger » <em>avant</em> de commencer.</li><li>Téléphone dans une autre pièce, ou au moins hors de vue.</li><li>Pause téléphone uniquement après une tâche <strong>terminée</strong>, avec une durée fixée à l\'avance.</li></ul></article>
</article>
<article class="learn2-card">
  <h3>📊 Règle simple</h3>
  <article class="learn-card"><div class="learn2-table-wrap"><table class="learn2-table"><tr><th>Situation</th><th>Décision</th></tr><tr><td>Je commence une séance</td><td>Téléphone hors de portée <em>et</em> hors de vue.</td></tr><tr><td>Je suis bloqué sur un exercice</td><td>Je relis la consigne et mes notes — pas les messages.</td></tr><tr><td>J\'ai besoin du site (QCM, flashcards)</td><td>Notifications coupées, une seule appli ouverte.</td></tr><tr><td>J\'ai fini une tâche</td><td>Pause courte autorisée, minuteur réglé.</td></tr></table></div><p class="learn-quote mt-3">« Ce n\'est pas une question de volonté, c\'est une question d\'organisation : le téléphone qu\'on ne voit pas ne demande aucun effort de résistance. »</p></article>
</article>
  </div>
</section>

<section id="sommeil" class="learn2-section" style="--learn2-accent:#9333ea">
  <div class="learn2-section-head">
    <span class="learn2-icon">😴</span>
    <div>
      <span class="learn2-kicker">Santé</span>
      <h2 class="learn2-title">Sommeil et corps</h2>
      <p class="learn2-summary">Le sommeil, le corps et l’hygiène de vie font partie de la méthode.</p>
    </div>
  </div>
  <div class="learn2-grid two">
    
<article class="learn2-card">
  <h3>😴 Dormir, c&#x27;est réviser</h3>
  <article class="learn-card"><p class="mt-2 muted">Pendant le sommeil, le cerveau <strong>rejoue et consolide</strong> ce qui a été appris dans la journée : les souvenirs fragiles deviennent durables. Un adolescent a besoin d\'environ 8 à 10 heures de sommeil. Sacrifier sa nuit pour réviser la veille d\'un contrôle, c\'est effacer une partie de ce qu\'on vient d\'apprendre — et se présenter avec une mémoire de travail dégradée.</p><ul class="learn2-list"><li>La veille d\'un contrôle : révision courte, puis nuit complète.</li><li>Écrans coupés 30 à 60 min avant de dormir (la lumière retarde l\'endormissement).</li><li>Heures de coucher régulières, même le week-end si possible.</li></ul></article>
</article>
<article class="learn2-card">
  <h3>🏃 Le corps fait partie de la méthode</h3>
  <article class="learn-card"><ul class="learn2-list"><li><strong>Bouger</strong> : l\'activité physique améliore l\'attention et la mémoire — une vraie pause active vaut mieux qu\'une pause écran.</li><li><strong>Manger et boire</strong> : un cerveau déshydraté ou à jeun raisonne moins bien, surtout le matin d\'un contrôle.</li><li><strong>Respirer</strong> : avant une évaluation, 5 respirations lentes calment le stress ; un stress modéré est normal et même utile, il mobilise l\'attention.</li><li><strong>Se parler correctement</strong> : remplacer « je suis nul » par « je ne sais pas <em>encore</em> ». Les compétences se construisent, elles ne sont pas données à la naissance.</li></ul></article>
</article>
  </div>
</section>

<section id="maths" class="learn2-section" style="--learn2-accent:#eab308">
  <div class="learn2-section-head">
    <span class="learn2-icon">√</span>
    <div>
      <span class="learn2-kicker">Matière</span>
      <h2 class="learn2-title">Réviser les mathématiques</h2>
      <p class="learn2-summary">En maths, la copie doit montrer le raisonnement et les propriétés utilisées.</p>
    </div>
  </div>
  <div class="learn2-grid three">
    
<article class="learn2-card">
  <h3>√ Comment réviser les maths</h3>
  <article class="learn-card"><p class="mt-2 muted">On n\'apprend pas les maths en regardant quelqu\'un en faire — pas plus qu\'on n\'apprend à nager en regardant des vidéos de natation. La règle : <strong>un stylo, une feuille, et refaire soi-même.</strong></p><ul class="learn2-list"><li>Refaire les exercices types sans la correction, puis comparer.</li><li>Écrire toutes les étapes, pas seulement le résultat.</li><li>Apprendre chaque propriété <em>avec</em> un exemple et un contre-exemple.</li><li>Mélanger les exercices de plusieurs chapitres (interleaving).</li><li>Tenir un carnet d\'erreurs : signe, parenthèse, fraction, domaine…</li></ul></article>
</article>
<article class="learn2-card">
  <h3>📄 La copie doit montrer le raisonnement</h3>
  <article class="learn-card"><ul class="learn2-list"><li>Je cite la propriété ou la définition utilisée.</li><li>J\'enchaîne les lignes proprement, avec des connecteurs (« donc », « or », « d\'après… »).</li><li>Je vérifie la cohérence du résultat (ordre de grandeur, signe, ensemble de définition).</li><li>Je conclus par une phrase ou un ensemble solution.</li></ul></article>
</article>
<article class="learn2-card">
  <h3>⏳ Séance 20 minutes</h3>
  <article class="learn-card"><div class="learn-timeline mt-3"><div class="learn-timeline-item"><span class="learn-date">5</span><p>Réciter une propriété de mémoire, avec un exemple.</p></div><div class="learn-timeline-item"><span class="learn-date">10</span><p>Refaire deux exercices sans correction, dont un d\'un chapitre précédent.</p></div><div class="learn-timeline-item"><span class="learn-date">5</span><p>Corriger, expliquer l\'erreur, la noter au carnet.</p></div></div></article>
</article>
<article class="learn2-card">
  <h3>✅ Checklist maths</h3>
  <article class="learn-card"><div class="learn-check mt-3"><label class=""><input type="checkbox"/> Je connais les définitions et propriétés de mémoire.</label><label class=""><input type="checkbox"/> Je sais refaire les exemples du cours seul.</label><label class=""><input type="checkbox"/> Je sais justifier chaque étape avec une propriété.</label><label class=""><input type="checkbox"/> Je reconnais quel type d\'exercice appelle quelle méthode.</label></div></article>
</article>
  </div>
</section>

<section id="pc" class="learn2-section" style="--learn2-accent:#f97316">
  <div class="learn2-section-head">
    <span class="learn2-icon">⚗️</span>
    <div>
      <span class="learn2-kicker">Matière</span>
      <h2 class="learn2-title">Réviser la physique-chimie</h2>
      <p class="learn2-summary">Relier définition, formule, unité, expérience, schéma et conclusion.</p>
    </div>
  </div>
  <div class="learn2-grid three">
    
<article class="learn2-card">
  <h3>⚗️ Comment réviser la physique-chimie</h3>
  <article class="learn-card"><ul class="learn2-list"><li>Apprendre chaque définition et chaque formule <em>avec</em> ses unités et un exemple.</li><li>S\'entraîner aux conversions jusqu\'à l\'automatisme.</li><li>Relire les TP en structure : but → protocole → observations → conclusion.</li><li>Refaire les schémas de mémoire : forces, circuits, rayons, montages.</li><li>Se demander « quel phénomène ? quelle loi ? » avant de calculer.</li></ul></article>
</article>
<article class="learn2-card">
  <h3>📋 Une réponse scientifique complète</h3>
  <article class="learn-card"><p class="mt-2 muted">La démarche vaut autant de points que le résultat :</p><ul class="learn2-list"><li>Données utiles, converties dans les bonnes unités.</li><li>Formule ou loi, citée avant application.</li><li>Calcul littéral d\'abord, valeurs ensuite, unités partout.</li><li>Regard critique : le résultat est-il plausible ? (ordre de grandeur)</li><li>Conclusion en une phrase qui répond à la question posée.</li></ul></article>
</article>
<article class="learn2-card">
  <h3>👁️ Erreur fréquente</h3>
  <article class="learn-card"><p class="mt-2 muted">Comprendre la correction ne suffit jamais : c\'est l\'illusion de maîtrise classique. Le seul test valable : cacher la correction, reprendre une feuille blanche, et refaire jusqu\'au bout — unités et conclusion comprises.</p></article>
</article>
<article class="learn2-card">
  <h3>✅ Checklist physique-chimie</h3>
  <article class="learn-card"><div class="learn-check mt-3"><label class=""><input type="checkbox"/> Je connais les formules avec leurs unités.</label><label class=""><input type="checkbox"/> Je sais choisir la loi adaptée à la situation.</label><label class=""><input type="checkbox"/> Je sais lire et exploiter un graphique.</label><label class=""><input type="checkbox"/> Je sais rédiger une conclusion qui répond à la question.</label></div></article>
</article>
  </div>
</section>

<section id="nsi" class="learn2-section" style="--learn2-accent:#4c1d95">
  <div class="learn2-section-head">
    <span class="learn2-icon">💻</span>
    <div>
      <span class="learn2-kicker">Matière</span>
      <h2 class="learn2-title">Réviser la NSI</h2>
      <p class="learn2-summary">Lire, prédire, écrire, tester et corriger du code.</p>
    </div>
  </div>
  <div class="learn2-grid three">
    
<article class="learn2-card">
  <h3>💻 Comment réviser la NSI</h3>
  <article class="learn-card"><p class="mt-2 muted">Programmer s\'apprend en programmant. Copier-coller un code qui marche n\'apprend rien ; l\'écrire soi-même, se tromper et corriger apprend tout.</p><ul class="learn2-list"><li>Lire du code et <strong>prédire</strong> ce qu\'il affiche avant de l\'exécuter.</li><li>Écrire des programmes courts de mémoire, sans copier-coller.</li><li>Tester avec plusieurs valeurs, <em>dont les cas limites</em> (0, liste vide, négatif).</li><li>Expliquer chaque ligne avec ses mots (technique de Feynman appliquée au code).</li><li>Refaire un programme du cours quelques jours plus tard, de zéro.</li></ul></article>
</article>
<article class="learn2-card">
  <h3>🛠️ Déboguer intelligemment</h3>
  <article class="learn-card"><p class="mt-2 muted">Un bug n\'est pas un échec : c\'est une énigme avec des indices. Méthode :</p><ul class="learn2-list"><li>Lire le message d\'erreur en entier — il dit souvent la ligne et la cause.</li><li>Formuler une hypothèse : « je pense que la variable vaut… »</li><li>Vérifier avec <code>print()</code> aux endroits stratégiques.</li><li>Tester sur un cas minuscule fait à la main.</li><li>Noter le bug résolu : les mêmes erreurs reviennent souvent.</li></ul></article>
</article>
<article class="learn2-card">
  <h3>💻 Séance 30 minutes</h3>
  <article class="learn-card"><div class="learn-timeline mt-3"><div class="learn-timeline-item"><span class="learn-date">5</span><p>Revoir une notion de syntaxe, de mémoire.</p></div><div class="learn-timeline-item"><span class="learn-date">10</span><p>Lire un code et prédire sa sortie, puis vérifier.</p></div><div class="learn-timeline-item"><span class="learn-date">10</span><p>Écrire une fonction de zéro, avec ses tests.</p></div><div class="learn-timeline-item"><span class="learn-date">5</span><p>Exécuter, corriger, noter ce qui a bloqué.</p></div></div></article>
</article>
<article class="learn2-card">
  <h3>✅ Checklist NSI</h3>
  <article class="learn-card"><div class="learn-check mt-3"><label class=""><input type="checkbox"/> Je sais expliquer chaque ligne d\'un programme du cours.</label><label class=""><input type="checkbox"/> Je sais écrire une fonction avec paramètres et return.</label><label class=""><input type="checkbox"/> Je sais prédire une sortie avant d\'exécuter.</label><label class=""><input type="checkbox"/> Je pense aux cas limites dans mes tests.</label></div></article>
</article>
  </div>
</section>

<section id="semaine" class="learn2-section" style="--learn2-accent:#0f172a">
  <div class="learn2-section-head">
    <span class="learn2-icon">🗓️</span>
    <div>
      <span class="learn2-kicker">Routine</span>
      <h2 class="learn2-title">Plan de semaine</h2>
      <p class="learn2-summary">Organiser les rappels et les révisions avant contrôle.</p>
    </div>
  </div>
  <div class="learn2-grid two">
    
<article class="learn2-card">
  <h3>📅 Plan de semaine (15–25 min/jour)</h3>
  <article class="learn-card"><p class="mt-2 muted">La régularité bat l\'intensité : mieux vaut 20 minutes chaque jour que 3 heures le dimanche.</p><div class="learn-timeline mt-3"><div class="learn-timeline-item"><span class="learn-date">Lun</span><p>Rappel sans cahier : 5 idées de chaque cours du jour.</p></div><div class="learn-timeline-item"><span class="learn-date">Mar</span><p>10–15 minutes d\'exercices, sans correction sous les yeux.</p></div><div class="learn-timeline-item"><span class="learn-date">Mer</span><p>QCM ou flashcards du site, en mode espacé (revenir sur la semaine passée).</p></div><div class="learn-timeline-item"><span class="learn-date">Jeu</span><p>Reprendre une erreur de la semaine et la refaire de zéro.</p></div><div class="learn-timeline-item"><span class="learn-date">Ven</span><p>Mini-bilan 🟢🟠🔴 : maîtrisé / fragile / à revoir. Planifier le week-end.</p></div><div class="learn-timeline-item"><span class="learn-date">W-E</span><p>Une séance de 25 min sur les 🔴, un exercice mélangé, et du repos réel.</p></div></div></article>
</article>
<article class="learn2-card">
  <h3>🏁 Plan avant contrôle</h3>
  <article class="learn-card"><ul class="learn2-list"><li><strong>J−4 / J−3</strong> : refaire les exercices types de mémoire, lister les points fragiles.</li><li><strong>J−2</strong> : travailler uniquement les points fragiles + un sujet complet en temps limité.</li><li><strong>La veille</strong> : QCM et flashcards difficiles, relire le carnet d\'erreurs, préparer ses affaires — puis <strong>nuit complète</strong>.</li><li><strong>Le jour même</strong> : relire la mini-fiche (10 min), manger, boire, respirer. On n\'apprend pas un nouveau chapitre le matin du contrôle.</li></ul><p class="learn-quote mt-3">« Si tu as suivi J0–J1–J3–J7 toute la séquence, la semaine du contrôle n\'est qu\'une confirmation — pas un sauvetage. »</p></article>
</article>
  </div>
</section>
    </div>
  </div>',
        'sort_order' => 1,
        'is_published' => 1,
    ],
    [
        'id' => 2,
        'slug' => 'lire-un-graphique',
        'title' => 'Lire un graphique',
        'summary' => 'Repérer les axes, les unités, les valeurs et interpréter une évolution.',
        'domain' => 'Physique-Chimie',
        'levels' => 'Collège / Lycée',
        'accent' => '#2563eb',
        'icon' => '📈',
        'content' => '<a href="#" class="nav-link">← Toutes les méthodes</a>

  <section class="method-page-hero mt-5 p-6 sm:p-9">
    <span class="badge">Physique-Chimie</span>
    <h1 class="page-title mt-4">Lire un graphique</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Repérer les axes, les unités, les valeurs et interpréter une évolution.</p>
  </section>

<section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">1</span> Étapes à suivre</h2>
    <div class="method-steps mt-5"><div class="method-step">Lire le titre et comprendre ce qui est représenté.</div><div class="method-step">Identifier les axes, les unités et l’échelle.</div><div class="method-step">Lire une valeur avec précision.</div><div class="method-step">Décrire l’évolution : augmente, diminue, reste constant.</div><div class="method-step">Conclure avec une phrase scientifique.</div></div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">2</span> Exemple et formule</h2>
    <div class="method-formula mt-5">Exemple : pente $a = \\dfrac{\\Delta y}{\\Delta x}$ si la courbe est une droite.</div>
    <div class="method-image-placeholder mt-5">Emplacement image / schéma / capture pour cette méthode</div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">3</span> Mini-exercice corrigé</h2>
    <div class="method-example mt-5">
      <p class="font-black text-slate-950 dark:text-white">Exercice</p>
      <p class="mt-2 muted">Sur un graphique distance-temps, la distance passe de 0 m à 12 m en 3 s. Calculer la vitesse moyenne.</p>
      <details class="method-correction">
        <summary>Voir la correction</summary>
        <div class="method-correction-body">On lit $d=12\\,m$ et $t=3\\,s$. Donc $v=d/t=12/3=4\\,m\\cdot s^{-1}$.</div>
      </details>
    </div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">4</span> À retenir</h2>
    <div class="method-example mt-5">
      <p class="font-black">Méthode courte</p>
      <p class="mt-2 muted">Je lis la consigne, je repère les données, je choisis la méthode, je rédige clairement et je vérifie le résultat.</p>
    </div>
  </section>',
        'sort_order' => 2,
        'is_published' => 1,
    ],
    [
        'id' => 3,
        'slug' => 'convertir-des-unites',
        'title' => 'Convertir des unités',
        'summary' => 'Passer correctement d’une unité à une autre avant un calcul.',
        'domain' => 'Physique-Chimie',
        'levels' => 'Collège / Lycée',
        'accent' => '#16a34a',
        'icon' => '↔',
        'content' => '<a href="#" class="nav-link">← Toutes les méthodes</a>

  <section class="method-page-hero mt-5 p-6 sm:p-9">
    <span class="badge">Physique-Chimie</span>
    <h1 class="page-title mt-4">Convertir des unités</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Passer correctement d’une unité à une autre avant un calcul.</p>
  </section>

<section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">1</span> Étapes à suivre</h2>
    <div class="method-steps mt-5"><div class="method-step">Identifier la grandeur : longueur, masse, durée, volume.</div><div class="method-step">Écrire l’unité de départ et l’unité d’arrivée.</div><div class="method-step">Utiliser un tableau ou une puissance de 10.</div><div class="method-step">Vérifier que la valeur convertie est cohérente.</div><div class="method-step">Toujours écrire l’unité finale.</div></div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">2</span> Exemple et formule</h2>
    <div class="method-formula mt-5">Exemples : $1\\,km=1000\\,m$, $1\\,h=3600\\,s$, $1\\,L=10^{-3}\\,m^3$.</div>
    <div class="method-image-placeholder mt-5">Emplacement image / schéma / capture pour cette méthode</div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">3</span> Mini-exercice corrigé</h2>
    <div class="method-example mt-5">
      <p class="font-black text-slate-950 dark:text-white">Exercice</p>
      <p class="mt-2 muted">Convertir 72 km/h en m/s.</p>
      <details class="method-correction">
        <summary>Voir la correction</summary>
        <div class="method-correction-body">$72\\,km/h=72\\times\\dfrac{1000}{3600}=20\\,m/s$.</div>
      </details>
    </div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">4</span> À retenir</h2>
    <div class="method-example mt-5">
      <p class="font-black">Méthode courte</p>
      <p class="mt-2 muted">Je lis la consigne, je repère les données, je choisis la méthode, je rédige clairement et je vérifie le résultat.</p>
    </div>
  </section>',
        'sort_order' => 3,
        'is_published' => 1,
    ],
    [
        'id' => 4,
        'slug' => 'utiliser-une-formule',
        'title' => 'Utiliser une formule',
        'summary' => 'Choisir une relation, remplacer les valeurs, calculer et conclure.',
        'domain' => 'Physique-Chimie',
        'levels' => 'Collège / Lycée',
        'accent' => '#f97316',
        'icon' => 'ƒ',
        'content' => '<a href="#" class="nav-link">← Toutes les méthodes</a>

  <section class="method-page-hero mt-5 p-6 sm:p-9">
    <span class="badge">Physique-Chimie</span>
    <h1 class="page-title mt-4">Utiliser une formule</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Choisir une relation, remplacer les valeurs, calculer et conclure.</p>
  </section>

<section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">1</span> Étapes à suivre</h2>
    <div class="method-steps mt-5"><div class="method-step">Écrire la formule littérale.</div><div class="method-step">Identifier chaque grandeur et son unité.</div><div class="method-step">Convertir si nécessaire.</div><div class="method-step">Remplacer les valeurs dans la formule.</div><div class="method-step">Donner le résultat avec l’unité et une phrase.</div></div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">2</span> Exemple et formule</h2>
    <div class="method-formula mt-5">Exemples : $v=\\dfrac{d}{t}$, $E_c=\\dfrac12mv^2$, $C_m=\\dfrac{m}{V}$.</div>
    <div class="method-image-placeholder mt-5">Emplacement image / schéma / capture pour cette méthode</div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">3</span> Mini-exercice corrigé</h2>
    <div class="method-example mt-5">
      <p class="font-black text-slate-950 dark:text-white">Exercice</p>
      <p class="mt-2 muted">Calculer l’énergie cinétique d’un objet de masse 2 kg qui se déplace à 3 m/s.</p>
      <details class="method-correction">
        <summary>Voir la correction</summary>
        <div class="method-correction-body">$E_c=\\frac12mv^2=\\frac12\\times2\\times3^2=9\\,J$.</div>
      </details>
    </div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">4</span> À retenir</h2>
    <div class="method-example mt-5">
      <p class="font-black">Méthode courte</p>
      <p class="mt-2 muted">Je lis la consigne, je repère les données, je choisis la méthode, je rédige clairement et je vérifie le résultat.</p>
    </div>
  </section>',
        'sort_order' => 4,
        'is_published' => 1,
    ],
    [
        'id' => 5,
        'slug' => 'rediger-une-reponse-scientifique',
        'title' => 'Rédiger une réponse scientifique',
        'summary' => 'Faire une réponse claire, justifiée et complète.',
        'domain' => 'Physique-Chimie',
        'levels' => 'Collège / Lycée',
        'accent' => '#dc2626',
        'icon' => '✍',
        'content' => '<a href="#" class="nav-link">← Toutes les méthodes</a>

  <section class="method-page-hero mt-5 p-6 sm:p-9">
    <span class="badge">Physique-Chimie</span>
    <h1 class="page-title mt-4">Rédiger une réponse scientifique</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Faire une réponse claire, justifiée et complète.</p>
  </section>

<section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">1</span> Étapes à suivre</h2>
    <div class="method-steps mt-5"><div class="method-step">Commencer par la donnée ou l’observation utile.</div><div class="method-step">Utiliser le vocabulaire du chapitre.</div><div class="method-step">Écrire le calcul ou le raisonnement.</div><div class="method-step">Conclure en répondant à la question.</div><div class="method-step">Vérifier unité, signe et phrase finale.</div></div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">2</span> Exemple et formule</h2>
    <div class="method-formula mt-5">Phrase modèle : « J’observe que..., donc je peux conclure que..., car... »</div>
    <div class="method-image-placeholder mt-5">Emplacement image / schéma / capture pour cette méthode</div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">3</span> Mini-exercice corrigé</h2>
    <div class="method-example mt-5">
      <p class="font-black text-slate-950 dark:text-white">Exercice</p>
      <p class="mt-2 muted">Rédiger une conclusion après un calcul de vitesse donnant 4 m/s.</p>
      <details class="method-correction">
        <summary>Voir la correction</summary>
        <div class="method-correction-body">La vitesse moyenne de l’objet est de $4\\,m/s$. Cela signifie qu’il parcourt en moyenne 4 mètres en une seconde.</div>
      </details>
    </div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">4</span> À retenir</h2>
    <div class="method-example mt-5">
      <p class="font-black">Méthode courte</p>
      <p class="mt-2 muted">Je lis la consigne, je repère les données, je choisis la méthode, je rédige clairement et je vérifie le résultat.</p>
    </div>
  </section>',
        'sort_order' => 5,
        'is_published' => 1,
    ],
    [
        'id' => 6,
        'slug' => 'exploiter-une-experience',
        'title' => 'Exploiter une expérience',
        'summary' => 'Passer d’une manipulation à une conclusion scientifique.',
        'domain' => 'Physique-Chimie',
        'levels' => 'Collège / Lycée',
        'accent' => '#0891b2',
        'icon' => '⚗',
        'content' => '<a href="#" class="nav-link">← Toutes les méthodes</a>

  <section class="method-page-hero mt-5 p-6 sm:p-9">
    <span class="badge">Physique-Chimie</span>
    <h1 class="page-title mt-4">Exploiter une expérience</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Passer d’une manipulation à une conclusion scientifique.</p>
  </section>

<section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">1</span> Étapes à suivre</h2>
    <div class="method-steps mt-5"><div class="method-step">Lire le protocole et identifier le but.</div><div class="method-step">Repérer les grandeurs mesurées.</div><div class="method-step">Présenter les résultats dans un tableau si nécessaire.</div><div class="method-step">Comparer les résultats avec le modèle attendu.</div><div class="method-step">Conclure sans inventer de résultat.</div></div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">2</span> Exemple et formule</h2>
    <div class="method-formula mt-5">Méthode : Observation → Mesure → Exploitation → Conclusion.</div>
    <div class="method-image-placeholder mt-5">Emplacement image / schéma / capture pour cette méthode</div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">3</span> Mini-exercice corrigé</h2>
    <div class="method-example mt-5">
      <p class="font-black text-slate-950 dark:text-white">Exercice</p>
      <p class="mt-2 muted">On chauffe de l’eau et la température reste proche de 100 °C pendant l’ébullition. Que conclure ?</p>
      <details class="method-correction">
        <summary>Voir la correction</summary>
        <div class="method-correction-body">Lors de l’ébullition, la température de l’eau pure reste constante autour de 100 °C sous pression atmosphérique normale.</div>
      </details>
    </div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">4</span> À retenir</h2>
    <div class="method-example mt-5">
      <p class="font-black">Méthode courte</p>
      <p class="mt-2 muted">Je lis la consigne, je repère les données, je choisis la méthode, je rédige clairement et je vérifie le résultat.</p>
    </div>
  </section>',
        'sort_order' => 6,
        'is_published' => 1,
    ],
    [
        'id' => 7,
        'slug' => 'ecrire-une-equation-chimique',
        'title' => 'Écrire une équation chimique',
        'summary' => 'Identifier réactifs et produits puis équilibrer l’équation.',
        'domain' => 'Physique-Chimie',
        'levels' => '3e / Lycée',
        'accent' => '#65a30d',
        'icon' => '🧪',
        'content' => '<a href="#" class="nav-link">← Toutes les méthodes</a>

  <section class="method-page-hero mt-5 p-6 sm:p-9">
    <span class="badge">Physique-Chimie</span>
    <h1 class="page-title mt-4">Écrire une équation chimique</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Identifier réactifs et produits puis équilibrer l’équation.</p>
  </section>

<section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">1</span> Étapes à suivre</h2>
    <div class="method-steps mt-5"><div class="method-step">Identifier les réactifs et les produits.</div><div class="method-step">Écrire les formules chimiques correctes.</div><div class="method-step">Compter les atomes de chaque élément.</div><div class="method-step">Ajouter des coefficients devant les formules.</div><div class="method-step">Vérifier la conservation des atomes.</div></div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">2</span> Exemple et formule</h2>
    <div class="method-formula mt-5">Exemple : $2H_2 + O_2 \\rightarrow 2H_2O$.</div>
    <div class="method-image-placeholder mt-5">Emplacement image / schéma / capture pour cette méthode</div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">3</span> Mini-exercice corrigé</h2>
    <div class="method-example mt-5">
      <p class="font-black text-slate-950 dark:text-white">Exercice</p>
      <p class="mt-2 muted">Équilibrer : $H_2 + O_2 \\rightarrow H_2O$.</p>
      <details class="method-correction">
        <summary>Voir la correction</summary>
        <div class="method-correction-body">On écrit $2H_2 + O_2 \\rightarrow 2H_2O$ pour avoir 4 H et 2 O de chaque côté.</div>
      </details>
    </div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">4</span> À retenir</h2>
    <div class="method-example mt-5">
      <p class="font-black">Méthode courte</p>
      <p class="mt-2 muted">Je lis la consigne, je repère les données, je choisis la méthode, je rédige clairement et je vérifie le résultat.</p>
    </div>
  </section>',
        'sort_order' => 7,
        'is_published' => 1,
    ],
    [
        'id' => 8,
        'slug' => 'faire-un-schema-propre',
        'title' => 'Faire un schéma propre',
        'summary' => 'Représenter une situation de façon claire et exploitable.',
        'domain' => 'Physique-Chimie',
        'levels' => 'Collège / Lycée',
        'accent' => '#7c3aed',
        'icon' => '△',
        'content' => '<a href="#" class="nav-link">← Toutes les méthodes</a>

  <section class="method-page-hero mt-5 p-6 sm:p-9">
    <span class="badge">Physique-Chimie</span>
    <h1 class="page-title mt-4">Faire un schéma propre</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Représenter une situation de façon claire et exploitable.</p>
  </section>

<section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">1</span> Étapes à suivre</h2>
    <div class="method-steps mt-5"><div class="method-step">Choisir les éléments importants à représenter.</div><div class="method-step">Tracer proprement à la règle si nécessaire.</div><div class="method-step">Nommer les grandeurs et les points utiles.</div><div class="method-step">Ajouter les unités ou les sens des vecteurs.</div><div class="method-step">Ne pas surcharger le dessin.</div></div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">2</span> Exemple et formule</h2>
    <div class="method-formula mt-5">Un schéma doit aider à raisonner, pas décorer la copie.</div>
    <div class="method-image-placeholder mt-5">Emplacement image / schéma / capture pour cette méthode</div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">3</span> Mini-exercice corrigé</h2>
    <div class="method-example mt-5">
      <p class="font-black text-slate-950 dark:text-white">Exercice</p>
      <p class="mt-2 muted">Faire le schéma d’un objet soumis à son poids et à la réaction du support.</p>
      <details class="method-correction">
        <summary>Voir la correction</summary>
        <div class="method-correction-body">On représente l’objet, puis deux forces : le poids vers le bas et la réaction du support vers le haut.</div>
      </details>
    </div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">4</span> À retenir</h2>
    <div class="method-example mt-5">
      <p class="font-black">Méthode courte</p>
      <p class="mt-2 muted">Je lis la consigne, je repère les données, je choisis la méthode, je rédige clairement et je vérifie le résultat.</p>
    </div>
  </section>',
        'sort_order' => 8,
        'is_published' => 1,
    ],
    [
        'id' => 9,
        'slug' => 'utiliser-python',
        'title' => 'Utiliser Python',
        'summary' => 'Écrire un petit programme pour calculer, simuler ou vérifier.',
        'domain' => 'NSI / Maths / Physique',
        'levels' => 'Lycée',
        'accent' => '#4c1d95',
        'icon' => 'Py',
        'content' => '<a href="#" class="nav-link">← Toutes les méthodes</a>

  <section class="method-page-hero mt-5 p-6 sm:p-9">
    <span class="badge">NSI / Maths / Physique</span>
    <h1 class="page-title mt-4">Utiliser Python</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Écrire un petit programme pour calculer, simuler ou vérifier.</p>
  </section>

<section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">1</span> Étapes à suivre</h2>
    <div class="method-steps mt-5"><div class="method-step">Identifier les données d’entrée.</div><div class="method-step">Créer des variables avec des noms clairs.</div><div class="method-step">Écrire la formule ou l’algorithme.</div><div class="method-step">Afficher un résultat lisible.</div><div class="method-step">Tester avec plusieurs valeurs.</div></div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">2</span> Exemple et formule</h2>
    <div class="method-formula mt-5">Exemple Python : `v = d / t` puis `print(v)`.</div>
    <div class="method-image-placeholder mt-5">Emplacement image / schéma / capture pour cette méthode</div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">3</span> Mini-exercice corrigé</h2>
    <div class="method-example mt-5">
      <p class="font-black text-slate-950 dark:text-white">Exercice</p>
      <p class="mt-2 muted">Écrire une fonction qui calcule une vitesse à partir d’une distance et d’une durée.</p>
      <details class="method-correction">
        <summary>Voir la correction</summary>
        <div class="method-correction-body">`def vitesse(d,t): return d/t` puis tester avec `vitesse(12,3)` qui donne 4.</div>
      </details>
    </div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">4</span> À retenir</h2>
    <div class="method-example mt-5">
      <p class="font-black">Méthode courte</p>
      <p class="mt-2 muted">Je lis la consigne, je repère les données, je choisis la méthode, je rédige clairement et je vérifie le résultat.</p>
    </div>
  </section>',
        'sort_order' => 9,
        'is_published' => 1,
    ],
    [
        'id' => 10,
        'slug' => 'preparer-une-evaluation',
        'title' => 'Préparer une évaluation',
        'summary' => 'Organiser une révision efficace avant un contrôle.',
        'domain' => 'Méthode générale',
        'levels' => 'Tous niveaux',
        'accent' => '#0f172a',
        'icon' => '✓',
        'content' => '<a href="#" class="nav-link">← Toutes les méthodes</a>

  <section class="method-page-hero mt-5 p-6 sm:p-9">
    <span class="badge">Méthode générale</span>
    <h1 class="page-title mt-4">Préparer une évaluation</h1>
    <p class="mt-4 max-w-3xl text-base leading-8 muted">Organiser une révision efficace avant un contrôle.</p>
  </section>

<section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">1</span> Étapes à suivre</h2>
    <div class="method-steps mt-5"><div class="method-step">Lire la liste des compétences exigibles.</div><div class="method-step">Refaire les exercices corrigés sans regarder la correction.</div><div class="method-step">Faire les QCM du chapitre.</div><div class="method-step">Réviser les flashcards difficiles.</div><div class="method-step">Faire une fiche bilan courte.</div></div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">2</span> Exemple et formule</h2>
    <div class="method-formula mt-5">Plan de révision : comprendre → s’entraîner → se tester → corriger.</div>
    <div class="method-image-placeholder mt-5">Emplacement image / schéma / capture pour cette méthode</div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">3</span> Mini-exercice corrigé</h2>
    <div class="method-example mt-5">
      <p class="font-black text-slate-950 dark:text-white">Exercice</p>
      <p class="mt-2 muted">Construire un plan de révision de 30 minutes pour un chapitre.</p>
      <details class="method-correction">
        <summary>Voir la correction</summary>
        <div class="method-correction-body">10 min bilan, 10 min exercices, 5 min QCM, 5 min flashcards et erreurs fréquentes.</div>
      </details>
    </div>
  </section>

  <section class="part-card mt-8">
    <h2 class="pedago-section-title"><span class="pedago-section-num">4</span> À retenir</h2>
    <div class="method-example mt-5">
      <p class="font-black">Méthode courte</p>
      <p class="mt-2 muted">Je lis la consigne, je repère les données, je choisis la méthode, je rédige clairement et je vérifie le résultat.</p>
    </div>
  </section>',
        'sort_order' => 10,
        'is_published' => 1,
    ],
];
