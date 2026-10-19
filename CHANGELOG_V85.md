# V85 — Nuances de gris

Demande : améliorer le rendu monochrome en jouant sur les **nuances de gris**,
plutôt qu'un gris plat.

## Une échelle de gris, enfin nommée

`--ds-grey-0` (blanc) à `--ds-grey-950` (quasi noir), en quatorze paliers. Les
jetons sémantiques (`--ds-bg`, `--ds-surface`, `--ds-border`, `--ds-text`…)
pointent dessus : la palette est cohérente partout, et un changement se fait à
un seul endroit.

## Ce qui crée la nuance

| Procédé | Application |
| --- | --- |
| **Paliers tonals** | fond `grey-100` < surface blanche < surface surélevée ; en-tête `grey-0` à 86 %, pied de page `grey-50 → grey-100` |
| **Dégradés de surface** | chaque carte passe de blanc à `grey-50` sur sa hauteur (2 à 3 % d'écart) : les aplats disparaissent |
| **Héros détachés** | bandeau `grey-0 → grey-150` en diagonale, qui reçoit par-dessus la teinte de la matière |
| **Fond de page** | dégradé vertical `grey-50 → grey-150` fixe, plus deux halos gris |
| **Champs et tableaux** | `grey-50 → grey-0` pour les saisies, `grey-150 → grey-100` pour les en-têtes de tableau |
| **Boutons** | dégradé graphite + liseré clair en haut (relief) + ombre posée |
| **Ombres** | plusieurs couches grises (`rgba(24,24,27,…)`) au lieu d'une seule |
| **Hiérarchie par le ton** | encre `grey-900`, texte courant `grey-600`, texte discret `grey-500` |
| **Motif** | semis de points à 8,5 % au lieu de 10 % : plus fin, moins présent |

Le mode sombre possède sa propre échelle inversée, avec les mêmes paliers.

## Deux défauts trouvés et corrigés

1. **Encadré « Règle d'or » blanc sur texte clair en mode sombre.** En inversant
   l'échelle, `var(--ds-grey-900)` vaut un gris très clair en thème sombre :
   l'encadré devenait blanc sous un texte clair (1,1:1). Les fonds qui doivent
   rester sombres sont désormais écrits en dur.
2. **Info-bulle des flashcards illisible** : du vert à 72 % d'opacité sur fond
   blanc, soit 2,9:1. Elle passe en encre d'accent opaque (≈ 7,5:1).

## Vérifications

- échelle de gris servie : `--ds-grey-50 = #f8f8f9`, `--ds-grey-900 = #18181b` ;
- fond de page : dégradé + halos ; cartes, héros, en-tête, pied de page, champs
  et tableaux : dégradés présents (mesurés un par un, y compris la carte de
  niveau dont une règle historique plus spécifique écrasait le dégradé) ;
- **contraste AA en clair et en sombre sur 15 pages** : aucune alerte hors les
  faux positifs connus du logo et des boutons (texte blanc sur dégradé graphite,
  que l'outil approxime par la couleur de base) ;
- les accents de matière restent colorés : `#2563eb`, `#16a34a`, `#f97316` ;
- mode sombre : fond `rgb(11,11,12)`, écart entre canaux de 1 ;
- 18 pages publiques en HTTP 200, `php -l` et `node --check` sans erreur.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=85`, cache service worker
`cours-gedion-v85`. Aperçus dans `apercus/`.
