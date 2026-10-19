# V80 — Onglets horizontaux sur les pages chapitre

Demande : ne plus avoir à faire défiler une page chapitre entière pour trouver
les QCM ou les flashcards. Des onglets horizontaux, des pastilles.

## Ce qui change

Une page chapitre empilait jusqu'à **8 sections** (documents, exercices, brevet
ou type bac, vidéos, QCM, flashcards, savoirs). Il fallait défiler parfois sur
plusieurs écrans.

Désormais une **barre de pastilles collante** se place sous l'en-tête, et une
seule section est affichée à la fois :

| Pastille | Contenu |
| --- | --- |
| 📄 Documents | cours, TP, corrigés, fiches méthode |
| ✏️ Exercices | entraînement pour le DS |
| ✏️ Brevet / Type Bac | selon le niveau (troisième, première) |
| 🎬 Vidéos | vidéos du chapitre |
| ✅ QCM | auto-évaluation |
| 🗂️ Flashcards | mémorisation |
| 🎯 Savoirs | savoirs et savoir-faire à cocher |

Chaque pastille affiche le **nombre d'éléments** qu'elle contient. Les sections
vides ne sont pas proposées : un élève ne clique pas pour tomber sur « aucun
contenu ». Si le chapitre n'a encore rien, une seule mention « Chapitre en
préparation » remplace la barre.

## Détails pratiques

- **Lien direct** : `chapitre.php?slug=…​#videos` ouvre directement l'onglet
  Vidéos. Les liens depuis la page niveau (`#documents`, `#exercices`) continuent
  de fonctionner.
- **Barre collante** : elle reste visible pendant la lecture de l'onglet.
- **Mobile** : pastilles défilant horizontalement, sans icônes, sur toute la
  largeur.
- **Clavier** : flèches ← →, Début et Fin pour circuler entre les onglets.
- **Accessibilité** : `role="tablist"`, `role="tab"`, `aria-selected`,
  `aria-controls`, `role="tabpanel"`, `aria-labelledby`.
- **Sans JavaScript** : la barre reste un jeu de liens d'ancre et toutes les
  sections sont visibles, exactement comme avant. Rien n'est masqué côté serveur.
- **Impression** : la barre disparaît et **toutes les sections** sont imprimées.

## Fichiers

- `chapitre.php` : construction des onglets, sections conditionnelles, panneaux.
- `assets/js/chapter-tabs.js` : amélioration progressive, clavier, lien direct.
- `src/ds-7-tabs.css` : styles de la barre, des pastilles et des panneaux.
- `tools/build-css.sh` : assemble désormais 7 sources.

## Vérifications

- onglets mesurés en navigateur : barre `role="tablist"`, 7 pastilles, une seule
  section visible, clic sur « Vidéos » → `#videos` et section basculée ;
- lien direct `#videos` → onglet actif au chargement ;
- HTML servi sans attribut `hidden` : le repli sans JavaScript est intact ;
- chapitre sans contenu → « Chapitre en préparation », sans barre ;
- chapitre à une seule section → pas de barre ni de script inutile ;
- contraste AA de la barre en clair et en sombre ; 27 pages publiques + 23 pages
  d'administration en HTTP 200 ; `php -l` et `node --check` sans erreur.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=80`, cache service worker
`cours-gedion-v80`. Aperçus dans `apercus-v80/`.
