# V122 — Formulaire de question QCM réorganisé

Signalé : « trop, c'est trop réarrangé » devant le formulaire de question.

## Ce qui n'allait pas

Les champs étaient posés dans une **grille à plusieurs colonnes** (`.work-filter-grid`).

Conséquences mesurées :

| Élément | Problème |
| --- | --- |
| Bloc image | écrasé dans une colonne de ~220 px, le bouton « Choisir un fichier » **débordait** |
| Six zones de réponse | alignées sur une seule rangée, étirées à la hauteur maximale |
| « Bonne réponse » et « Ordre » | coincés au milieu de la grille |

## La nouvelle structure

Une colonne, du haut vers le bas, dans l'ordre où l'on rédige :

1. **Question** (pleine largeur) ;
2. **Réponses** : A et B côte à côte (les obligatoires) ;
3. **Réponses supplémentaires (C à F)** dans un bloc **replié** par défaut — le
   formulaire ne montre que l'essentiel ;
4. **Bonne réponse** + **Ordre** sur une ligne ;
5. **Image de la question** (pleine largeur) ;
6. **Explication** (pleine largeur).

## Mesures après correction

| Élément | Avant | Après |
| --- | --- | --- |
| Largeur du bloc image | ~220 px (débordant) | **1070 px** |
| Champ fichier dans son cadre | non | **oui** |
| Largeur du champ Question | étroite | **1068 px** |
| Débordement horizontal | oui | **aucun** |

Le formulaire de carte (flashcards) a été vérifié aussi : **2 blocs image de
1043 px**, champs fichier dans leur cadre, aucun débordement.

## Vérifications

- contraste AA en clair et en sombre : aucune alerte ;
- `php -l` sans erreur ;
- blocs image : 2 sur flashcards, 1 sur QCM, tous pleine largeur.

## Mise à niveau

CSS `?v=122`, cache service worker `cours-gedion-v122`.
Aperçus : `apercus/admin-qcm-editeur.jpg`, `admin-flashcards-editeur.jpg`.
