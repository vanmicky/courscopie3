# V78 — Motif de fond sur les pages intérieures

Demande : un fond avec motif sur toutes les pages **sauf l'accueil**.

## Ce qui est fait

Un **quadrillage discret de papier millimétré**, dessiné uniquement en CSS
(deux `repeating-linear-gradient` pour le petit carreau de 22 px et la ligne
majeure tous les 5 carreaux, soit 110 px). Aucune image à télécharger.

- **Accueil** : fond uni conservé (les grandes cartes de niveau respirent).
- **Toutes les autres pages** : niveau, chapitre, exercices, QCM, flashcards,
  méthodes, fiche méthode, travail à faire, examen, minuteur, 404, administration.

## Comment ça marche

`page_head()` ajoute une classe au `<body>` :

| Page | Classe |
| --- | --- |
| `index.php` | `page-home` — pas de motif |
| Toutes les autres | `page-inner` — motif |
| Administration | `admin-area page-inner` — motif |

La détection est automatique (nom du script courant), avec une option
`page_head(..., ['home' => true])` si tu veux forcer l'un ou l'autre.

## Lisibilité

Le quadrillage est très clair, mais il assombrit légèrement le fond : le gris du
texte secondaire (`--ds-text-faint`) a été assombri à `#635d51` pour rester
au-dessus de 4,5:1 **même sur la ligne majeure**.

Contrastes mesurés (texte le plus clair posé directement sur le motif) :

| | fond de base | ligne fine | ligne majeure |
| --- | --- | --- | --- |
| Mode clair | 5,84:1 | 5,15:1 | **4,57:1** |
| Mode sombre | 5,95:1 | 5,47:1 | **4,98:1** |

Les cartes, le pied de page et les héros ont des fonds opaques : le motif ne
passe pas derrière le texte des contenus.

## Réglages

Tout est dans `src/ds-5-warm.css` :

| Ce que tu veux | Jeton |
| --- | --- |
| Intensité du petit carreau | `--ds-pattern-line` |
| Intensité de la ligne majeure | `--ds-pattern-line-major` |
| Taille des carreaux | `--ds-pattern-size` (22 px) |

Par exemple, un motif plus marqué : `--ds-pattern-line: rgba(122,100,66,.16)`.
Pour l'enlever d'une page précise, retirer la classe `page-inner` sur son `<body>`.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=78`, cache service worker `cours-gedion-v78`.
Aperçus dans `apercus-v78/`.
