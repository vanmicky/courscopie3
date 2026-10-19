# V109 — QCM : vert et rouge plus francs

Demande : « pour les QCM, utiliser un vert et un rouge plus foncés ».

## Le problème

Le thème monochrome employait des verts et rouges très désaturés :

| Rôle | Ancienne valeur |
| --- | --- |
| Réussi | `#476b55` (vert grisé) |
| À revoir | `#754b47` (rouge-brun) |

Sur un QCM, « réussi » et « à revoir » se distinguaient mal, et « à revoir »
utilisait en réalité la couleur **ambre** du brouillon.

## Les nouvelles valeurs (QCM uniquement)

| État | Fond | Texte | Bordure |
| --- | --- | --- | --- |
| Réponse juste | `#f0fdf4` | **`#14532d`** | `#86efac` |
| Réponse fausse | `#fef2f2` | **`#7f1d1d`** | `#fca5a5` |
| Pastille « Réussi » | `#f0fdf4` | `#14532d` | `#86efac` |
| Pastille « À revoir » | `#fef2f2` | `#7f1d1d` | `#fca5a5` |

En mode sombre : fonds `#052e16` / `#2a0a0a`, textes `#bbf7d0` / `#fecaca`.

La pastille « À revoir » n'utilise plus l'ambre : elle est désormais **rouge**,
ce qui correspond à son sens.

## Mesures

| Contrôle | Résultat |
| --- | --- |
| Réponse juste (clair) | `rgb(240,253,244)` / `rgb(20,83,45)` |
| Réponse fausse (clair) | `rgb(254,242,242)` / `rgb(127,29,29)` |
| Mode sombre | `rgb(5,46,22)` / `rgb(187,247,208)` et `rgb(42,10,10)` / `rgb(254,202,202)` |
| Contraste AA clair + sombre | aucune alerte |
| `php -l` | sans erreur |

## Périmètre

Nouvelle couche `src/ds-18-qcm.css`, qui ne touche **que** les composants du
QCM (`.qcm-choice`, `.status-pill.is-qcm-good/.is-qcm-bad`, `.qcm-result`).
Le reste du site conserve sa palette monochrome.

## Mise à niveau

Aucune migration SQL. CSS `?v=109`, cache service worker `cours-gedion-v109`.
Aperçus : `apercus/qcm-couleurs.jpg`, `qcm-couleurs-sombre.jpg`.
