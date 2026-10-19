# V94 — Fiche méthode : colonne de lecture alignée

Trois défauts signalés : les textes des onglets débordent, et les paragraphes
laissent un grand vide à droite.

## 1. Le vide à droite

**Mesure avant** (1440 px) :

| Élément | Largeur |
| --- | --- |
| Héros | 1184 px pour **626 px de texte** |
| Panneau de lecture | 896 px |
| Section | 819 px pour **614 px de texte** → 205 px de vide |

Le texte était donc collé à gauche, avec un vide important à droite, et le héros
ne partageait pas l'axe du contenu.

**Ce qui a changé :**

- le héros et le panneau de lecture sont bornés à la **même largeur** (46 rem,
  soit ≈ 69 caractères) ;
- le héros est **décalé de la largeur de l'index** : il commence exactement à
  l'aplomb de la colonne de lecture ;
- les deux blocs ont la **même marge interne**, et les plafonds de largeur des
  paragraphes (62 ch, 70 ch…) ont été retirés : le texte remplit la mesure au
  lieu de créer un second vide à l'intérieur du bloc.

**Résultat mesuré :** héros et panneau à 416 px, texte du héros et texte du
contenu à **455 px — écart de 0 px**. Plus aucun débordement horizontal.

Sur téléphone, le décalage ne s'applique pas : le héros occupe toute la largeur
(363 px sur 390).

## 2. Le texte qui déborde dans les onglets

La barre d'onglets n'existe plus depuis la V93 (remplacée par l'index latéral).
Ce que tu voyais venait d'un **script resté en cache** : l'ancien
`methode-sections.js` ré-appliquait le mode onglets sur la nouvelle mise en page.

- les scripts de la fiche sont désormais **versionnés** (`?v=94`), ce qui force
  le navigateur à charger la bonne version ;
- le cache du service worker est renouvelé (`cours-gedion-v94`).

## Vérifications

- alignement mesuré : **0 px d'écart** entre le texte du héros et celui du
  contenu ; aucun débordement horizontal (desktop et mobile) ;
- contraste AA en clair et en sombre : aucune alerte ;
- 18 pages publiques en HTTP 200 sans erreur PHP ;
- `php -l` et `node --check` sans erreur.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=94`, scripts versionnés, cache service
worker `cours-gedion-v94`. Aperçus : `apercus/fiche-methode-index.jpg`,
`fiche-methode-lecture.jpg`, `fiche-methode-mobile.jpg`.
