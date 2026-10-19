# V58 - Correctif CSS hebergement mutualise

- Correction du probleme visible sur hebergement mutualise : les pages PHP pouvaient chercher le CSS avec un chemin trop absolu.
- `dist/output.css` est maintenant charge avec un chemin relatif adapte a la page courante.
- Les pages publiques chargent les assets depuis `./`.
- Les pages admin chargent les assets depuis `../`.
- Les images de niveaux/matieres, fichiers envoyes et scripts suivent la meme logique.
- `base_url` peut rester vide dans `includes/config.php`, ce qui est le reglage recommande.
- Ajout de `/admin/sauvegarde.php` pour exporter et importer les contenus dynamiques en JSON.
- Export separe du dossier `uploads` en ZIP quand l'extension PHP ZipArchive est disponible.
- Les comptes admin ne sont pas inclus dans l'export de contenus.
- Cache offline mis a jour en `cours-gedion-v58`.

Controle rapide apres installation : ouvrir `dist/output.css` dans le navigateur depuis le domaine. Si la page CSS affiche une erreur 404, le dossier `dist` n'est pas au bon endroit.
