# V56 - Structure dynamique

- Ajout de `/admin/niveaux.php` pour créer, modifier et supprimer les niveaux.
- Upload d'image pour chaque niveau dans `uploads/levels`.
- Couleur, ordre, résumé et publication/brouillon gérés pour les niveaux.
- Ajout de `/admin/matieres.php` pour gérer les disciplines.
- Upload d'image pour chaque matière dans `uploads/subjects`.
- Couleur, résumé, ordre et publication/brouillon gérés pour les matières.
- Menu public rendu dynamique : les niveaux viennent de MySQL.
- Tableau de bord admin enrichi avec niveaux et matières.
- Schema MySQL mis à jour pour les images et métadonnées de matières.
- Ajout de `database/migrate_v55_to_v56.sql`.
- Cache offline mis à jour en `cours-gedion-v56`.
