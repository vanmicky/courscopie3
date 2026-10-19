# V73 - Parcours visuel

Base : V71. Cette version reprend ses contenus et fonctionnalités.

- Accueil illustré, titres plus hiérarchisés et cartes de niveaux sur trois colonnes sur grand écran.
- Images de niveaux affichées en entier, sans recadrage forcé.
- Recherche identifiée par un libellé, insensible aux accents, compteur de résultats et état vide avec remise à zéro.
- Bandeaux de chapitre sombres accordés à la couleur du niveau, marges internes renforcées.
- Navigation mobile sur une ligne distincte, cible de 44 px et repère de niveau actif.
- Tableau de bord : gestion séparée en actions principales et secondaires.
- Conservation du CSS unique et des modes clair/sombre.

## Vérifications

Pages publiques et connexion admin contrôlées via HTTP avec une base de démonstration séparée de la configuration livrée.
La validation visuelle par capture n'a pas pu être effectuée : macOS refuse le lancement du navigateur de test (permission MachPortRendezvous).

## Installation

Sauvegarder la base et les fichiers. Remplacer les fichiers applicatifs en conservant `includes/config.php` et `uploads/`. Ne pas réimporter `schema.sql` ou `seed.sql` sur une installation existante. Aucune migration SQL depuis V71/V72. CSS versionné V73.
