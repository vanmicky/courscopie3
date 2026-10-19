# Installation V75 sur hebergement mutualise

## Mise a jour depuis V74 (ou V73)

Le site est desormais **entierement dynamique** : le dossier `pages/` et les fichiers
`index.html` / `offline.html` ont ete supprimes. Les fiches methodes et le minuteur de
classe sont devenus des pages PHP adossees a la base.

1. **Sauvegarder la base** (et les fichiers) avant toute chose.
2. Remplacer les fichiers applicatifs en conservant `includes/config.php` et `uploads/`.
   **Supprimer l'ancien dossier `pages/`** sur le serveur : il n'est plus utilise.
3. Importer `database/migrate_v74_to_v75.sql` dans phpMyAdmin. Cette migration cree
   deux ensembles de tables et reinjecte les 10 fiches methodes :
   - `methods` : les fiches methodes, desormais modifiables dans `/admin/methodes.php` ;
   - `qcm_attempts` et `qcm_attempt_answers` : les resultats des QCM pour le suivi.
   Sans cette migration, le site fonctionne : les fiches methodes s'affichent depuis
   `includes/seed-methods.php` et la page `/admin/resultats.php` indique quoi importer.
4. Verifier que `.htaccess` est bien envoye (il active la compression, le cache et la
   page 404). Il fonctionne aussi bien a la racine du domaine que dans un sous-dossier.
5. Se connecter a `/admin/login.php`. La connexion demande maintenant un jeton CSRF :
   elle fonctionne normalement depuis un navigateur.

Nouveautes a connaitre :

- `/admin/resultats.php` : taux de reussite par QCM et par question, filtres par niveau,
  classe et date, export CSV. Les eleves peuvent laisser leur nom et leur classe
  (facultatif) au debut d'un QCM ; c'est memorise dans un cookie pour ne pas le ressaisir.
- `/admin/methodes.php` : creation et modification des fiches methodes. Le champ
  « contenu » accepte du HTML simple ; les balises actives sont retirees a l'enregistrement.
- `/methodes.php` et `/methode.php` remplacent `pages/methodes/`.
- `/timer.php` remplace `pages/outils/timer-classe.html`.
- `robots.txt` et `sitemap.xml` sont generes par PHP (`robots.php`, `sitemap.php`).

### Modifier le CSS

`dist/output.css` est genere : ne pas l'editer directement.

```bash
tools/build-css.sh
```

### Tester en local

Copier `includes/config.local.example.php` en `includes/config.local.php` et adapter les
identifiants. Ce fichier n'est pas versionne et n'a aucun effet sur l'hebergement.

## Mise a jour depuis V71 ou V72

Sauvegarder la base et les fichiers. Remplacer les fichiers applicatifs en conservant `includes/config.php` et `uploads/`. Aucune migration SQL. Ne pas reimporter schema.sql ou seed.sql. Le CSS est versionne avec `?v=73`.

1. Envoyer tout le contenu du dossier dans `public_html`, `www` ou `htdocs`.
2. Creer une base MySQL/MariaDB depuis le panneau de l'hebergeur.
3. Importer `database/schema.sql` avec phpMyAdmin.
4. Importer ensuite `database/seed.sql` pour charger les niveaux et chapitres de depart.
5. Ouvrir `includes/config.php` et renseigner `db_host`, `db_name`, `db_user`, `db_pass`. Laisser `base_url` vide sauf cas tres particulier.
6. Aller sur `/admin/setup.php` pour creer le seul compte admin.
7. Ensuite utiliser `/admin/login.php`, puis `/admin/assignments.php` pour gerer le travail a faire.
8. Aller dans `/admin/chapitres.php`, puis choisir `Gerer le chapitre` pour administrer toutes ses ressources sur un seul ecran.
9. Aller dans `/admin/fichiers.php` pour envoyer les PDF, images et documents.
10. Aller dans `/admin/qcm.php`, `/admin/flashcards.php` et `/admin/exercices.php` pour gerer les activites dynamiques.
11. Aller dans `/admin/niveaux.php` et `/admin/matieres.php` pour gerer la structure du site.
12. Aller dans `/admin/lecons.php` pour creer les blocs de cours dynamiques.
13. Aller dans `/admin/sauvegarde.php` pour exporter/importer les contenus.

LaTeX est conserve via KaTeX. Dans les champs de texte, ecrire par exemple `\(2x+5=17\)` ou `\[E=mc^2\]`.

Important : apres creation du compte admin, ne laissez pas un mot de passe faible.

## Mise a jour depuis V51

Si la V51 est deja installee :

1. Remplacer les fichiers PHP/CSS/JS par ceux de cette version.
2. Importer `database/migrate_v51_to_v52.sql`, puis `database/migrate_v52_to_v53.sql`, puis `database/migrate_v53_to_v54.sql`, puis `database/migrate_v54_to_v55.sql`, puis `database/migrate_v55_to_v56.sql`, puis `database/migrate_v56_to_v57.sql`, puis `database/migrate_v57_to_v58.sql` dans phpMyAdmin.
3. Se connecter a `/admin/login.php`.
4. Aller dans `/admin/chapitres.php` pour commencer a remplir le contenu dynamique des cours.

Tout le contenu est desormais en base de donnees : il n'existe plus de page HTML statique dans le depot.

## Mise a jour depuis V52

Si la V52 est deja installee :

1. Remplacer les fichiers PHP/CSS/JS par ceux de cette version.
2. Importer `database/migrate_v52_to_v53.sql`, puis `database/migrate_v53_to_v54.sql`, puis `database/migrate_v54_to_v55.sql`, puis `database/migrate_v55_to_v56.sql`, puis `database/migrate_v56_to_v57.sql`, puis `database/migrate_v57_to_v58.sql` dans phpMyAdmin.
3. Verifier que le dossier `uploads/files` existe et que PHP peut y ecrire.
4. Se connecter a `/admin/login.php`.
5. Utiliser `/admin/fichiers.php` pour envoyer les documents.
6. Utiliser `/admin/assignments.php` pour joindre des fichiers ou liens au travail a faire.

Le dossier `uploads` contient un fichier `.htaccess` qui bloque l'execution de scripts envoyes par erreur.

## Mise a jour depuis V53

Si la V53 est deja installee :

1. Remplacer les fichiers PHP/CSS/JS par ceux de cette version.
2. Importer `database/migrate_v53_to_v54.sql`, puis `database/migrate_v54_to_v55.sql`, puis `database/migrate_v55_to_v56.sql`, puis `database/migrate_v56_to_v57.sql`, puis `database/migrate_v57_to_v58.sql` dans phpMyAdmin.
3. Se connecter a `/admin/login.php`.
4. Utiliser `/admin/qcm.php` pour creer des QCM.
5. Utiliser `/admin/flashcards.php` pour creer des decks et des cartes.
6. Utiliser `/admin/exercices.php` pour creer les exercices et corrections.

Les pages publiques `qcm.php`, `flashcards.php` et `exercices.php` affichent uniquement les contenus publies.

## Mise a jour depuis V54

Si la V54 est deja installee :

1. Remplacer les fichiers PHP/CSS/JS par ceux de cette version.
2. Importer `database/migrate_v54_to_v55.sql`, puis `database/migrate_v55_to_v56.sql`, puis `database/migrate_v56_to_v57.sql`, puis `database/migrate_v57_to_v58.sql` dans phpMyAdmin.
3. Se connecter a `/admin/login.php`.
4. Utiliser les nouveaux filtres dans `/admin/qcm.php`, `/admin/flashcards.php` et `/admin/exercices.php`.

## Mise a jour depuis V55

Si la V55 est deja installee :

1. Remplacer les fichiers PHP/CSS/JS par ceux de cette version.
2. Importer `database/migrate_v55_to_v56.sql`, puis `database/migrate_v56_to_v57.sql`, puis `database/migrate_v57_to_v58.sql` dans phpMyAdmin.
3. Verifier que les dossiers `uploads/levels` et `uploads/subjects` existent et que PHP peut y ecrire.
4. Se connecter a `/admin/login.php`.
5. Utiliser `/admin/niveaux.php` et `/admin/matieres.php`.

V56 modifie la table `subjects` pour ajouter image, couleur, resume, ordre et statut.

## Mise a jour depuis V56

Si la V56 est deja installee :

1. Remplacer les fichiers PHP/CSS/JS par ceux de cette version.
2. Importer `database/migrate_v56_to_v57.sql`, puis `database/migrate_v57_to_v58.sql` dans phpMyAdmin.
3. Se connecter a `/admin/login.php`.
4. Utiliser `/admin/lecons.php` pour creer les blocs de cours par chapitre.
5. Utiliser `/admin/fichiers.php` pour associer des documents aux chapitres.

V57 ajoute le champ `kind` dans la table `lessons`.

## Mise a jour depuis V57

Si la V57 est deja installee :

1. Remplacer les fichiers PHP/CSS/JS par ceux de cette version.
2. Importer `database/migrate_v57_to_v58.sql` seulement si vous voulez garder une trace des migrations.
3. Verifier que `includes/config.php` garde bien `base_url` vide.
4. Vider le cache du navigateur si l'ancien rendu brut reste affiche.

## Correctif V58 : CSS non charge

Si le site apparait comme du HTML brut avec des liens bleus, le fichier CSS n'est pas trouve par le navigateur.

V58 corrige les chemins generes par PHP :

- depuis `index.php`, le CSS est appele avec `./dist/output.css` ;
- depuis `/admin`, le CSS est appele avec `../dist/output.css` ;
- les images, fichiers envoyes, scripts et liens internes suivent le meme principe.

Apres l'envoi sur l'hebergement, verifier que le dossier `dist` est bien au meme niveau que `index.php`.

## Sauvegarde des contenus

La page `/admin/sauvegarde.php` permet :

- d'exporter les contenus dynamiques en JSON ;
- de reimporter ce JSON sur une autre installation ;
- de telecharger le dossier `uploads` en ZIP si l'extension PHP ZipArchive est disponible.

Les comptes admin ne sont pas exportes. Sur une nouvelle installation, creer d'abord le compte admin avec `/admin/setup.php`, puis importer les contenus.

## Mise a jour depuis V58

1. Depuis la V58, telecharger d'abord une sauvegarde JSON et une copie du dossier `uploads`.
2. Remplacer les fichiers du site par ceux de la V59 sans remplacer `includes/config.php`.
3. Importer `database/migrate_v58_to_v59.sql` dans phpMyAdmin.
4. Ouvrir `/admin/systeme.php` et verifier les indicateurs.
5. Ouvrir `/admin/sauvegarde.php` pour creer une premiere sauvegarde complete V59.

L'import se fait maintenant en deux etapes : verifier le JSON et lire l'apercu, puis choisir `AJOUTER` pour conserver l'existant ou `RESTAURER` pour le remplacer.

## Mise a jour depuis V59

1. Creer une sauvegarde complete depuis `/admin/sauvegarde.php`.
2. Remplacer les fichiers du site par ceux de la V60 sans remplacer `includes/config.php`.
3. Importer `database/migrate_v59_to_v60.sql` dans phpMyAdmin.
4. Vider le cache du navigateur ou recharger la page sans cache.
5. Ouvrir `/admin`, puis utiliser le nouveau menu lateral.

Les anciens contenus restent compatibles. Les nouveaux champs `alt_text` servent uniquement a decrire les images pour l'accessibilite.

## Mise a jour depuis V60

1. Creer une sauvegarde complete depuis `/admin/sauvegarde.php`.
2. Remplacer les fichiers du site par ceux de la V61 sans remplacer `includes/config.php` ni le dossier `uploads`.
3. Aucune modification de base de donnees n'est necessaire. Le fichier `database/migrate_v60_to_v61.sql` sert de trace.
4. Vider le cache du navigateur ou effectuer un rechargement sans cache.
5. Verifier les pages Travail a faire, Flashcards, Timer et Admin.

## Mise a jour depuis V61

1. Creer une sauvegarde complete depuis `/admin/sauvegarde.php`.
2. Remplacer les fichiers du site par ceux de la V62 sans remplacer `includes/config.php` ni le dossier `uploads`.
3. Ouvrir `/admin/lecons.php`. La colonne manquante est ajoutee automatiquement si le compte MySQL possede le droit `ALTER`.
4. Si un message de mise a niveau reste affiche, importer `database/migrate_v61_to_v62.sql` dans phpMyAdmin.
5. Ouvrir `/admin/systeme.php` et verifier que « Blocs de cours V62 » indique « Disponible ».

Cette migration conserve tous les niveaux, chapitres, cours, exercices et fichiers existants.

## Mise a jour depuis V62

1. Remplacer les fichiers du site par ceux de la V63 sans remplacer `includes/config.php` ni `uploads`.
2. Pour recreer l'unique administrateur, vider seulement la table `admins` dans phpMyAdmin.
3. Ouvrir `/admin/setup.php`, saisir deux fois un mot de passe d'au moins 10 caracteres, puis valider.
4. Apres la creation, le site ouvre automatiquement le tableau de bord.

Les contenus pedagogiques ne sont jamais supprimes par cette operation.

## Mise a jour depuis V63

1. Creer une sauvegarde complete depuis `/admin/sauvegarde.php`.
2. Remplacer les fichiers par ceux de la V64 sans remplacer `includes/config.php` ni le dossier `uploads`.
3. Ouvrir `/admin`. Les tables des videos, des savoirs et la categorie des documents sont creees automatiquement.
4. Si la mise a niveau automatique est refusee par l'hebergeur, importer `database/migrate_v63_to_v64.sql` dans phpMyAdmin.
5. Ouvrir `/admin/systeme.php` et verifier que « Ressources V64 » indique « Disponible ».

Les anciens contenus de cours restent stockes dans la base mais ne sont plus affiches. Aucun chapitre, exercice, QCM, flashcard ou fichier n'est supprime.

## Mise a jour depuis V68

1. Creer une sauvegarde complete depuis `/admin/sauvegarde.php`.
2. Remplacer les fichiers du site sans remplacer `includes/config.php` ni le dossier `uploads`.
3. Ouvrir `/admin`. Les colonnes d'ordre V69 sont ajoutees automatiquement si le compte MySQL possede le droit `ALTER`.
4. Si la mise a niveau automatique est refusee, importer `database/migrate_v68_to_v69.sql` une seule fois dans phpMyAdmin.
5. Ouvrir `/admin/pilotage.php` pour verifier les scores et alertes des chapitres.

La duplication complete cree des copies en brouillon. Les originaux ne sont jamais modifies.

## Mise a jour depuis V69

1. Creer une sauvegarde complete depuis `/admin/sauvegarde.php`.
2. Remplacer les fichiers du site sans remplacer `includes/config.php` ni le dossier `uploads`.
3. Aucune migration SQL n'est necessaire : les espaces Brevet et Bac utilisent les exercices et categories existants.
4. Vider le cache du navigateur afin de charger le CSS V70.
5. Ouvrir un niveau Troisieme ou Premiere, puis verifier le nouvel acces Brevet ou Bac.

## Mise a jour depuis V70

1. Creer une sauvegarde complete depuis `/admin/sauvegarde.php`.
2. Remplacer les fichiers du site sans remplacer `includes/config.php` ni le dossier `uploads`.
3. Aucune migration SQL n'est necessaire.
4. Vider le cache du navigateur ; la feuille de style V71 possede aussi un nouvel identifiant de cache.
5. Verifier l'accueil, une page de niveau et le tableau de bord admin en mode clair puis sombre.
