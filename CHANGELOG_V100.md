# V100 — Cartes mémoire : recto/verso réparé

## Ce qui n'allait pas

La capture montrait la question **et** la réponse en même temps, dans une carte
énorme dont la moitié était vide.

Mesures avant correction (viewport 756 px) :

| Élément | Valeur |
| --- | --- |
| Face recto | `position: static`, hauteur **97 px** |
| Face verso | `position: static`, hauteur **57 px**, empilée dessous |
| Carte | hauteur imposée **320 px** → 149 px de vide |
| `backface-visibility` | `visible` |

**Cause :** la règle `.flash-side` qui superposait les deux faces avait disparu
des couches CSS. Sans `position: absolute` ni `backface-visibility: hidden`, les
deux faces s'empilaient dans le flux : la réponse s'affichait sous la question
et la carte gardait sa hauteur minimale, entièrement vide en bas.

## La correction

Nouvelle couche `src/ds-16-flashcards.css` :

- les deux faces sont **superposées** (`display: grid` + `grid-area: carte`) et
  occupent la même cellule ;
- `backface-visibility: hidden` + `preserve-3d` : une seule face visible,
  l'animation de retournement est conservée ;
- **sécurité** : `visibility: hidden` sur la face opposée quand la carte n'est
  pas tournée, pour rester correct même sans transformation 3D ;
- hauteur de carte ramenée à `clamp(14rem, 32vh, 20rem)` : **320 → 224 px** au
  premier essai, sans espace mort ;
- titres et réponses bornés (`28ch` / `46ch`), texte centré, marges internes
  fluides ;
- compteurs de progression bornés et maintenus dans le cadre ;
- barre d'actions à trois boutons de hauteur égale, en colonne sur mobile.

## Vérifications

| Contrôle | Résultat |
| --- | --- |
| Faces dans la même cellule | **oui** (même `top`, même hauteur 224 px) |
| Recto affiché, verso masqué | `front: visible` / `back: hidden` |
| Après clic | `front: hidden` / `back: visible`, rotation `matrix3d(-1…)` |
| Compteurs dans le cadre (1440 px) | **oui** |
| Débordement horizontal | **aucun** (desktop et mobile) |
| Contraste AA clair + sombre | **aucune alerte** |

Un défaut de contraste au passage : l'indice « Clique ou appuie sur Entrée… »
était semi-transparent (**2,82:1**). Il reprend la couleur du texte, distingué
par la taille et la graisse.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=100`, cache service worker
`cours-gedion-v100`. Aperçus : `apercus/flashcards-recto.jpg`,
`flashcards-verso.jpg`, `flashcards-mobile.jpg`.
