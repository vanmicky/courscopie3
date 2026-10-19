# V90 — Héros compactes

Les deux héros (accueil et page niveau) étaient trop hauts, avec des marges
généreuses et du vide. Ils sont redessinés.

## Le diagnostic, chiffré

| | Avant | Après |
| --- | --- | --- |
| Héros « niveau » | **378 px** | **192 px** |
| Colonne de texte du niveau | 226 px | 158 px |
| Vide sous le texte | **152 px** | 34 px |
| Titre (niveau) | 50,4 px | ~36 px |
| Illustration | 490 × 325 px (impose la hauteur) | 300 × 190 px |
| Pastilles de ressources | 64 px de haut | 37 px, une seule rangée |
| Héros « accueil » | **270 px** (dans un carton blanc) | **155 px** (bandeau) |
| Titre (accueil) | 50,4 px, sur deux lignes | 34,4 px, sur une ligne |

## Ce qui a changé

### Accueil
- **Plus de carton blanc** autour du héros : un bandeau simple, séparé de la
  grille par un filet fin.
- **Texte à gauche, recherche alignée à droite** sur la même ligne (elle
  flottait dans un grand vide).
- Titre réduit et ramené sur une ligne, pastilles de matières compactées,
  compteur « 6 niveaux » rapproché.
- **24 px de respiration** entre le bandeau et les cartes (il n'y en avait
  aucun : les cartes collaient au filet).

### Niveau
- **Illustration bornée à 300 px de large** : sa hauteur tombe à 190 px au lieu
  de 325, et c'est elle qui imposait la hauteur du héros.
- **Plus de vide** : la colonne de texte (158 px) et l'illustration (192 px)
  sont désormais centrées l'une par rapport à l'autre, au lieu d'un bloc de
  226 px perdu dans 378 px.
- **Pastilles sur une seule rangée**, en format compact (titre + précision sur
  la même ligne).
- **Légende « 5e · Physique-Chimie » supprimée** : elle répétait le titre et le
  badge.
- Marge haute de la page réduite de 32 px à 16 px.

### Téléphone
- L'illustration est **masquée** sous 760 px : elle n'apporte rien et prenait
  la moitié de l'écran.
- Les pastilles passent en deux colonnes, la recherche occupe toute la largeur.

## Vérifications

- mesures avant/après relevées dans le navigateur (tableau ci-dessus) ;
- contraste AA des deux héros en clair et en sombre : aucune alerte ;
- 18 pages publiques en HTTP 200 sans erreur PHP ;
- `php -l` et `node --check` sans erreur ;
- rendu mobile contrôlé à 390 px : héros 223 px (accueil), 230 px (niveau).

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=90`, cache service worker
`cours-gedion-v90`. Aperçus : `apercus/heros-accueil.jpg`,
`apercus/heros-niveau.jpg` et les versions mobiles.
