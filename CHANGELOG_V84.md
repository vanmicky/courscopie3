# V84 — Thème monochrome : le site en noir et blanc

Demande : tout le site en noir et blanc, en nuances de gris, en gardant
**quelques couleurs pour les matières**.

## Principe

Toute la chromatique décorative passe en gris. La seule couleur qui subsiste
vient des **accents de matière** : `--accent` (niveau, chapitre) et
`--method-accent` (domaine d'une fiche méthode), définis par les pages à partir
de la base. Autrement dit : le contenant est gris, le repère de matière reste
coloré.

## Ce qui devient gris

| Élément | Avant | Après |
| --- | --- | --- |
| Fond de page | crème `#f6f2e8` | `#f4f4f5` |
| Surfaces | ivoire `#fffdf7` | blanc / `#f7f7f8` |
| Encre | `#1d1a24` | `#18181b` |
| Marque (boutons, logo, focus) | indigo `#4f46e5` | graphite `#27272a` |
| Dégradé de marque | indigo → violet | graphite → noir |
| Halos de fond | indigo + ambre | gris neutres |
| Motif pointillé | brun chaud | noir à 10 % |
| Ombres | teintées brun | noir neutre |
| Mode sombre | bleu nuit `#0a1018` | `#0b0b0c` |

## Ce qui garde sa couleur

- **Les accents de matière** : `--accent` et `--method-accent`. Sur la page
  « Méthodes », les étiquettes restent bleu, vert, orange… selon le domaine.
- **Le feu tricolore** de niveau sonore (rouge / orange / vert) : c'est un signal
  de classe, pas une décoration. Choix assumé.
- **Les couleurs d'état** (réussite, attention, erreur, information) : conservées
  pour le sens, mais **fortement désaturées** pour se fondre dans le gris.
- **La coloration syntaxique** des blocs de code : conservée mais désaturée.

## Détail technique

Deux points avaient un reste de teinte bleue :

- les variantes d'accent étaient mélangées à un bleu très sombre (`#0b1220`,
  `#0d1526`) : les aplats d'accent tiraient vers le bleu. Les mêmes mélanges sont
  refaits avec des bases graphites (`#09090b`, `#131316`), pour que seule la
  teinte de la matière reste perceptible ;
- le logo et le bouton du minuteur en mode sombre utilisaient un dégradé indigo
  codé en dur.

Tout est dans **une seule couche**, `src/ds-9-mono.css`, chargée en dernier :
aucune couche précédente n'est modifiée, et le thème se retire en supprimant son
import de `tools/build-css.sh`.

## Vérifications

- **Surfaces mesurées grises** sur l'accueil, les méthodes, la fiche méthode et
  les outils : fond `rgb(244,244,245)`, cartes blanches, en-tête `rgba(255,255,255,.82)`,
  pied de page blanc, logo et boutons en dégradé graphite `rgb(63,63,70)` → `rgb(24,24,27)` ;
- **accents de matière intacts** : `#2563eb`, `#16a34a`, `#f97316` toujours
  présents là où ils doivent l'être ;
- **mode sombre neutre** : fond `rgb(11,11,12)` (écart entre canaux : 1),
  pied de page `rgb(21,21,23)` ;
- **contraste AA en clair et en sombre** sur 14 pages représentatives : aucune
  alerte ;
- 18 pages publiques en HTTP 200 sans erreur PHP, `php -l` et `node --check`
  sans erreur ;
- `theme-color` du navigateur et `manifest.webmanifest` alignés sur les gris.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=84`, cache service worker
`cours-gedion-v84`. Aperçus dans `apercus/`.
