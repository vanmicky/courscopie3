# V57 - Chapitres complets

- Ajout de `/admin/lecons.php` pour créer des blocs de cours par chapitre.
- Blocs typés : cours, définition, méthode, exemple, activité, bilan, à retenir.
- Chaque bloc peut être ordonné, publié ou gardé en brouillon.
- La page publique `chapitre.php` affiche les blocs dynamiques structurés.
- Les fichiers associés à un chapitre apparaissent maintenant dans `chapitre.php`.
- La médiathèque peut être ouverte avec un chapitre préselectionné.
- Raccourcis ajoutés depuis `/admin/chapitres.php` et le tableau de bord.
- Ajout du champ `kind` à la table `lessons`.
- Ajout de `database/migrate_v56_to_v57.sql`.
- Cache offline mis à jour en `cours-gedion-v57`.
