# V97 — Direction artistique de l'administration

Refonte de l'interface d'administration en tant que designer UI/UX et directeur
artistique. **Périmètre : l'administration uniquement**, le site public garde sa
direction.

## Ambiance choisie

> **« Atelier tech et épuré »**

Neutres froids, surfaces blanches, une seule couleur d'accent, aucune
décoration. L'interface s'efface devant les données.

## 1. Hiérarchie de l'information

- **En-tête de page de 56 px** au lieu du grand bandeau : le badge « Admin » est
  supprimé, le titre passe de 2 rem à **20 px**, un filet de 1 px sépare
  l'en-tête du contenu. L'œil descend directement aux données.
- **Bandeau de chiffres clés** : dix cartes flottantes deviennent **une seule
  surface** divisée par des filets. Chiffre en 24 px tabulaire au-dessus,
  libellé en petites majuscules dessous. Tout se lit d'un coup d'œil.
- **Loi de proximité** appliquée par une échelle stricte : 8 px (libellé →
  champ), 12 px (entre champs), 24 px (entre groupes), 32 px (entre sections).
- **Un seul bouton primaire par écran** ; les actions secondaires passent en
  bouton bordé.

## 2. Simplicité

Supprimés : dégradés, halos, ombres colorées, badge d'en-tête, cartes
flottantes pour les chiffres, `stat-hint` répété sur chaque tuile.
L'accent `#2563eb` ne sert plus qu'aux liens, états actifs et focus — moins de
5 % de surface colorée par écran.

## 3. Couleurs

| Rôle | Valeur |
| --- | --- |
| Fond | `#f6f7f9` |
| Surface | `#ffffff` |
| Texte principal | `#14161a` |
| Texte secondaire | `#5b6270` |
| Texte discret | `#6b7280` |
| Prim\-aire (action) | `#18181b` |
| Accent | `#2563eb` |

Le **mode sombre** a son propre jeu : fond `#0b0c0f`, surface `#131519`,
primaire clair `#fafafa`, accent `#7aa2ff`, ombres supprimées.

## 4. Style visuel

| Élément | Rayon | Hauteur |
| --- | --- | --- |
| Bouton | 8 px | 44 px |
| Champ | 8 px | 40 px |
| Carte | 14 px | — |
| Badge | 999 px | 20 px |

- bordure de 1 px sur toutes les surfaces ;
- ombre à deux couches quasi imperceptible, **supprimée en mode sombre** ;
- survol : bordure plus foncée + fond très légèrement gris, **aucune
  élévation** ;
- icônes maison 24 × 24, contour `currentColor`, 16/20/22 px selon le contexte.

La spécification complète est dans **`DESIGN-ADMIN.md`** (couleurs, typographie,
espaces, formes, ombres, icônes, composants, interdits).

## 5. Trois défauts de contraste corrigés

L'audit automatique a relevé trois textes trop faibles dans l'admin :

| Élément | Avant | Après |
| --- | --- | --- |
| Compteur de section du menu | 2,84:1 | conforme |
| Libellés du bandeau de chiffres | 3,13:1 | conforme |
| Panneau « état du système » (fond bleu nuit en dur) | 3,75:1 en sombre | conforme |

## Vérifications

- jetons servis : `--a-bg=#f6f7f9`, `--a-surface=#ffffff`, `--a-text=#14161a`,
  `--a-accent=#2563eb`, `--a-border=#e4e7ec` ;
- en-tête mesuré : `flex`, sans fond, filet de 1 px `#e4e7ec`, titre 20,8 px,
  badge masqué ;
- bandeau de chiffres : 5 colonnes, gouttières de 1 px, valeur 24 px ;
- bouton primaire : `#18181b`, rayon 8 px, hauteur 44 px ;
- **contraste AA en clair et en sombre sur 8 pages d'administration** : aucune
  alerte ;
- 17 pages d'administration + 18 pages publiques en HTTP 200 ;
- `php -l` et `node --check` sans erreur.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=97`, cache service worker
`cours-gedion-v97`. Aperçus : `apercus/admin-tableau-de-bord.jpg`,
`admin-chapitres.jpg`, `admin-sombre.jpg`, `admin-mobile.jpg`.
