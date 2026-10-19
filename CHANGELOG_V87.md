# V87 — Couleur de matière dans la navigation, icônes partout, logo coloré

Quatre demandes : le menu qui prend la couleur du niveau au survol, les pastilles
colorées **avec icônes**, des icônes un peu partout, et le logo « MG » qui change
de couleur.

## 1. Le menu prend la couleur du niveau

Chaque lien de niveau porte désormais son propre `--accent` (5e bleu, 3e vert,
2de PC orange, 2de Maths ambre, 1re PC rouge, 1re NSI violet). Au survol :

- fond teinté à 11 % de la couleur du niveau ;
- texte dans l'encre de cette couleur ;
- **pastille de couleur** qui grossit légèrement.

« Méthodes » et « Outils » suivent la couleur de la page en cours, sinon celle
de la marque.

Mesuré en navigateur avec un vrai état de survol (`CSS.forcePseudoState`) :

| Lien | Fond au survol | Texte |
| --- | --- | --- |
| 5e | bleu très clair | bleu foncé `#183B86` |
| 3e | vert très clair | vert foncé `#115A35` |
| 1re PC | rouge très clair | rouge foncé `#741C23` |

## 2. Pastilles colorées et icônes

Les emoji des pastilles sont remplacés par un **jeu d'icônes SVG maison**
(`includes/icons.php`, 32 symboles, tracés 24×24 en `currentColor`).

| Pastille | Icône |
| --- | --- |
| Documents | page |
| Exercices / Brevet / Type Bac | crayon / médaille / toque |
| Vidéos | écran |
| QCM | coche |
| Flashcards | cartes |
| Savoirs | cible |
| Minuteur / Groupes / Tirage / Consignes / Dés | horloge / groupe / dé / panneau / courbe |

Les pastilles s'éclairent de la couleur de la matière au survol, et l'icône de
la pastille active passe en blanc.

## 3. Des icônes un peu partout

- **menu** : pastilles de couleur + icônes sur Méthodes et Outils ;
- **pied de page** : une icône par lien ;
- **titres de section** d'un chapitre : icône colorée à la teinte de la matière ;
- **cartes de niveau** : flèche animée sur « Accéder aux chapitres » ;
- **outils de classe** : icônes sur Copier, Imprimer, Plein écran ;
- **bouton de thème** : soleil / lune en SVG (l'emoji a disparu).

Le sprite est inséré **une seule fois** en bas de page : aucune requête
supplémentaire, et les icônes héritent de `currentColor`.

## 4. Le logo « MG » change de couleur

Le logo reprend la **couleur de la matière de la page** :

| Page | Couleur du logo |
| --- | --- |
| Accueil | dégradé de marque indigo → violet |
| `?level=troisieme` | vert |
| `?level=premiere-nsi` | violet |
| Chapitre, QCM, flashcards, fiche méthode | teinte de la matière |

La couleur est transmise dès l'en-tête : `page_head()` pose `--accent` sur
`<body>`, donc le logo, le menu et les pastilles la partagent. Au survol, le
logo pivote légèrement et prend une ombre de sa couleur.

## Vérifications

- 32 symboles dans le sprite, **23 icônes demandées toutes présentes**,
  15 icônes visibles rendues à 16-18 px ;
- survol du menu testé par pseudo-classe forcée : chaque niveau prend bien sa
  couleur ;
- logo mesuré : indigo sur l'accueil, vert sur `?level=troisieme`, violet sur
  `?level=premiere-nsi` ;
- **contraste AA en clair et en sombre sur 10 pages** : aucune alerte (hors faux
  positifs de dégradés : logo, boutons, titre dégradé) ;
- 18 pages publiques en HTTP 200 sans erreur PHP, `php -l` et `node --check`
  sans erreur.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=87`, cache service worker
`cours-gedion-v87`. Aperçus dans `apercus/`.
