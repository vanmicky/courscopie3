# V120 — Images dans les QCM et les cartes, réponses variables

Quatre demandes : insérer des images sans passer par la médiathèque, les
redimensionner, ne plus imposer 4 réponses aux QCM, et corriger la liste des
cartes dans l'administration.

## 1. Images envoyées directement dans le formulaire

Chaque question de QCM et chaque carte porte désormais un bloc **« Image
(facultative) »** :

- envoi direct d'un fichier (PNG, JPG, WEBP, GIF — **5 Mo maximum**) ;
- **largeur réglable de 10 à 100 %** : l'image s'adapte à l'écran, elle reste
  nette ;
- aperçu de l'image actuelle et case **« Retirer cette image »** ;
- aucun passage par la médiathèque.

Côté base, deux colonnes sont ajoutées automatiquement si besoin :
`image_path` et `image_width`.

| Endroit | Affichage |
| --- | --- |
| Administration, liste des cartes | vignette à la largeur choisie |
| Page publique « flashcards » | image sur le recto, largeur choisie |
| Page publique « QCM » | image au-dessus de l'énoncé |

Le dossier `assets/uploads/` est protégé : aucun script ne peut s'y exécuter et
il n'est pas listable.

## 2. Nombre de réponses variable (QCM)

**De 2 à 6 réponses**, seules les réponses remplies sont enregistrées.

| Règle | Comportement |
| --- | --- |
| A et B | obligatoires |
| C, D, E, F | facultatives |
| Moins de 2 réponses | refusé : « Indiquez au moins deux réponses » |
| Bonne réponse non saisie | refusée : « doit être l’une des réponses saisies » |

Le menu « Bonne réponse » propose A à F.

## 3. Liste des cartes : alignée et sans débordement

- liste **alignée à gauche** (plus de contenu centré) ;
- retour à la ligne forcé (`overflow-wrap: anywhere`) ;
- les formules mathématiques sont **contenues dans la carte** (défilement
  horizontal au lieu de dépasser) ;
- encadrés et réponses alignés à gauche, boutons d'action à gauche.

## 4. Tests

La suite passe de 60 à **66 vérifications**, avec :

```
✓ QCM — deux réponses suffisent
✓ QCM — six réponses acceptées
✓ QCM — une seule réponse refusée
✓ QCM — bonne réponse hors liste refusée
✓ QCM — colonnes d'image présentes
✓ Cartes — colonnes d'image présentes
```

## Vérifications manuelles

| Contrôle | Résultat |
| --- | --- |
| QCM à 2 réponses + image | enregistré, 2 réponses en base, image `35 %` |
| Carte avec image | enregistrée, `30 %` |
| Image servie | HTTP 200, `image/png` |
| Affichage carte 5/5 | conteneur visible, `src` présent, largeur `30%` |
| Cartes 1 à 4 | conteneur masqué (pas d'image) |
| Contraste AA clair + sombre | aucune alerte |

## Mise à niveau

Les colonnes d'image sont créées **automatiquement** à la première utilisation ;
elles sont aussi ajoutées à `database/schema.sql` pour une installation neuve.

CSS `?v=120`, JS `?v=120`, cache service worker `cours-gedion-v120`.
Aperçus : `apercus/admin-cartes-images.jpg`, `admin-qcm-reponses.jpg`.
