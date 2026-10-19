# V79 — Lecture longue + nouveau motif de fond

Deux demandes : rendre la fiche « Apprendre à apprendre » lisible, et changer le
motif de fond.

## 1. « Apprendre à apprendre » : d'une grille de cartes à un document

**Le diagnostic.** Le contenu faisait 12 sections et 72 cartes, rendues en
**grilles de 2 à 3 colonnes**, chaque carte étant une boîte bordée sur fond gris,
et chaque section une autre boîte. Résultat : des colonnes de 25 caractères de
large, 70 blocs qui se concurrencent visuellement, et aucune continuité de lecture.

**Ce qui change :**

- **colonne de lecture unique** — les grilles passent en `display: block` ;
- **mesure limitée à 70 caractères** et interligne 1,78 sur tout le texte ;
- **plus de boîtes** : les 72 cartes deviennent des sous-sections sans cadre,
  séparées par du blanc ; les 12 sections sont séparées par un filet ;
- **hiérarchie typographique** : titres de section et sous-titres en Fraunces,
  étiquette de section en petites capitales colorées, chapô sous chaque titre ;
- **encadrés unifiés** (règle d'or, à faire, à éviter, attention, citation,
  mémoire, concentration) : même forme, filet de couleur à gauche, fond doux ;
- **chronologie J0 · J1 · J3 · J7** : vraie frise verticale avec pastilles ;
- **sommaire** collant sur ordinateur (section en cours mise en évidence) et
  bandeau de pastilles défilant sur mobile, plus une longue liste qui poussait
  le texte vers le bas ;
- **confort de lecture** : barre de progression en haut, compteur
  (« 3 063 mots · ≈ 15 min de lecture · 12 sections ») et bouton de retour en
  haut, activés uniquement sur les fiches de plus de 900 mots.

## 2. Nouveau motif : un semis de points

Le quadrillage est remplacé par un **semis de points** (20 px), plus discret et
plus moderne, toujours limité aux pages intérieures — l'accueil reste sur fond uni.

Toujours en CSS pur, sans image. Trois variantes sont documentées dans
`src/ds-5-warm.css` (quadrillage, hachures à 45°, lignes horizontales) : il suffit
de remplacer la 3ᵉ couche de `background-image` pour changer de motif.

## 3. Bug corrigé au passage

- La « Règle d'or » héritait d'un **texte clair sur fond clair** (la couche
  historique la croyait sur fond sombre) : elle est maintenant en encadré encre,
  lisible dans les deux thèmes.
- Les **titres d'accent des fiches courtes** étaient en encre foncée sur fond
  foncé en mode sombre (angle mort de l'audit précédent, qui ignorait les fonds
  dégradés) : corrigé.

## Vérifications

- contraste AA : fiche longue et fiches courtes, dans les deux thèmes, plus
  accueil, niveau, chapitre et méthodes — aucune alerte hors approximation du
  logo (fond dégradé) ;
- 27 pages publiques + 23 pages d'administration en HTTP 200 ;
- `php -l` et `node --check` sans erreur ;
- mesures relevées : sidebar 264 px + colonne 872 px, mesure de texte 706 px,
  grilles en `display: block`, cartes sans bordure, sommaire `sticky`.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=79`, cache service worker `cours-gedion-v79`.
Aperçus dans `apercus-v79/`.
