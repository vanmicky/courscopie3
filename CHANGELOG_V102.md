# V102 — Projection individuelle des outils de classe

Demande : chaque outil de classe doit pouvoir être **projeté seul**, en grand.

## Chaque outil a sa propre adresse

| Outil | URL de projection |
| --- | --- |
| Minuteur | `outils.php?projeter=minuteur` |
| Formation de groupes | `outils.php?projeter=groupes` |
| Tirage au sort | `outils.php?projeter=tirage` |
| Consignes | `outils.php?projeter=consignes` |
| Dés & chrono | `outils.php?projeter=animation` |

Chaque panneau porte un bouton **« Projeter cet outil »** qui ouvre l'adresse
dans un **nouvel onglet** : la page de contrôle reste ouverte d'un côté, la
projection de l'autre.

## Ce que fait le mode projection

- **un seul outil** est affiché : les quatre autres panneaux sont masqués
  (règle CSS, donc actif même sans JavaScript) ;
- **ni en-tête de site, ni héros, ni onglets, ni pied de page** ;
- **barre de sortie** discrète : nom de l'outil, bouton **Plein écran**,
  bouton **Quitter** ;
- **contenus agrandis** selon la taille de l'écran :

| Élément | Taille normale | En projection (1440 px) |
| --- | --- | --- |
| Chiffres du minuteur | 144 px | **187 px** |
| Texte des consignes | — | **52 px** |
| Résultat du tirage | — | **66 px** |

## Clavier

- **Plein écran** : bouton dédié (l'API plein écran exige un geste de
  l'utilisateur) ;
- **Échap** : quitte le plein écran ; une seconde fois, revient à la page des
  outils.

## Vérifications

Pour les cinq outils, mesure du DOM :

| Outil | Panneaux visibles | Barre | En-tête site | Héros | Onglets | Débordement |
| --- | --- | --- | --- | --- | --- | --- |
| minuteur | `[minuteur]` | oui | absent | masqué | masqués | non |
| groupes | `[groupes]` | oui | absent | masqué | masqués | non |
| tirage | `[tirage]` | oui | absent | masqué | masqués | non |
| consignes | `[consignes]` | oui | absent | masqué | masqués | non |
| animation | `[animation]` | oui | absent | masqué | masqués | non |

- page normale : **5 onglets** et **5 boutons « Projeter »** toujours présents ;
- contraste AA en clair et en sombre : aucune alerte ;
- `php -l` et `node --check` sans erreur.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=102`, cache service worker
`cours-gedion-v102`. Aperçus : `apercus/projection-minuteur.jpg`,
`projection-consignes.jpg`, `projection-tirage.jpg`, `outils-classes.jpg`.
