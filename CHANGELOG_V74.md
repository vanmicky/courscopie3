# V74 — Refonte visuelle, lisibilité et ergonomie

Cette version ne modifie **aucune fonctionnalité** : les écrans, les formulaires, les
routes, les tables SQL et les scripts existants restent en place. Le travail porte sur
la couche visuelle, la lisibilité, l'accessibilité et l'ergonomie.

## Feuille de style réorganisée

`dist/output.css` était un fichier unique de 7 299 lignes, empilant quatre couches
d'ajustements successives (V69 → V73) qui se contredisaient (`!important` en cascade,
trois échelles typographiques concurrentes, 127 classes utilitaires utilisées dans les
gabarits mais absentes du CSS).

Le fichier est désormais **généré** à partir de sources lisibles :

| Source | Rôle |
| --- | --- |
| `src/legacy-v73.css` | composants historiques conservés tels quels (aucune régression) |
| `src/ds-1-base.css` | jetons, remise à zéro, typographie, utilitaires complets |
| `src/ds-2-components.css` | boutons, cartes, formulaires, tableaux, états, lecture |
| `src/ds-3-pages.css` | pages publiques, administration, outils, impression |

Régénérer : `tools/build-css.sh` (bash + `cat`, aucune dépendance npm).
L'ancien fichier est conservé dans `src/output-v73-full.backup.css`.

### Ce que la réorganisation corrige

- les **127 classes utilitaires manquantes** (`flex-col`, `md:flex-row`,
  `xl:grid-cols-3`, `h-20`, `mt-7`, `mt-9`, `bg-white/50`, `tracking-tight`…) sont
  maintenant définies ; les mises en page reprennent l'intention d'origine ;
- une **seule échelle typographique** fluide (`clamp()`) remplace les trois
  concurrentes ;
- les **couleurs d'accent** sont recalculées au bon niveau : `--accent-deep`,
  `--accent-ink`, `--accent-soft` étaient figés sur l'indigo par héritage de `:root`,
  les pastilles de chapitre restaient donc indigo sur un niveau vert.

## Design system

- jetons nommés : couleurs, espaces (`--ds-space-*`), rayons, ombres, focus, mouvement ;
- palette claire et sombre vérifiée (contrastes AA) ;
- suppression des décors de fond en diagonale qui concurrençaient le texte ;
- composants unifiés : boutons (tailles, variantes, état désactivé), étiquettes,
  cartes, formulaires, tableaux, alertes, états vides, barres de progression.

## Pages publiques

- **Accueil** : bandeau éditorial, matières en pastilles, recherche alignée, cartes de
  niveau en 3:2 avec pied de carte aligné.
- **Niveau** : visuel pleine largeur légendé, raccourcis Travail / Exercices / QCM /
  Flashcards, bandeau Brevet ou Bac, grille de chapitres.
- **Chapitre** : bandeau enrichi d'un compteur de ressources, sections espacées,
  états vides compacts, icônes de type de document, phrase d'attente quand un exercice
  n'a pas encore de correction.
- **Exercices** : compteur, réinitialisation des filtres, mesure de lecture limitée.
- **QCM** : colonne de lecture centrée, barre d'action collante avec compteur de
  réponses, bouton désactivé tant qu'aucune réponse n'est donnée, bilan chiffré et
  appréciation après correction, bouton « Recommencer ».
- **Flashcards** : consigne, bilan, boutons d'auto-évaluation en couleurs sémantiques
  (vert « réussi », orange « pas encore ») au lieu de l'accent du niveau.
- **Travail à faire** : badge d'échéance assombri (contraste), mention « échéance
  passée », liens externes distingués des fichiers, réinitialisation des filtres.
- **Espace Brevet / Bac** : bandeau de statistiques, filtres alignés, cartes d'exercice
  à hauteur égale.
- **Timer de classe** : intégré au chrome du site (en-tête, thème clair/sombre, pied de
  page), feu tricolore redessiné, `aria-pressed` sur les feux, suppression du fond teinté
  derrière le compteur.

## Administration

- **Connexion** : carte centrée, champs empilés, `autocomplete`, et **jeton CSRF** ;
- **Tableau de bord** : 10 statistiques en grille compacte cliquable (la page passe de
  2 494 px à ~1 200 px de haut) ;
- **Barre latérale** : groupes aérés, état actif lisible, `admin-area` posé par
  `page_head()` (plus de script en ligne) ;
- formulaires : suppression du libellé « Action » devant les boutons de validation
  (11 occurrences), champs de filtres alignés, badges de tableau sur une seule ligne ;
- **Chapitres** : recherche instantanée dans la liste (41 chapitres) avec compteur.

## Navigation et contenu

- le lien **Méthodes** est ajouté à la navigation : les 11 fiches méthodes
  (`pages/methodes/`) étaient orphelines, accessibles uniquement par URL directe ;
- pied de page enrichi (niveaux, travail à faire, QCM, flashcards, méthodes, espace
  enseignant) ;
- nom du site et version mis à jour (V74).

## Accessibilité

- anneau de focus unique et visible, lien d'évitement, `aria-live` sur les compteurs ;
- contrastes contrôlés automatiquement sur les pages publiques, statiques et outils :
  plus aucun texte sous le seuil AA ;
- couleurs d'auto-évaluation sémantiques sur les flashcards et les QCM interactifs ;
- `prefers-reduced-motion` respecté, styles d'impression pour les fiches d'exercices.

## Vérifications effectuées

- `php -l` sur les 68 fichiers PHP : aucune erreur ;
- 32 URL publiques + 24 URL d'administration : HTTP 200, aucune erreur PHP visible
  (les 404 attendues le restent) ;
- captures d'écran 1440 px et 390 px, thème clair et thème sombre ;
- audit de contraste automatique (calcul du ratio réel sur les fonds effectifs).

## Mise à niveau

Aucune migration SQL. Remplacer les fichiers, puis vider le cache du navigateur
(le CSS est servi avec `?v=74` et le service worker utilise le cache `cours-gedion-v74`,
donc l'ancien cache est supprimé automatiquement).

## Aperçus

Le dossier `apercus-v74/` contient des captures de contrôle (1440 px et 390 px, clair et
sombre) : `accueil.png`, `niveau-premiere-nsi.png`, `chapitre.png`,
`admin-tableau-de-bord.png`, `timer-mobile.png`, `accueil-sombre.png`.
