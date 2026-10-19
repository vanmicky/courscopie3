# V96 — Administration simplifiée

Demande : l'administration était « une usine à gaz ». Simplifier, rendre
l'utilisation plus facile et intuitive.

## Le diagnostic

- **16 entrées de menu** réparties en 4 blocs : il fallait tout lire à chaque
  fois pour retrouver une page ;
- **13 boutons** empilés sur le tableau de bord, qui doublonnaient le menu ;
- la recherche globale occupait une place de premier niveau dans le menu alors
  qu'elle est un réflexe, pas une destination.

## 1. Un menu à cinq sections dépliables

| Section | Contenu |
| --- | --- |
| **Tableau de bord** | vue d'ensemble |
| **Cours** | Niveaux · Matières · Chapitres · Vidéos et savoirs · Fiches méthodes |
| **Activités** | Exercices · QCM · Flashcards · Travail à faire |
| **Résultats** | Résultats des QCM · Pilotage pédagogique |
| **Réglages** | Médiathèque · Sauvegardes · État du système |

- **seule la section où l'on se trouve est dépliée** : on voit 5 entrées au
  lieu de 16, et le contexte reste lisible ;
- chaque section porte une **icône** ;
- un **compteur** indique le nombre de pages d'une section repliée ;
- un **champ de recherche** est placé en haut du menu.

Vérifié page par page : « Cours » s'ouvre sur `chapitres.php`, « Activités » sur
`qcm.php`, « Résultats » sur `resultats.php`, « Réglages » sur `sauvegarde.php`,
et rien ne s'ouvre sur le tableau de bord.

## 2. Un tableau de bord orienté action

Les 13 boutons et la recherche en double sont remplacés par **quatre actions**
qui correspondent à ce que fait vraiment un professeur :

- **Chapitres** — ajouter ou modifier un chapitre ;
- **QCM** — créer un questionnaire ;
- **Documents** — envoyer un PDF ou une image ;
- **Résultats** — voir les scores des élèves.

Le bloc de pilotage (chapitres complets / à vérifier) et les statistiques de
contenu sont conservés : ce sont des informations, pas des boutons.

## 3. Bénéfices

- **16 → 5** entrées de menu, **13 → 4** boutons sur le tableau de bord ;
- le chemin « je veux ajouter quelque chose » demande deux clics ;
- le menu reste utilisable sur téléphone (bouton « Menu admin »).

## Vérifications

- **17 pages d'administration en HTTP 200**, aucune erreur PHP ;
- ouverture de section vérifiée sur 5 pages (voir tableau ci-dessus) ;
- contraste AA en clair et en sombre : aucune alerte ;
- `php -l` et `node --check` sans erreur.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=96`, cache service worker
`cours-gedion-v96`. Aperçus : `apercus/admin-tableau-de-bord.jpg`,
`admin-chapitres.jpg`, `admin-mobile.jpg`.
