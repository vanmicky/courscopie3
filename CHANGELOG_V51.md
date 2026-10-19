# V51 - Premier socle PHP/MySQL

- Ajout d'une architecture PHP compatible hebergement mutualise.
- Ajout de `index.php`, `niveau.php`, `chapitre.php` et `travail-a-faire.php`.
- Ajout d'un espace admin avec creation du premier compte, connexion, tableau de bord et gestion du travail a faire.
- Ajout du schema MySQL complet : niveaux, matieres, chapitres, cours, exercices, QCM, flashcards, fichiers, annonces et devoirs.
- Ajout de `assignments`, `assignment_files` et `assignment_links` pour integrer le travail a faire proprement dans la base.
- Ajout d'un import de depart avec les niveaux et chapitres existants.
- Conservation de KaTeX pour afficher LaTeX dans les contenus dynamiques.
- Conservation du CSS unique `dist/output.css`.
- Cache offline mis a jour en `cours-gedion-v51` et limite aux assets pour eviter de figer les pages PHP.
