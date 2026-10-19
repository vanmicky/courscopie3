# V121 — Images dans les pages QCM/Flashcards, recto et verso, cartes compactes

## 1. Les pages dédiées ont maintenant tout

`admin/qcm.php` et `admin/flashcards.php` (menu **Activités**) permettent
désormais :

- d'**ajouter et modifier** les questions et les cartes (c'était déjà là) ;
- d'**envoyer une image** directement dans le formulaire ;
- pour les cartes : **une image pour le recto ET une pour le verso** ;
- pour les QCM : **2 à 6 réponses** au lieu de 4 obligatoires.

Les mêmes fonctions sont disponibles dans **Programme → chapitre** (onglet
Flashcards / QCM).

## 2. Recto et verso illustrés

Deux jeux de colonnes sont créés automatiquement : `image_path` / `image_width`
(recto, ou question) et `image_back_path` / `image_back_width` (verso).

| Face | Image | Largeur |
| --- | --- | --- |
| Recto de la carte | affichée sous le titre | réglable 10–100 % |
| Verso de la carte | affichée sous « Réponse » | réglable 10–100 % |
| Question de QCM | affichée au-dessus de l'énoncé | réglable 10–100 % |

## 3. Cartes compactes dans Programme → chapitre

Signalé : « les flashcards occupent un espace énorme ».

**Cause mesurée** : la liste des cartes est une grille placée à côté du
formulaire. Par défaut, une grille **étire ses lignes** pour remplir la hauteur
reçue : chaque carte prenait donc la hauteur du formulaire voisin.

| Mesure | Avant | Après |
| --- | --- | --- |
| Hauteur d'une carte | **332 px** | **212 px** |

Correction : `align-content: start` sur la liste.

## 4. Vérifications

| Contrôle | Résultat |
| --- | --- |
| QCM (page dédiée) — 2 réponses + image | enregistré : 2 réponses, image `45 %` |
| Carte (page dédiée) — recto + verso | recto `40 %`, verso `55 %` |
| Affichage public — recto | image visible, largeur `40 %` |
| Affichage public — verso | image `55 %`, fichier correct |
| Blocs image dans les pages | 2 sur flashcards, 1 sur QCM |
| Réponses proposées | A à F |
| Suite de tests | **66 vérifications** |
| Audit complet | 0 problème |

## Mise à niveau

Colonnes créées automatiquement. CSS `?v=121`, JS `?v=121`, cache service worker
`cours-gedion-v121`.
Aperçus : `apercus/admin-flashcards-editeur.jpg`, `admin-qcm-editeur.jpg`.
