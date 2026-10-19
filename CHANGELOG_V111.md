# V111 — Cartes mémoire : recto et verso de deux couleurs distinctes

Demande : « mettre deux couleurs différentes, recto et verso ».

## Le problème

- le **recto** reprenait la couleur de la matière, en dégradé clair ;
- le **verso** utilisait un vert pâle.

Les deux faces se ressemblaient : en révision, on ne savait pas immédiatement si
l'on regardait la question ou la réponse.

## Le code de couleur retenu

| Face | Rôle | Fond | Texte | Bordure |
| --- | --- | --- | --- | --- |
| **Recto** | la question | `#eef2ff` (bleu indigo) | **`#1e3a8a`** | `#c7d2fe` |
| **Verso** | la réponse | `#f0fdf4` (vert) | **`#14532d`** | `#86efac` |

Étiquettes « Recto » / « Verso » assorties à leur face.

Mode sombre : recto `#111c33` / `#c7d2fe`, verso `#052e16` / `#bbf7d0`.

Le code est **le même dans toutes les séries** : bleu = question, vert =
réponse. Il rejoint le vert du QCM, donc « vert = validé » reste cohérent sur
tout le site.

## Vérifications

| Contrôle | Résultat |
| --- | --- |
| Recto (clair) | `rgb(238,242,255)` / `rgb(30,58,138)` |
| Verso (clair) | `rgb(240,253,244)` / `rgb(20,83,45)` |
| Recto (sombre) | `rgb(17,28,51)` / `rgb(199,210,254)` |
| Verso (sombre) | `rgb(5,46,22)` / `rgb(187,247,208)` |
| Contraste AA clair + sombre | aucune alerte |

## Mise à niveau

Aucune migration SQL. CSS `?v=111`, cache service worker `cours-gedion-v111`.
Aperçus : `apercus/flashcards-recto.jpg`, `flashcards-vers.jpg`,
`flashcards-recto-sombre.jpg`, `flashcards-vers-sombre.jpg`.
