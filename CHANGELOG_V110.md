# V110 — Cartes mémoire : révision plus efficace

Demande : « améliorer les flashcards ».

## Ce qui manquait

- la progression n'était donnée que par du **texte** (« Carte 3 / 8 ») ;
- les raccourcis clavier se limitaient aux flèches, et seulement quand un bouton
  avait le focus ;
- aucun moyen de **mélanger** les cartes ;
- le bilan final affichait un score mais ne proposait que « Recommencer » :
  impossible de retravailler uniquement les cartes ratées.

## Ce qui a été ajouté

| Ajout | Détail |
| --- | --- |
| **Barre de progression** | visible sous les compteurs, avec `role="progressbar"` et `aria-valuenow` |
| **Raccourcis complets** | `Entrée` / `Espace` retourne · `←` `→` navigue · `1` pas encore · `2` réussi |
| **Raccourcis actifs partout** | écoute au niveau du document, sans devoir cliquer dans la carte ; ignorée dans les champs de saisie et les raccourcis système |
| **Bouton Mélanger** | mélange de Fisher-Yates, relance la série dans un ordre aléatoire |
| **Légende discrète** | « Entrée : retourner · ← → : naviguer · 1 / 2 : évaluer » |
| **Touches visibles** | les boutons affichent `1` et `2` |
| **Bilan enrichi** | pourcentage, message adapté, bouton **« Revoir les cartes à revoir »** qui relance uniquement les cartes ratées |

## Vérifications (mesurées dans le navigateur)

| Étape | Résultat |
| --- | --- |
| Départ | « Carte 1 / 8 », barre à 0 % |
| `Entrée` | carte retournée, verso visible |
| `2` | progression **13 %** (1 carte sur 8) |
| `→` | « Carte 2 / 8 », retour au recto |
| Fin de série (3 réussies / 8) | bilan « 3 réussites sur 8 », **38 %** |
| « Revoir les cartes à revoir » | relance **5 cartes** (les ratées) |
| Contraste AA clair + sombre | aucune alerte |
| `node --check` | sans erreur |

## Mise à niveau

Aucune migration SQL. CSS `?v=110`, cache service worker `cours-gedion-v110`.
Aperçus : `apercus/flashcards-recto.jpg`, `flashcards-vers.jpg`,
`flashcards-bilan.jpg`.
