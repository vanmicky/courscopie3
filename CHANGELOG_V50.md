# V50 - CSS centralise

- Suppression des gros blocs CSS embarques dans les pages HTML.
- Conservation du style commun dans `dist/output.css` pour une lecture beaucoup plus simple du HTML.
- Ajout des styles de la page Timer dans le fichier CSS central.
- Toutes les pages gardent un lien vers le fichier CSS unique du site.
- Retrait du dossier `src` et du fichier `package.json` dans cette version prete a deployer : GitHub Pages utilise directement les HTML, JS, assets et `dist/output.css`.
- Cache offline mis a jour en `cours-gedion-v50`.
