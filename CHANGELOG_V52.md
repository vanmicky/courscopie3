# V52 - Admin chapitres et cours

- Ajout d'une vraie page `/admin/chapitres.php`.
- Possibilite d'ajouter, modifier, publier, masquer ou supprimer un chapitre.
- Ajout de l'edition du contenu de cours dynamique avec support LaTeX via KaTeX.
- La page publique `chapitre.php` affiche maintenant le contenu MySQL quand il existe.
- Conservation du lien vers l'ancienne page statique comme secours pendant la migration.
- Tableau de bord admin mis a jour avec un acces direct aux chapitres.
- Ajout de `database/migrate_v51_to_v52.sql` pour mettre a jour une installation V51.
- Cache offline mis a jour en `cours-gedion-v52`.
