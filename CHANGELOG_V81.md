# V81 — Outils de classe

Quatre demandes : masquer « Admin » du menu, améliorer l'outil de classe avec un
grand affichage, ajouter un générateur de groupes, et d'autres outils inspirés de
ClassroomScreen.

## 1. « Admin » retiré du menu

Le lien « Admin » a disparu de la navigation (ordinateur et mobile). L'accès
enseignant reste possible par le pied de page (« Espace enseignant ») ou par
l'adresse directe `/admin/login.php`.

## 2. Un espace « Outils de classe » à onglets

L'ancienne page minuteur devient un espace complet, accessible depuis le menu
(« Outils de classe ») et depuis le raccourci de l'application installée.
`timer.php` redirige vers `outils.php#minuteur`, donc les anciens liens
continuent de fonctionner.

### ⏱️ Minuteur
Minuteur, consignes rapides (2 à 30 min), feu tricolore de niveau sonore et
sonnerie. Nouveau : bouton **Plein écran** et raccourci <kbd>Maj</kbd> + <kbd>F</kbd>.
En plein écran, le compteur passe à environ 18 rem.

### 👥 Groupes
- on colle la liste des prénoms (un par ligne, ou séparés par des virgules) ;
- on choisit **le nombre de groupes** ou **la taille des groupes** ;
- option « mélanger » ;
- les groupes s'affichent en cartes colorées, avec le nombre d'élèves ;
- boutons **Copier** (pour coller dans un mail ou un ENT) et **Imprimer**
  (impression limitée aux groupes, sur deux colonnes).

La liste est mémorisée dans le navigateur : on la ressaisit une seule fois.

### 🎲 Tirage au sort
- **Tirer un élève** : le nom défile puis s'arrête sur un tirage aléatoire ;
- **Ordre de passage** : mélange la classe et affiche l'ordre numéroté ;
- plein écran pour projeter.

### 🪧 Consignes
Huit consignes en un clic (silence, chuchoter, travail individuel, travail de
groupe, question, prise de parole, écoute, rangement) plus une **consigne
personnalisée**. Le message s'affiche en très grand, avec code couleur
(calme, travail, question, fin de séance) et symbole.

### 🎯 Dés, chronomètre et horloge
- **Dés** : 1 à 4 dés, lancer animé, total affiché ;
- **Chronomètre** : départ, pause, reprise, remise à zéro, au dixième ;
- **Horloge** : heure courante en grand.

## Détails techniques

- Amélioration progressive : **sans JavaScript**, tous les panneaux restent
  visibles et les liens d'onglet font défiler la page. Rien n'est masqué côté
  serveur.
- Accessibilité : `role="tablist"` / `tab` / `tabpanel`, `aria-selected`,
  `aria-controls`, navigation au clavier (flèches, Début, Fin).
- `:fullscreen` agrandit chaque outil et masque l'en-tête du site.
- Nouveaux fichiers : `outils.php`, `src/ds-8-outils.css`,
  `assets/js/outils.js`, `outils-tirage.js`, `outils-consignes.js`,
  `outils-animation.js`.
- `manifest.webmanifest` : raccourci « Outils de classe ».
- `service-worker.js` : cache `cours-gedion-v81`, nouveaux scripts hors-ligne.

## Vérifications

Fonctionnel en navigateur :

- onglets : un seul panneau visible, `#groupes` → onglet Groupes ;
- groupes : 10 prénoms, 4 groupes → 3 + 3 + 2 + 2 = 10 élèves, compteur « 10 » ;
- tirage : un nom tiré, ordre de passage de 10 élèves ;
- consignes : « Travail de groupe », « Silence », consigne personnalisée ;
- dés : 5 lancers → valeurs indépendantes et totaux corrects (13, 13, 7, 9, 6) ;
- chronomètre : 00:00.7 après 700 ms ; horloge à l'heure système.

Aussi : contraste AA en clair et en sombre, 28 pages publiques + 23 pages
d'administration en HTTP 200, `php -l` et `node --check` sans erreur, `timer.php`
en 302 vers `outils.php#minuteur`.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=81`. Aperçus dans `apercus-v81/`.
