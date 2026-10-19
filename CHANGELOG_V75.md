# V75 — Site entièrement dynamique, performance et suivi des élèves

Trois chantiers en un : le site ne contient plus aucune page HTML statique, les
ressources sont allégées, et les résultats des QCM sont désormais conservés.

## 1. Tout est dynamique

Les **239 pages HTML statiques** ont été supprimées (`pages/**`, `index.html`,
`offline.html`) ainsi que les ressources devenues orphelines (7 scripts JS, 11 images
SVG). Le contenu qui n'existait QUE sous forme statique a été migré avant suppression.

### Fiches méthodes → base de données

Les 10 fiches de `pages/methodes/` (~4 200 mots, dont le guide « apprendre à apprendre »)
sont maintenant dans une table `methods` :

- `methodes.php` : la liste, avec recherche plein texte et filtre par domaine ;
- `methode.php?slug=…` : la fiche complète, plus les fiches du même domaine ;
- `/admin/methodes.php` : création, modification, publication et suppression.

Le contenu HTML d'origine est conservé à l'identique. Les **71 icônes Font Awesome**
qui venaient d'un CDN externe ont été remplacées par des emoji : plus aucune dépendance
à `cdnjs.cloudflare.com`.

Sans migration SQL, le site reste complet : les fiches s'affichent depuis
`includes/seed-methods.php`.

### Minuteur de classe → timer.php

`pages/outils/timer-classe.html` devient `timer.php`, avec l'en-tête, le thème
clair/sombre et le pied de page du site. Comportement identique (sonnerie, feu
tricolore, durées rapides), avec `aria-pressed` sur les feux.

### Mode hors-ligne → offline.php

`offline.html` devient `offline.php`, toujours déclaré dans le cache du service worker.
Il renvoie un statut 200 : le service worker refuse de mettre en cache une réponse 503,
ce qui aurait empêché l'installation hors-ligne.

### Ce qui a été nettoyé

- `pages/` (237 fichiers), `index.html`, `offline.html` ;
- `assets/js/` : `apprendre-a-apprendre.js`, `cahier.js`, `flashcards.js`,
  `interactive-quiz.js`, `methodes.js`, `quiz.js`, `travail-global.js` ;
- `assets/img/` : les SVG de niveaux et de chapitres, plus jamais référencés ;
- les 82 valeurs `legacy_path` qui pointaient vers des fichiers supprimés.

Le suivi personnel du travail à faire, qui n'existait que dans le cahier statique, a été
reporté dans `travail-a-faire.php` : une case « Fait » par devoir, mémorisée sur
l'appareil (`assets/js/work-done.js`), avec un compteur.

## 2. Performance et sobriété

- **Compression et cache HTTP** dans `.htaccess` : gzip sur HTML/CSS/JS/SVG/JSON,
  cache un mois pour les assets, un an pour les polices, `no-cache` sur les pages.
  En-têtes de sécurité ajoutés (`nosniff`, `Referrer-Policy`, `X-Frame-Options`,
  `Permissions-Policy`). Vérifié avec un vrai serveur Apache : `Content-Encoding: gzip`
  et `Content-Length` réduits.
- **Images responsives** : les 6 visuels de niveau passent de 2,45 Mo de JPEG à
  0,50 Mo de WebP (1200 px) ou 0,31 Mo en 800 px pour les téléphones, servis via
  `<picture>` avec le JPEG en repli.
- **Police Inter auto-hébergée** (variable, sous-ensembles latin et latin-ext, 132 Ko) :
  plus aucune requête vers Google Fonts. Gain de confidentialité, de vitesse, et
  fonctionnement hors-ligne.
- **KaTeX chargé uniquement là où il sert** : 13 pages n'affichent jamais de formule
  (accueil, pages de niveau, connexion, listes d'administration) et économisent ~270 Ko
  de JavaScript. Les pages qui en ont besoin (chapitres, exercices, QCM, flashcards,
  fiches méthodes) le chargent comme avant.
- **PWA réactivée** : le manifeste et le service worker n'étaient liés nulle part sur les
  pages dynamiques. Ils le sont maintenant, avec une icône 192/512 et une icône maskable
  générées, un favicon, et un cache renommé `cours-gedion-v75`.

## 3. Suivi des résultats des élèves

Jusqu'ici, le score d'un QCM s'affichait puis disparaissait. Deux tables enregistrent
désormais chaque tentative et chaque réponse.

- Au début d'un QCM, l'élève peut indiquer **son nom et sa classe** (facultatif,
  mémorisé dans un cookie pour ne pas le ressaisir). Rien n'est obligatoire.
- À la correction : le score s'affiche, un message confirme l'enregistrement, et la
  section **« À revoir »** liste les questions ratées avec un lien vers le chapitre
  correspondant et les fiches méthodes — c'est la remédiation qui manquait.
- `/admin/resultats.php` : nombre de tentatives, élèves identifiés, réussite moyenne,
  tableau par QCM (tentatives, élèves, moyenne, meilleur score) et **questions les plus
  ratées** avec une barre de progression, filtres par niveau, QCM, classe et date,
  et **export CSV** (séparateur `;`, compatible Excel français).

## 4. Cohérence et finitions

- **Page 404** commune (`404.php`), servie par `.htaccess` pour les URL de navigation,
  avec les liens utiles et le bon statut HTTP. Les fichiers absents (images, CSS)
  renvoient toujours un vrai 404 Apache.
- **`robots.txt` et `sitemap.xml`** générés par PHP : le domaine n'a pas à être connu à
  l'avance, et le plan du site (68 URL) suit automatiquement les contenus publiés.
- Le lien **Méthodes** de la navigation pointe vers la page dynamique, plus vers un
  fichier HTML.

## 5. Vérifications

- `php -l` sur tous les fichiers PHP : aucune erreur ;
- 27 URL publiques et 25 URL d'administration : HTTP 200, aucune erreur PHP visible ;
- audit de contraste automatique sur les pages nouvelles et modifiées : aucune alerte
  sous le seuil AA ;
- `.htaccess` testé sur un serveur **Apache 2.4 réel** (pas seulement en théorie) :
  compression, cache, en-têtes, robots/sitemap, 404, refus des `.sql`/`.md`/`.zip` ;
- saisie d'un QCM testée de bout en bout : 4 tentatives enregistrées, détail par
  question en base, statistiques et export CSV vérifiés.

## 6. Mise à niveau

1. Sauvegarder la base.
2. Remplacer les fichiers, **supprimer l'ancien dossier `pages/`** sur le serveur.
3. Importer `database/migrate_v74_to_v75.sql` (facultatif mais recommandé).
4. Vider le cache du navigateur : le CSS est servi avec `?v=75`.

Une sauvegarde complète du contenu statique supprimé reste disponible dans
`sauvegarde-statique-V74-*.zip` (protection par `.htaccess`) : elle peut être supprimée
une fois la migration confirmée.

Voir `CHANGELOG_V74.md` pour la refonte visuelle, `DESIGN_SYSTEM.md` pour les jetons et
composants, et `INSTALLATION_SHARED_HOSTING.md` pour la procédure de déploiement.
