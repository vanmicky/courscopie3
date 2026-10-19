# V106 — Refonte UX de l'éditeur de chapitre

Demande : refaire la partie administration, jugée « pas du tout intuitive ».

## Le fil conducteur

Pour un enseignant, la question la plus importante est **« est-ce visible par
mes élèves ? »**. L'interface la traitait en second plan. La refonte la remet
au centre. L'audit complet est dans `AUDIT-ADMIN.md`.

## Ce qui change

### 1. Publication du chapitre en un clic
L'en-tête de l'éditeur affiche désormais :

- le **statut** du chapitre (« Publié » vert / « Brouillon » ambre) ;
- un bouton **« Publier le chapitre » / « Passer en brouillon »** ;
- un lien **« Voir sur le site ↗ »** quand il est publié.

Un clic suffit, depuis n'importe quel onglet (nouveau handler
`action=chapter_toggle`).

### 2. Publication évidente sur chaque contenu
Le bouton « Publier / Masquer » de chaque ligne devient un **vrai bouton**
distinct : vert pour « Publier », neutre pour « Masquer ». Le statut est une
pastille colorée et en majuscules.

### 3. Ajouter ou modifier ? sans ambiguïté
Le formulaire porte un **badge coloré** : « AJOUTER » (vert) ou « MODIFIER »
(bleu), suivi du nom de l'objet modifié.

## Mesures

| Contrôle | Résultat |
| --- | --- |
| Bascule du chapitre | 1 → 0 → 1, HTTP 302, état restauré |
| Boutons « Publier/Masquer » présents | oui (2 séries) |
| Badge « Ajouter » présent | oui |
| Tests | **49/49 au vert** |
| Contraste AA clair + sombre | aucune alerte |
| `php -l` / `node --check` | sans erreur |

## Mise à niveau

Aucune migration SQL. CSS `?v=106`, cache service worker `cours-gedion-v106`.
Aperçus : `apercus/admin-chapitre-publication.jpg`,
`admin-chapitre-informations.jpg`.
