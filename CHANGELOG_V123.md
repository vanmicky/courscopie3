# V123 — Cartes redessinées, blocage supprimé

Trois signalements vérifiés un par un.

## 1. « Impossible de modifier ou supprimer »

**Cause trouvée** : le script `chapter-workspace.js` posait une question
« quitter le site ? » dès qu'un champ avait été modifié, **y compris** quand on
cliquait sur « Modifier », « Supprimer » ou « Publier ». Le navigateur bloquait
l'action, qui semblait donc ne rien faire.

**Correction** : l'avertissement est levé dès qu'un formulaire est envoyé, quel
qu'il soit (`submit` au niveau du document). Les boutons agissent normalement.

Côté serveur, les trois actions ont été vérifiées en HTTP :

| Action | Résultat |
| --- | --- |
| Modifier une carte | formulaire pré-rempli (`card_id` = 64, recto et verso) |
| Supprimer une carte | 2 → 1 carte en base |
| Créer / enregistrer | carte ajoutée, formules conservées |

## 2. « 7 cartes alors qu'il y en a 2 »

Vérifié sur le site public : la série **« La masse volumique » contient bien
7 cartes** (`3e PC · 7 cartes`). Ce ne sont pas des cartes fantômes : elles ont
été créées et se sont accumulées, faute de pouvoir supprimer (voir le point 1).

La liste redessinée rend la suppression évidente : un bouton **Supprimer** rouge
sur chaque carte.

## 3. Formules LaTeX

Vérifié dans le navigateur sur la page d'administration : **6 formules rendues,
0 erreur**, aucun `\(` affiché en clair. Le rendu fonctionne (`math.js` traite
tout `document.body`). Le formulaire, lui, montre volontairement le code LaTeX
brut dans les zones de saisie — c'est normal.

## 4. Cartes redessinées

| Avant | Après |
| --- | --- |
| « Ordre N » en gris | **pastille numérotée** |
| texte, puis « Verso » replié | texte en évidence, verso replié sous le contenu |
| deux boutons empilés en pleine largeur | **actions alignées à droite**, dont un bouton **Supprimer** rouge |
| hauteur 332 px | **180 px** |

Nouvelle structure `.admin-card` : numéro, contenu, verso replié, vignettes
d'image éventuelles, actions à droite (pleine largeur sur mobile).

## Vérifications

- cartes mesurées : **180 px**, aucune débordement horizontal ;
- formules : 1 à 6 rendues selon les cartes, 0 erreur ;
- contraste AA clair + sombre : aucune alerte ;
- suite de tests : **66 vérifications** ;
- audit complet : 0 problème.

## Mise à niveau

CSS `?v=123`, JS `chapter-workspace.js?v=123`, cache service worker
`cours-gedion-v123`. Aperçu : `apercus/admin-cartes-liste.jpg`.
