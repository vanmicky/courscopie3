# V89 — Composants « façon HyperUI », noir et blanc

Deux demandes : utiliser des composants HyperUI, et mettre le site en noir et
blanc.

## Une précision d'abord

Les composants HyperUI sont écrits pour **Tailwind CSS** (classes utilitaires).
Ce site a un build volontairement sans Node/npm : son CSS est assemblé à la main
par `tools/build-css.sh`. Introduire Tailwind réclamerait une chaîne de build et
migrer toutes les pages — un chantier lourd et risqué.

On a donc reproduit fidèlement **l'esthétique HyperUI** — minimaliste, bordée,
monochrome — avec les jetons du design system existant. C'est le rendu qui
compte, et il est là, sans casser le projet.

## Noir et blanc

Le thème monochrome (`src/ds-9-mono.css`) est **réactivé** : neutres en gris,
marque graphite, dégradés graphite, ombres grises, motif de points gris, mode
sombre `#0b0b0c`. La seule couleur restante vient des **matières** (niveau,
chapitre, domaine), en accent discret.

## L'esthétique HyperUI, composant par composant

| Composant | Avant | Façon HyperUI |
| --- | --- | --- |
| Bouton primaire | dégradé graphite + liseré | **plein quasi noir** `#18181b`, texte blanc, `hover` gris, rayon 8 px |
| Bouton secondaire | surface grise | **blanc + bordure grise** `#d4d4d8`, `hover` `#f4f4f5` |
| Cartes | dégradé vertical | **blanc plat + bordure `#e4e4e7`** + ombre `0 1px 2px` |
| Héros | dégradé diagonal | **panneau blanc** à bordure fine |
| Rayons | 14 / 20 / 26 px | **6 / 8 / 10 / 14 / 16 px** (échelle resserrée) |
| Champs | dégradé | **blanc + bordure grise + anneau de focus fin** |
| Pastilles | colorées | **grises** `#f4f4f5` / `#3f3f46` |
| Mode sombre | — | bouton primaire **blanc** (inversé), cartes `#17171a` |

## Deux défauts corrigés en route

1. La **carte de niveau** gardait un dégradé : une règle historique plus
   spécifique (`.editorial-home .level-card`) l'emportait. Reprise explicitement
   pour un fond plat et un rayon de 12 px.
2. En voulant assourdir les accents, j'avais forcé la couleur du **numéro de
   chapitre** alors que son fond est coloré : texte devenu illisible (1,03:1).
   Annulé — il garde son blanc sur fond d'accent.

## Vérifications

- bouton primaire : `rgb(24,24,27)` plat, blanc, rayon 8 px ; secondaire blanc
  bordé ; badge gris `rgb(244,244,245)` ;
- carte de niveau : blanc plat, bordure `#e4e4e7`, rayon 12 px, ombre subtile ;
- **contraste AA en clair et en sombre sur 15 pages** : aucune alerte (hors faux
  positifs de dégradés : logo, boutons, titre dégradé) ;
- 18 pages publiques en HTTP 200 sans erreur PHP, `php -l` et `node --check`
  sans erreur ;
- `theme-color` et `manifest.webmanifest` alignés sur le noir et blanc.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=89`, cache service worker
`cours-gedion-v89`. Aperçus dans `apercus/`.
