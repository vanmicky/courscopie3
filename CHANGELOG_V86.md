# V86 — Retour aux couleurs

Le thème monochrome est abandonné : le site retrouve ses couleurs.

## Ce qui revient

| Élément | Noir et blanc (V84/V85) | Couleur (V86) |
| --- | --- | --- |
| Fond de page | `#f4f4f5` | crème `#f6f2e8` |
| Surfaces | blanc / `#f7f7f8` | ivoire `#fffdf7` |
| Marque | graphite `#27272a` | indigo `#4f46e5` |
| Dégradé de marque | graphite → noir | indigo → violet |
| Halos de fond | gris | indigo + ambre |
| Motif | points noirs | points brun chaud |
| Ombres | gris neutre | teintées chaud |
| Mode sombre | `#0b0b0c` | `#100f16` |

Les **couleurs de matières** n'ont jamais bougé : elles restent sur les niveaux,
chapitres et domaines.

## Comment le retour a été fait

Le thème monochrome vivait dans **une seule couche**, `src/ds-9-mono.css`,
chargée en dernier. Il a suffi de la retirer de la boucle de compilation :
aucune autre couche n'a été touchée, donc tout le travail de structure
(onglets des chapitres, fiche méthode à onglets, outils de classe, polices
Krub/Inter, fond de lecture) est intact.

Le fichier est **conservé** : pour rebasculer en noir et blanc, il suffit de
remplacer `ds-9-fixes.css` par `ds-9-mono.css` dans `tools/build-css.sh`.

## Ce qui est conservé du passage en monochrome

- **Les polices** : Inter pour le texte, Krub pour les titres.
- **Un correctif réel** : l'info-bulle des flashcards était en
  `color-mix(in srgb, currentColor 72%, transparent)`, soit un texte
  semi-transparent à **2,9:1** sur fond clair — sous le seuil AA. Elle passe sur
  l'encre d'accent, opaque (≈ 7,5:1). Ce correctif est déplacé dans une petite
  couche dédiée, `src/ds-9-fixes.css`, prévue pour les corrections transverses
  qui ne dépendent pas d'un thème.
- Le reste (échelle de gris, dégradés de surface, désaturation des couleurs
  d'état) était propre au thème et disparaît avec lui.

## Vérifications

- palette colorée servie : fond `rgb(246,242,232)`, surfaces ivoire, logo et
  boutons en dégradé indigo `rgb(99,102,241)` → `rgb(79,70,229)`, accents de
  matière intacts (`#2563eb`, `#16a34a`, `#f97316`) ;
- titres toujours en **Krub** ;
- **contraste AA en clair et en sombre sur 14 pages** : aucune alerte, hors deux
  faux positifs documentés — le logo et les boutons principaux (texte blanc sur
  dégradé, que l'outil approxime par la couleur de base), et le titre dégradé de
  l'accueil (42 px, seuil AA de 3:1 pour les grands textes : le stop le plus
  clair du dégradé mesure 4,0:1) ;
- 18 pages publiques en HTTP 200 sans erreur PHP, `php -l` et `node --check`
  sans erreur ;
- `theme-color` du navigateur et `manifest.webmanifest` revenus aux teintes
  colorées.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=86`, cache service worker
`cours-gedion-v86`. Aperçus dans `apercus/`.
