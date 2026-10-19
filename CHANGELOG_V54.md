# V54 - QCM, flashcards et exercices dynamiques

- Ajout des pages publiques dynamiques `qcm.php`, `flashcards.php` et `exercices.php`.
- QCM avec correction automatique, score et bonne réponse affichée en vert.
- Flashcards dynamiques par deck, avec cartes dépliables et LaTeX.
- Exercices dynamiques avec énoncé, correction dépliable, difficulté et lien vers le chapitre.
- Ajout de `/admin/qcm.php` pour gérer QCM, questions, réponses A/B/C/D et bonne réponse.
- Ajout de `/admin/flashcards.php` pour gérer decks et cartes.
- Ajout de `/admin/exercices.php` pour gérer exercices et corrections.
- Tableau de bord admin enrichi avec compteurs et accès directs.
- Navigation publique enrichie avec Exercices, QCM et Flashcards.
- Styles centralisés dans `dist/output.css`.
- Ajout de `database/migrate_v53_to_v54.sql` avec un petit contenu de test.
- Cache offline mis à jour en `cours-gedion-v54`.
