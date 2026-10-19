# V101 — Raccourcis de niveau sur une seule ligne

## Le problème

Les quatre raccourcis du niveau portaient un titre **et** une description :

| Avant | Après |
| --- | --- |
| **Travail à faire** · Ce qui est à préparer | **Travail à faire** |
| **Exercices** · S'entraîner | **Exercices** |
| **QCM** · Correction immédiate | **QCM** |
| **Flashcards** · Mémoriser | **Flashcards** |

À cause de ces descriptions, les puces passaient à la ligne et formaient
plusieurs rangées irrégulières.

## La correction

- les descriptions sont **supprimées** de `niveau.php` : seuls les libellés
  restent ;
- les puces deviennent des **pastilles compactes** (hauteur 2,1 rem, rayon
  complet, texte sur une ligne) ;
- le groupe ne passe plus à la ligne sur ordinateur (`flex-wrap: nowrap`).

Sur téléphone, les quatre pastilles s'organisent en **2 × 2** : c'est le seul
moyen de rester lisible sur 390 px de large.

## Mesures

| Largeur | Lignes | Largeur totale des puces | Place disponible | Débordement |
| --- | --- | --- | --- | --- |
| 1440 px | **1** | 374 px | 797 px | non |
| 1100 px | **1** | 374 px | 656 px | non |
| 900 px | **1** | 374 px | 476 px | non |
| 390 px | 2 (2 × 2) | — | — | non |

## Vérifications

- 4 puces, 1 seule ligne de 900 px à 1440 px ;
- aucun débordement horizontal, desktop et mobile ;
- contraste AA en clair et en sombre : aucune alerte ;
- page `niveau.php` en HTTP 200, sans erreur PHP ;
- CSS servi avec `?v=101`, cache service worker `cours-gedion-v101`.

Aperçus : `apercus/niveau-raccourcis.jpg`, `niveau-raccourcis-mobile.jpg`.
