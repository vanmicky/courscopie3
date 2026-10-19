# V108 — Les messages de confirmation n'occupent plus l'écran

## Le défaut

Le bandeau « Opération réussie · Modifications enregistrées. » s'affichait sur
**toute la hauteur de l'écran**.

Mesure avant correction :

| Propriété | Valeur |
| --- | --- |
| `position` | `fixed` |
| `top` | `16px` |
| `bottom` | `16px` |
| hauteur calculée | **812 px** pour un écran de 844 px |

**Cause :** deux couches CSS fixaient des bords différents. La couche
historique imposait `top: 1rem`, la couche de refonte ajoutait
`bottom: 1rem` **sans annuler `top`**. Un élément fixe qui a à la fois `top` et
`bottom` s'étire entre les deux : d'où le panneau plein écran.

## La correction

Le message est ancré sur **un seul coin**, en bas à droite :

| Propriété | Après |
| --- | --- |
| `top` | `auto` |
| `bottom` / `right` | `24px` |
| hauteur | **74 px** (automatique) |
| largeur | 416 px maximum, bornée à l'écran |

- couleurs adaptées au thème de l'administration, filet d'accent à gauche
  (vert = succès, rouge = erreur) ;
- bouton de fermeture carré, lisible ;
- sur téléphone : le message occupe la largeur disponible, avec des marges ;
- **disparition automatique après 6 secondes** (nouveau script `main.js`), en
  plus du bouton de fermeture.

## Vérifications

- hauteur mesurée : **812 px → 74 px** ;
- position : 24 px du bas et de la droite, sur un écran de 1440 × 844 ;
- contraste AA en clair et en sombre : aucune alerte ;
- `node --check` sans erreur.

## Mise à niveau

Aucune migration SQL. CSS `?v=108`, cache service worker `cours-gedion-v108`.
Aperçu : `apercus/admin-message-succes.jpg`.
