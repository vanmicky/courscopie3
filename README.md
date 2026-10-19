# MG — Application PHP/MySQL V124

## V124 — Service worker et archives

Le service worker passe en « réseau d'abord » pour le JS et le CSS : **plus
besoin de vider le cache** pour voir une correction. Les trois archives
(mise à jour / complet / installation) contiennent désormais `service-worker.js`.
Voir `CHANGELOG_V124.md`.


## V123 — Cartes redessinées

Blocage « quitter le site ? » corrigé (modifier/supprimer fonctionnent), cartes
compactes avec bouton Supprimer visible, formules rendues. Voir
`CHANGELOG_V123.md`.


## V122 — Formulaire QCM réorganisé

Une seule colonne : question, réponses A/B, réponses C–F repliées, bonne
réponse + ordre, image pleine largeur, explication. Voir
`CHANGELOG_V122.md`.


## V121 — QCM et flashcards complets

Images dans les pages `admin/qcm.php` et `admin/flashcards.php`, recto **et**
verso illustrés, QCM de 2 à 6 réponses, cartes compactes dans l'éditeur de
chapitre. Voir `CHANGELOG_V121.md`.


## V120 — Images et QCM flexibles

Images envoyées directement dans les QCM et les cartes (largeur réglable), QCM
de 2 à 6 réponses, liste des cartes alignée sans débordement. Voir
`CHANGELOG_V120.md`.


## V119 — Permissions

`fix-perms.php` corrige les droits (755/644) en un clic : c'était la cause du
CSS refusé sur le serveur. Voir `CHANGELOG_V119.md`.


## V118 — Fichiers statiques : types MIME et droits

`.htaccess` déclare explicitement les types MIME (CSS, JS, polices) et
`diag.php` teste six fichiers statiques (droits, HTTP, type reçu). Voir
`CHANGELOG_V118.md`.


## V117 — Diagnostic de déploiement

Ouvre `/diag.php` après l'envoi : 35 vérifications (fichiers, droits, CSS servi
en HTTP, type MIME, base, HTTPS) et la correction à appliquer pour chaque
échec. Voir `CHANGELOG_V117.md`.


## V116 — Déploiement en une seule archive

`php tools/build-deploy.php` produit `site-a-envoyer.zip` (3,9 Mo, 105 fichiers)
pour éviter la saturation des connexions FTP. Voir `DEPLOIEMENT.md`.


## V115 — Statistiques de visites

Nouvelle page d'administration « Trafic du site » : visites, visiteurs
différents, pays (localisation **hors ligne**, sans service externe), pages les
plus vues. Voir `CHANGELOG_V115.md`.


## V114 — KaTeX en local

Les formules mathématiques ne dépendent plus d'un CDN : KaTeX et ses 20 polices
sont servis depuis `assets/vendor/katex/`. Le site fonctionne même si les
domaines externes sont bloqués. Voir `CHANGELOG_V114.md`.


## V112 — Marque MG, audit et mise en ligne

Logo MG seul (plus de nom ni de version en pied de page), nouvel outil d'audit
complet `php tools/audit.php`, et guide de déploiement `DEPLOIEMENT.md`.


## V111 — Deux couleurs pour les cartes

Recto bleu indigo (question), verso vert (réponse) : on sait d'un coup d'œil
quelle face on regarde. Voir `CHANGELOG_V111.md`.


## V110 — Cartes mémoire

Barre de progression, raccourcis clavier complets (Entrée, ← →, 1/2), bouton
« Mélanger » et bilan qui permet de ne revoir que les cartes ratées. Voir
`CHANGELOG_V110.md`.


## V109 — Couleurs des QCM

Vert et rouge nettement plus foncés pour les réponses et le bilan des QCM
(`#14532d` / `#7f1d1d`), déclinés en mode sombre. Voir `CHANGELOG_V109.md`.


## V108 — Messages de confirmation

Le toast n'occupe plus la hauteur de l'écran (74 px au lieu de 812 px), ancré en
bas à droite, avec disparition automatique après 6 secondes. Voir
`CHANGELOG_V108.md`.


## V107 — Vue unifiée « Cours »

Le programme se gère en une seule page : rail de niveaux (avec compteurs et
couleurs), chapitres avec statut Publié/Brouillon et bouton Publier en un clic.
Feuille de route : `AUDIT-ADMIN.md`. Détails : `CHANGELOG_V107.md`.


## V106 — Refonte UX de l'éditeur de chapitre

Publication en un clic (chapitre et contenus), statut vert/ambre impossible à
manquer, badge « Ajouter / Modifier ». Audit complet : `AUDIT-ADMIN.md`.


## V105 — Publication des contenus

Un nouveau contenu est publié par défaut, le champ « Publié » envoie une valeur
explicite et la lecture du drapeau est corrigée : ce qui est enregistré apparaît
sur le site. La suite de tests passe à **49 vérifications** (`bash tools/run-tests.sh`).
Voir `CHANGELOG_V105.md`.


## V104 — Flashcards et suite de tests

Le bloc des cartes est désormais annoncé par un bandeau « Série sélectionnée »
et l'on y revient après chaque enregistrement. Nouvelle suite de tests
fonctionnels :

```bash
php tools/tests.php        # ou : bash tools/run-tests.sh
```

Voir `CHANGELOG_V104.md`.


## V103 — Éditeur de chapitre

Les panneaux de l'éditeur reçoivent un vrai rembourrage (28 px), la jauge n'est
plus rognée, les critères deviennent des pastilles et le panneau d'état suit la
lecture. Voir `CHANGELOG_V103.md`.


## V102 — Projection des outils de classe

Chaque outil s'ouvre seul et en grand pour le vidéoprojecteur :
`outils.php?projeter=minuteur` (ou `groupes`, `tirage`, `consignes`,
`animation`). Bouton « Projeter cet outil » sur chaque panneau, plein écran et
sortie par Échap. Voir `CHANGELOG_V102.md`.


## V101 — Raccourcis de niveau

Les descriptions des raccourcis sont retirées : « Travail à faire, Exercices,
QCM, Flashcards » tiennent sur une seule ligne. Voir `CHANGELOG_V101.md`.


## V100 — Cartes mémoire

Recto et verso ne s'affichent plus en même temps : les deux faces sont
superposées, la carte a une hauteur juste. Voir `CHANGELOG_V100.md`.


## V99 — En-tête des pages d'administration

Le titre n'est plus rogné par la barre d'accent et la description repasse sous
le titre, sur le même axe. Voir `CHANGELOG_V99.md`.


> **Base de données.** Le fichier `includes/config.local.php` (non versionné)
> est la configuration réellement utilisée : base `cours_preview`, MySQL sur le
> port **8889** (MAMP). Il a priorité sur `includes/config.php`, qui contient
> encore des identifiants d'exemple (`db_name = mysql`) : sans le fichier local,
> le site se connecte à la base système `mysql` où aucune table n'existe, et
> l'administration affiche « la table des résultats n'existe pas ».
>
> Ouvrir `/admin/` redirige vers le tableau de bord si la session est valide,
> vers la connexion sinon (`admin/index.php`).


## V98 — Menu permanent et connexion mémorisée

La barre latérale d'administration est **toujours visible** (plus d'accordéon,
plus de bouton sur ordinateur). La connexion propose **« Se souvenir de moi
pendant 30 jours »** : jeton haché en base, rotation à chaque usage, révocation
à la déconnexion. Voir `CHANGELOG_V98.md`.


## V97 — Direction artistique de l'administration

Refonte de l'admin (et seulement de l'admin) : ambiance « atelier tech et
épuré », en-tête compact, bandeau de chiffres clés en une seule surface,
hiérarchie portée par la typographie et les espacements, une seule couleur
d'accent. Spécification complète dans `DESIGN-ADMIN.md`.


## V96 — Administration simplifiée

Le menu d'administration passe de **16 entrées à 5 sections dépliables** (seule
la section courante est ouverte), avec une recherche en haut. Le tableau de bord
remplace ses **13 boutons** par **4 actions** concrètes. Voir
`CHANGELOG_V96.md`. CSS servi avec `?v=96`.


## V95 — Nettoyage et accès administration

- le texte de remplissage des chapitres (« Contenu repris du site statique… »)
  est supprimé du jeu de données et **masqué à l'affichage**, même s'il reste
  en base ;
- la page de connexion admin est **centrée** (elle héritait du décalage réservé
  à la barre latérale) ;
- l'accès à l'espace enseignant est de nouveau visible (pied de page + menu
  mobile) ;
- **mot de passe admin oublié** : sans terminal, vider la table `admins` dans
  phpMyAdmin puis ouvrir `/admin/setup.php` ; en terminal,
  `bash tools/reset-admin.sh`. Voir `CHANGELOG_V95.md`.


## V94 — Fiche méthode : colonne de lecture alignée

Le texte ne colle plus à gauche : héros et panneau de lecture sont bornés à la
**même largeur (46 rem)** et posés sur la même origine, donc parfaitement
alignés (mesuré : 0 px d'écart). Le vide à droite disparaît. Les textes des
onglets débordaient à cause d'un script resté en cache : il est versionné.


## V93 — Fiche « Apprendre à apprendre » : index latéral groupé

Les douze onglets sont remplacés par un **sommaire latéral collant**, groupé en
trois entrées (Les principes, Au quotidien, Par matière) et numéroté. Tout le
contenu tient dans une colonne : on peut scanner, comparer et utiliser la
recherche du navigateur. Sur téléphone, le sommaire devient un bloc repliable.


## V92 — Recherche par niveau en direct

Le filtrage des niveaux sur l'accueil se fait désormais **à la frappe**, comme
la recherche de chapitres dans un niveau : les cartes disparaissent au fur et à
mesure, le compteur et l'état vide se mettent à jour, sans appuyer sur Entrée.


## V91 — Héros de niveau : padding et dégradé teinté

Le héros de niveau redevient une **carte** : marges internes de 30 px et
**dégradé très léger de la couleur du niveau** (bordure assortie). La hauteur
reste contenue (192 → 273 px, contre 378 px à l'origine).


## V90 — Héros compactes

Les héros de l'accueil et des pages niveau ont été redessinés et resserrés :
le héros de niveau passe de **378 px à 192 px**, celui de l'accueil de **270 px
à 155 px**, sans perte d'information (titre, résumé, pastilles, illustration).
Voir `CHANGELOG_V90.md`. CSS servi avec `?v=90`.


## V89 — Composants « façon HyperUI », noir et blanc

Retour au **noir et blanc** (nuances de gris) et adoption de l'esthétique
**HyperUI** : boutons pleins quasi noirs, cartes blanches à bordure grise fine,
rayons resserrés, pastilles grises, anneaux de focus discrets. Seule la couleur
des matières subsiste, en accent discret. Voir `CHANGELOG_V89.md`.
CSS servi avec `?v=89`.


## V88 — Menu mobile en tiroir

Sur téléphone, la navigation devient un **panneau coulissant** (bottom sheet) ouvert
par un bouton hamburger : grandes cibles tactiles, couleur du niveau, fermeture au
fond, au lien, à la croix ou à Échap, avec un repli en bande horizontale sans
JavaScript. Voir `CHANGELOG_V88.md`. CSS servi avec `?v=88`.


## V87 — Couleur de matière, icônes, logo coloré

Le menu prend la couleur du niveau survolé, les pastilles portent des icônes SVG
et s'éclairent de la couleur de la matière, les icônes se multiplient dans
l'interface, et le logo « MG » adopte la couleur de la matière de la page.
Voir `CHANGELOG_V87.md`. CSS servi avec `?v=87`.


## V86 — Retour aux couleurs

Le thème monochrome est abandonné : le site retrouve la palette colorée
(crème chaud, marque indigo, couleurs de matières), en conservant les polices
**Inter + Krub** et tout le travail de structure. Le thème noir et blanc reste
disponible en une ligne (`src/ds-9-mono.css`). Voir `CHANGELOG_V86.md`.
CSS servi avec `?v=86`.


## V85 — Nuances de gris

Le thème monochrome gagne en profondeur : échelle de gris nommée (`--ds-grey-0`
à `--ds-grey-950`), paliers tonals, dégradés de surface très légers, halos,
ombres en couches, hiérarchie typographique par le ton. Les accents de matière
restent les seules couleurs. Voir `CHANGELOG_V85.md`. CSS servi avec `?v=85`.


## V84 — Thème monochrome

Tout le site passe en **noir et blanc, nuances de gris** : neutres, marque,
dégradés, halos, ombres et motif de fond. La seule couleur restante vient des
**accents de matière** (niveau, chapitre, domaine). Les couleurs d'état et la
coloration du code sont conservées mais fortement désaturées ; le feu tricolore
de niveau sonore garde ses couleurs (signal de classe).
Voir `CHANGELOG_V84.md`. CSS servi avec `?v=84`.


## V83 — Inter + Krub, et fiche méthode à onglets

Typographie unifiée : **Inter** pour le texte courant, **Krub** pour les titres
(le serif Fraunces est retiré). La fiche « Apprendre à apprendre » passe en
**onglets horizontaux** — une section affichée à la fois, bouton « Tout afficher »,
et un fond de lecture plein pour la lisibilité. Voir `CHANGELOG_V83.md`.
CSS servi avec `?v=83`.


## V82 — Passe de finition

Nettoyage et allègement, sans nouvelle fonctionnalité : **57,8 Ko de CSS mort**
supprimés (reliquats des 237 pages statiques), `theme-color` et préchargement de
police alignés, hiérarchie des titres corrigée, projet ramené de 24 Mo à 9,3 Mo.
Vérifié par comparaison d'images avant/après. Voir `CHANGELOG_V82.md`.
CSS servi avec `?v=82`.


## V81 — Outils de classe

Nouvel espace enseignant à onglets : minuteur plein écran, **formation de groupes**
(prénoms + nombre de groupes), tirage au sort, consignes à projeter, dés,
chronomètre et horloge. Le lien « Admin » est retiré du menu (accès par le pied de
page). Voir `CHANGELOG_V81.md`. CSS servi avec `?v=81`.


## V80 — Onglets horizontaux sur les pages chapitre

Les 8 sections d'une page chapitre (documents, exercices, vidéos, QCM, flashcards,
savoirs…) deviennent des **pastilles horizontales collantes** : plus besoin de défiler
pour trouver une ressource. Sans JavaScript, la barre redevient un jeu de liens
d'ancrage et toutes les sections restent visibles. Voir `CHANGELOG_V80.md`.
CSS servi avec `?v=80`.


## V79 — Lecture longue + motif de fond

La fiche « Apprendre à apprendre » passe d'une grille de 72 cartes sur 2-3 colonnes
à une colonne de lecture unique (mesure 70 caractères, sommaire collant, barre de
progression, encadrés unifiés). Le motif de fond devient un semis de points, toujours
sur les pages intérieures seulement. Voir `CHANGELOG_V79.md`. CSS servi avec `?v=79`.


## V78 — Motif de fond sur les pages intérieures

Quadrillage discret de papier millimétré (CSS pur, sans image) sur toutes les pages
sauf l'accueil, qui reste sur fond uni. Voir `CHANGELOG_V78.md`. CSS servi avec `?v=78`.


## V77 — Refonte éditoriale chaleureuse

Nouvelle direction visuelle : palette crème + encre profonde, titres en serif
Fraunces (auto-hébergé), héros agrandis, surfaces adoucies. Aucune migration SQL.
Voir `CHANGELOG_V77.md`. Le CSS est servi avec `?v=77`.


## V76 — Refonte visuelle moderne

Refonte visuelle sans toucher aux fonctionnalités : signature dégradée indigo→violet,
profondeur (halo de fond, en-tête verre dépoli, ombres étagées), héros plus affirmés,
cartes et boutons adoucis, zoom des illustrations de niveau au survol. Voir
`CHANGELOG_V76.md`. Le CSS est servi avec `?v=76` ; aucune migration SQL.


## V75 — Site entièrement dynamique, performance et suivi des élèves

- **plus aucune page statique** : les 239 fichiers HTML de `pages/` sont supprimés ;
- les 10 fiches méthodes passent en base (`/methodes.php`, `/methode.php`, gestion dans `/admin/methodes.php`) ;
- le minuteur de classe devient `/timer.php`, le mode hors-ligne `/offline.php` ;
- **résultats des QCM enregistrés** (`qcm_attempts`, `qcm_attempt_answers`) avec suivi dans `/admin/resultats.php`, questions les plus ratées et export CSV ;
- au début d'un QCM, nom et classe facultatifs pour que l'enseignant voie où la classe bloque ;
- performance : gzip et cache dans `.htaccess`, images WebP responsives (2,45 Mo → 0,50 Mo), police Inter auto-hébergée, KaTeX chargé seulement là où il sert ;
- PWA réactivée (manifeste + service worker + icônes), page 404 commune, `robots.txt` et `sitemap.xml` générés par PHP ;
- migration : `database/migrate_v74_to_v75.sql`. Voir `CHANGELOG_V75.md` et `INSTALLATION_SHARED_HOSTING.md`.


## V74 — Refonte visuelle, lisibilité et ergonomie

Aucune fonctionnalité modifiée : le travail porte sur la couche visuelle, la lisibilité,
l'accessibilité et l'ergonomie. Voir `CHANGELOG_V74.md` et `DESIGN_SYSTEM.md`.

- feuille de style réorganisée en sources (`src/ds-*.css`) assemblées par `tools/build-css.sh` ;
- 127 classes utilitaires manquantes rétablies : les mises en page reprennent l'intention d'origine ;
- échelle typographique unique et fluide, palette claire/sombre vérifiée (contrastes AA) ;
- accueil, niveau, chapitre, exercices, QCM, flashcards, travail à faire et espaces Brevet/Bac redessinés ;
- QCM : colonne de lecture centrée, compteur de réponses, bilan après correction, bouton « Recommencer » ;
- tableau de bord administrateur compact (10 statistiques cliquables), connexion en carte centrée avec jeton CSRF ;
- recherche instantanée dans la liste des chapitres de l'administration ;
- lien **Méthodes** ajouté à la navigation (les 11 fiches étaient orphelines) et pied de page enrichi ;
- minuteur de classe intégré au chrome du site (thème, en-tête, pied de page) ;
- vérifications : `php -l` sur tous les fichiers PHP, 56 URL testées (HTTP 200, aucune erreur PHP), captures 390 px et 1440 px, clair et sombre, audit de contraste automatique.

## V73 - Parcours visuel

Cette version repart de V71 : accueil illustré, images entières, cartes et bandeaux accordés aux niveaux, recherche avec résultats et remise à zéro, navigation mobile et gestion des actions du tableau de bord. Voir `CHANGELOG_V73.md` pour les limites de vérification et la mise à jour sans migration SQL.

## V71 - Design system et cohérence visuelle

- tokens uniques pour les couleurs, espacements, rayons, ombres et mouvements ;
- palette claire et sombre harmonisée avec les couleurs propres à chaque niveau ;
- composants cohérents sur le site élève et dans l'administration ;
- navigation active, focus clavier visible et lien d'accès direct au contenu ;
- formulaires, tableaux, messages et boutons normalisés ;
- cibles tactiles, grilles et rythme vertical améliorés sur mobile ;
- prise en charge de la préférence de réduction des animations.

Voir `CHANGELOG_V71.md` et `DESIGN_SYSTEM.md`. Aucune migration SQL n'est nécessaire depuis la V70.

## V70 - Espaces Brevet et Bac

- accès direct depuis les niveaux Troisième et Première ;
- regroupement des exercices de tous les chapitres concernés ;
- filtres par recherche, thème, difficulté et présence d'une correction ;
- corrections repliées pour laisser l'élève chercher avant de les consulter ;
- interface élève harmonisée, responsive et accordée à la couleur du niveau ;
- typographie, contrastes, états de focus et cartes simplifiés sur les pages publiques.

Voir `CHANGELOG_V70.md`. La mise à jour depuis la V69 ne nécessite aucune modification de base de données.

## V69 - Pilotage et publication simplifiée

- tableau de pilotage par niveau avec score de complétude, filtres, alertes et dernières modifications ;
- prévisualisation élève sécurisée, y compris pour les brouillons de la session admin ;
- publication ou mise en brouillon de plusieurs chapitres ou ressources en une opération ;
- ordre manuel des documents, exercices, vidéos, QCM, flashcards et savoirs ;
- duplication complète vers un autre niveau avec fichiers, travaux, QCM et flashcards associés ;
- détection des fichiers absents, exercices sans correction, QCM vides, decks vides et liens vidéo invalides.

Voir `CHANGELOG_V69.md` et `INSTALLATION_SHARED_HOSTING.md`. Depuis la V68, la mise à niveau est automatique ou peut être appliquée avec `database/migrate_v68_to_v69.sql`.

## V68 - Atelier complet d'un chapitre

- un seul ecran pour gerer les informations, documents, exercices, videos, QCM, flashcards et savoirs d'un chapitre ;
- creation et modification des questions de QCM et cartes recto/verso directement dans leur ressource ;
- nettoyage du CSS et composants d'administration plus compacts et uniformes ;
- tableau des documents pleinement utilisable sur mobile ;
- mode sombre coherent et correction du redemarrage des flashcards avec LaTeX.

Depuis `/admin/chapitres.php`, utiliser le bouton `Gerer le chapitre`. Voir `CHANGELOG_V68.md` pour le detail. Aucune migration SQL supplementaire n'est requise apres la V65.

## V67 - Bandeaux accordés aux niveaux

- chaque bandeau reprend automatiquement la couleur du niveau ou de la matière ;
- la couleur est assombrie pour garantir la lisibilité du texte blanc ;
- le rendu est uniforme sur les chapitres, flashcards, QCM, niveaux et matières ;
- les cartes de chapitres de l'administration récupèrent désormais la couleur de leur niveau.

## V66 - Cohérence des cartes et documents

- bandeaux sombres uniformes avec texte blanc sur les cartes de chapitres, matières et flashcards ;
- contrastes harmonisés dans l'administration et sur le site public ;
- documents présentés dans un tableau responsive ;
- recherche instantanée et filtres par niveau, chapitre, type et statut ;
- tri ascendant/descendant par colonne ;
- pagination de 10 documents ;
- modification, publication, ouverture et suppression conservées dans le tableau.

## V65 - Finitions pédagogiques

- correction du panneau de repères LaTeX dans l'administration ;
- rendu LaTeX fiable sur les deux faces des flashcards ;
- classement des exercices en préparation DS, révision Brevet ou type Bac ;
- section Brevet pour les niveaux Troisième et section type Bac pour les Première ;
- fonds à motif discret automatiquement accordés à la couleur de chaque niveau ;
- contrastes renforcés pour les titres de l'administration.

La colonne `exercises.category` est ajoutée automatiquement à l'ouverture de l'administration. Si nécessaire, importer `database/migrate_v64_to_v65.sql`.

Site pedagogique dynamique pour hebergement mutualise. Les niveaux, matieres, chapitres, blocs de cours, travaux, fichiers, QCM, flashcards et exercices sont geres depuis `/admin`.

## Changements visuels

- Mode clair par défaut.
- Titres diminués.
- Titres lisibles en mode sombre.
- Menu simplifié :
  - 5e
  - 3e
  - 2de → Physique-Chimie / Mathématiques
  - Première → Spécialité PC / Spécialité NSI
- Accueil avec jumbotron scientifique.
- Cartes de niveaux avec illustrations SVG locales.
- Pages de niveaux avec chapitres illustrés.
- Couleur spécifique par niveau.
- QCM avec score en haut et réponses A, B, C, D.
- Flashcards avec questions moins grandes.
- Blocs Python sur fond clair avec numéros de lignes.
- Page vidéos mieux hiérarchisée.

## Installation actuelle

La version dynamique demande PHP et MySQL. Suivre `INSTALLATION_SHARED_HOSTING.md`. Le CSS est deja compile dans `dist/output.css` : aucune commande npm n'est necessaire sur l'hebergement.

Le stylesheet est genere a partir de `src/ds-1-base.css`, `src/ds-2-components.css`,
`src/ds-3-pages.css` et de la couche historique `src/legacy-v73.css` :

```bash
tools/build-css.sh        # assemble dist/output.css (bash + cat, sans dependance)
```

Ne pas modifier `dist/output.css` directement : les changements seraient perdus au
prochain build.

### Tester en local

`includes/config.local.php` (non versionne, modele dans `includes/config.local.example.php`)
permet d'utiliser des identifiants de test sans toucher au `includes/config.php` de
production. `includes/db.php` le charge automatiquement s'il existe ; sinon, le
comportement reste inchange.

GitHub Pages ne peut pas executer cette version PHP/MySQL. Utiliser l'hebergement mutualise lie au domaine.

## Modifier

- Images : `assets/img/`
- PDF et documents : `fichiers/pdf/` et `uploads/`
- Styles : `src/ds-*.css` puis `tools/build-css.sh`
- Gabarits publics : `index.php`, `niveau.php`, `chapitre.php`, `exercices.php`,
  `qcm.php`, `flashcards.php`, `travail-a-faire.php`, `examen.php`,
  `methodes.php`, `methode.php`, `timer.php`, `404.php`
- Administration : `admin/`, mise en page et navigation dans `admin/auth.php`
- Tout le contenu se modifie depuis `/admin` (chapitres, exercices, QCM, flashcards,
  documents, vidéos, savoirs, fiches méthodes, travail à faire)


## v6 — améliorations ajoutées

- Préparation des formules avec KaTeX.
- Possibilité d’écrire :
  - formule en ligne : `$E = mc^2$`
  - formule centrée : `$$v = \frac{d}{\Delta t}$$`
- Blocs Python plus compacts.
- Fond clair pour le code, même en mode sombre.
- Numéros de lignes plus discrets.
- Icônes SVG locales pour :
  - PDF
  - vidéo
  - QCM
  - flashcards
  - formules
  - exercices
- Tuiles de documents plus lisibles.

## Propositions d’améliorations futures

1. Ajouter une page “Tableau de bord élève” avec les derniers chapitres.
2. Ajouter une barre de progression par chapitre.
3. Ajouter des filtres : cours / TP / vidéo / QCM / correction.
4. Ajouter une page “Révisions avant évaluation”.
5. Ajouter un mode “professeur” pour masquer ou afficher les corrections.
6. Ajouter une recherche globale.
7. Ajouter des tags par compétence du BO.
8. Ajouter une page par évaluation avec barème et compétences.
9. Ajouter des mini-animations SVG pour les chapitres de PC.
10. Ajouter une page “Méthodes” commune à tous les niveaux.

## Formules

Les formules sont rendues par KaTeX via :

```text
assets/js/math.js
```

Exemple :

```html
<p>Formule : $E = mc^2$</p>

$$v = \frac{d}{\Delta t}$$
```


## v7 — corrections

- Correction du menu : les sous-menus ne disparaissent plus quand on descend la souris.
- Les sous-menus peuvent aussi s’ouvrir au clic.
- Les tuiles de documents sont plus facilement cliquables.
- Ajout d’embeds YouTube réels dans `pages/videos/index.html`.
- Ajout d’une vidéo intégrée dans `pages/chapitres/chapitre-modele.html`.

## Remplacer une vidéo YouTube

Dans une URL comme :

```text
https://www.youtube.com/watch?v=fqrHujjh1eo
```

l’identifiant est :

```text
fqrHujjh1eo
```

Dans le site, remplace simplement :

```html
https://www.youtube-nocookie.com/embed/fqrHujjh1eo
```

par :

```html
https://www.youtube-nocookie.com/embed/NOUVEL_ID
```


## v8 — QCM et flashcards avec formules et code

Les QCM et les flashcards acceptent maintenant du contenu enrichi.

### Exemple de question avec formule

Dans `assets/js/quiz.js` :

```js
{
  question: "<p>La relation correcte pour une vitesse moyenne est :</p>",
  choices: [
    "<p>$v = d \\times \\Delta t$</p>",
    "<p>$v = \\dfrac{d}{\\Delta t}$</p>",
    "<p>$v = \\dfrac{\\Delta t}{d}$</p>",
    "<p>$v = d + \\Delta t$</p>"
  ],
  answer: 1,
  explanation: "La vitesse moyenne est $v = \\dfrac{d}{\\Delta t}$."
}
```

### Exemple de question avec code Python

```js
{
  question: "<p>Que renvoie le code suivant ?</p><pre class='mini-code'><code>x = 2\\nprint(x ** 3)</code></pre>",
  choices: [
    "<p><span class='inline-code'>5</span></p>",
    "<p><span class='inline-code'>6</span></p>",
    "<p><span class='inline-code'>8</span></p>",
    "<p>Une erreur</p>"
  ],
  answer: 2,
  explanation: "<span class='inline-code'>x ** 3</span> calcule $2^3$."
}
```

### Exemple de flashcard avec formule

```js
{
  front: "<p>Formule d’une vitesse moyenne</p>",
  back: "<p>La vitesse moyenne s’écrit :</p>$$v = \\frac{d}{\\Delta t}$$"
}
```

### Exemple de flashcard avec code

```js
{
  front: "<p>Fonction Python qui calcule une vitesse</p>",
  back: "<pre class='mini-code'><code>def vitesse(distance, duree):\\n    return distance / duree</code></pre>"
}
```

Important : comme le contenu est injecté dans la page, garde ces contenus édités par toi uniquement. N’utilise pas du HTML fourni librement par des élèves sans nettoyage.


## v9 — images retenues intégrées

Les images finales retenues ont été intégrées dans `assets/img/levels/`.

Correspondance :

```text
5e.jpeg          → carte 5e
3e.jpeg          → carte 3e
2e-pc.jpeg       → carte 2de Physique-Chimie
2e-maths.jpeg    → carte 2de Mathématiques
1ePC.jpeg        → carte Première spécialité PC
1eNSI.jpeg       → carte Première spécialité NSI
```

Mise à jour effectuée : accueil, cartes de niveaux, bandeaux des pages de niveaux et sous-menus 2de/Première avec miniatures.


## v10 — accueil épuré et cards colorées

Modifications :
- suppression des trois blocs QCM / PDF / Code sur l'accueil ;
- suppression du bloc “Nouvelles fonctionnalités” ;
- suppression de la phrase sous “Choisir son espace de travail” ;
- les cards des niveaux ont maintenant une couleur de fond cohérente avec leur image :
  - 5e : bleu/violet ;
  - 3e : vert ;
  - 2de PC : orange ;
  - 2de Maths : jaune ;
  - Première PC : rouge ;
  - Première NSI : violet/sombre.


## v11 — correction réelle de l'accueil

Corrections :
- suppression effective du grand hero “Les cours de M. Gedion” avec l'image bleue ;
- suppression des blocs QCM/PDF/Code et “Nouvelles fonctionnalités” si présents ;
- suppression de la phrase sous le titre des niveaux ;
- suppression du bandeau/couleur parasite au-dessus des images ;
- couleurs des cards forcées sur l’accueil avec sélecteurs CSS prioritaires :
  - 5e : violet ;
  - 3e : vert ;
  - 2de PC : orange ;
  - 2de Maths : jaune ;
  - Première PC : rouge ;
  - Première NSI : violet/sombre.


## v12 — pages de niveaux avec chapitres fictifs numérotés

Chaque page de niveau contient maintenant une progression fictive avec chapitres numérotés, fond coloré cohérent avec la card, motifs légers, boutons Cours / Vidéo / QCM / Flashcards et recherche de chapitre.


## v13 — chapitres cliquables avec ressources fictives

Ajouts :
- 41 pages individuelles de chapitre créées dans `pages/chapitres/`.
- Chaque carte de chapitre est cliquable.
- Chaque chapitre contient :
  - une section Cours fictif ;
  - des sous-parties fictives ;
  - un PDF fictif de correction TP ;
  - un PDF fictif de correction des exercices ;
  - une zone QCM fictive ;
  - une zone Flashcards fictives.
- Les PDF sont dans `fichiers/pdf/chapitres/`.


## v14 — sections vidéo numérotées dans chaque chapitre

Ajouts :
- section `Vidéos du chapitre` dans chaque page de chapitre ;
- 3 vidéos numérotées par chapitre : Vidéo 1, Vidéo 2, Vidéo 3 ;
- embeds YouTube en `youtube-nocookie.com`;
- attribut `loading="lazy"` sur les iframes ;
- lien direct `Vidéos` depuis les cartes de chapitres.


## v15 — notions, compétences exigibles et exercices supplémentaires corrigés

Modifications :
- suppression de la section “Cours fictif” dans chaque chapitre ;
- ajout d’une section “Notions et compétences exigibles” en deux colonnes ;
- ajout d’une section “Exercices supplémentaires avec correction” dans chaque chapitre ;
- mise à jour des boutons : “Cours” devient “Notions” ;
- ajout d’un accès rapide vers les exercices supplémentaires corrigés.


## v16 — exercices verticaux et corrections déroulantes

Modifications :
- les exercices supplémentaires ne sont plus en colonnes ;
- affichage vertical : un exercice par ligne ;
- chaque correction est masquée par défaut ;
- la correction se déroule au clic grâce aux balises HTML `<details>` et `<summary>`.


## v17 — vrai modèle pédagogique de chapitre

Chaque page de chapitre a été reconstruite avec un modèle pédagogique commun :
1. Objectifs, notions et compétences exigibles ;
2. Vidéos numérotées ;
3. Activité / TP et corrections PDF ;
4. Exercices progressifs verticaux avec corrections déroulantes ;
5. QCM expliqué ;
6. Flashcards ;
7. Bilan à retenir et auto-évaluation.

Objectif pédagogique :
- rendre chaque chapitre plus lisible ;
- guider l’élève étape par étape ;
- faciliter l’autonomie ;
- conserver une structure homogène pour tous les niveaux.


## v18 — QCM, corrections, Python, flashcards, formules/images

Améliorations :
- QCM expliqué avec bonnes réponses, mauvaises réponses et explications ;
- corrections déroulantes avec fond vert ;
- boutons corrigés pour éviter blanc sur blanc ;
- blocs Python colorés ;
- emplacements pour formules et images ;
- flashcards avec recto et verso de couleurs différentes.


## v19 — QCM pages dédiées, flashcards dédiées, exercices en dernier

Modifications :
- partie 3 des chapitres : uniquement les corrections PDF ;
- suppression de l’activité guidée ;
- partie 5 : uniquement des boutons QCM ;
- création de 3 pages QCM par chapitre : QCM 1, QCM 2, QCM 3 ;
- chaque page QCM garde le modèle pédagogique : bonnes réponses, mauvaises réponses, explications, formules et emplacement image ;
- partie 6 : bouton “Ouvrir les flashcards” ;
- création d’une page flashcards dédiée par chapitre ;
- exercices placés tout à la fin du chapitre ;
- code Python passé en fond clair pour être plus lisible.


## v20 — QCM et flashcards interactifs

Modifications :
- les pages QCM ne sont plus statiques ;
- réponse cliquable avec correction immédiate ;
- score affiché et enregistrable dans le navigateur via localStorage ;
- navigation question précédente / suivante ;
- barre de progression ;
- les flashcards sont interactives :
  - clic pour retourner recto/verso ;
  - bouton précédente / suivante ;
  - bouton “Je sais” et “À revoir” ;
  - compteur de cartes connues ;
  - sauvegarde locale.


## v21 — correction chargement QCM/flashcards

Correctif :
- suppression du chargement JSON qui pouvait être mal échappé ;
- données QCM et flashcards injectées directement dans `window.__QUIZ_DATA__` et `window.__FLASH_DATA__` ;
- JavaScript rendu plus robuste ;
- flashcards recto/verso cliquables avec réponse au verso ;
- conservation des formules et images possibles dans les cartes.


## v22 — correction ERR_FILE_NOT_FOUND

Correctif :
- correction des chemins relatifs dans les pages QCM et flashcards ;
- les pages `pages/qcm/chapitres/` et `pages/flashcards/chapitres/` sont un niveau plus profond que les pages chapitre ;
- les liens vers `dist/`, `assets/`, `index.html` et `pages/niveaux/` ont été ajustés ;
- vérification automatique des liens internes HTML : 0 lien(s) local(aux) manquant(s).


## v23 — cahier de texte par niveau et matière

Modifications :
- suppression des phrases descriptives sur les pages de niveau ;
- suppression du bloc “Organisation fictive” ;
- ajout d’un bloc “Cahier de texte” sur chaque page de niveau ;
- création de 6 pages de cahier de texte dans `pages/cahier/` ;
- chaque cahier contient un travail à faire daté ;
- filtres par recherche et par date ;
- cases “Fait” sauvegardées localement avec `localStorage` ;
- vérification liens internes : 0 lien(s) cassé(s).


## v24 régénérée
- Nouveau lien valide.
- Dates au format mardi 05 juin via JavaScript.
- Devoirs en puces.
- Bouton simple sur l’accueil.
- Liens cassés: 0.


## v25 — accueil et pages niveaux simplifiés

Modifications :
- page d’accueil : uniquement un bouton simple `Voir le travail à faire` ;
- pages niveau/matière : suppression du détail des devoirs ;
- pages niveau/matière : conservation d’un seul bouton `Voir le travail à faire` ;
- images de niveau/matière remises à droite dans le bandeau ;
- suppression des textes `Organisation fictive` et phrases descriptives ;
- vérification liens internes : 0 lien(s) cassé(s).


## v26 — page globale Travail à faire
- Page `pages/cahier/travail-a-faire.html` créée.
- Bouton accueil vers page globale.
- Filtres recherche/niveau/matière/date/statut.
- Statistiques affichés/faits/à faire.
- Liens cassés : 0.


## v27 — Page Méthodes

Ajouts :
- création de `pages/methodes/index.html` ;
- création de 9 pages méthodes détaillées ;
- filtres par recherche, domaine et niveau ;
- chaque méthode contient étapes, formule, emplacement image/schéma, mini-exercice corrigé déroulant ;
- bouton `Méthodes` ajouté sur l’accueil et les pages niveau ;
- vérification liens internes : 0 lien(s) cassé(s).


## v28 — Mode professeur / élève

Ajouts :
- bouton flottant `Élève / Professeur` sur toutes les pages ;
- mode sauvegardé dans `localStorage` ;
- contenus `teacher-only` masqués en mode élève ;
- contenus `student-only` masqués en mode professeur ;
- corrections déroulantes ouvertes automatiquement en mode professeur ;
- fiches professeur ajoutées sur les pages de chapitres ;
- panneaux de pilotage ajoutés sur les pages niveau ;
- conseils professeur ajoutés sur les pages méthodes ;
- vérification liens internes : 0 lien(s) cassé(s).


## v29 — hors-ligne, révision, accessibilité, suppression mode prof/élève

Modifications :
- suppression du mode professeur / élève ;
- suppression du script `mode-prof-eleve.js` ;
- suppression des panneaux professeur et des boutons flottants ;
- ajout d’un mode hors-ligne :
  - `service-worker.js`,
  - `manifest.webmanifest`,
  - `offline.html`,
  - `assets/js/register-sw.js` ;
- ajout de `pages/revision/avant-evaluation.html` ;
- ajout des boutons `Réviser avant l’évaluation` ;
- amélioration accessibilité / contraste :
  - focus visible,
  - boutons plus lisibles,
  - tailles minimales des éléments cliquables,
  - contraste renforcé des textes et champs ;
- vérification liens internes : 21 lien(s) cassé(s).


## v29 correctif
- Liens internes revérifiés : 1 lien(s) cassé(s).
- Restes visibles du mode professeur/élève : 0 page(s).

- Correction finale liens : 0 lien(s) cassé(s).


## v30 — Apprendre à apprendre

Modifications :
- suppression de la page `pages/revision/avant-evaluation.html` ;
- suppression des boutons/liens `Réviser avant l’évaluation` ;
- création de `pages/methodes/apprendre-a-apprendre.html` ;
- ajout d’un bouton `Apprendre à apprendre` sur l’accueil ;
- ajout de la carte dans la page `Méthodes` ;
- page organisée avec onglets :
  - Courbe d’Ebbinghaus,
  - Réviser les maths,
  - Réviser la physique-chimie,
  - Réviser la NSI,
  - Plan de semaine ;
- ajout d’un schéma SVG simplifié de la courbe de l’oubli ;
- vérification liens internes : 0 lien(s) cassé(s) ;
- mentions restantes de l’ancienne page révision : 0.


## v31 — Apprendre à apprendre dans la navbar + onglets verticaux

Modifications :
- suppression du texte `Mode hors-ligne` de la page d’accueil, fonction hors-ligne conservée ;
- suppression du bouton `Apprendre à apprendre` de la page d’accueil ;
- ajout d’un onglet `Apprendre à apprendre` dans la navbar ;
- onglets de la page `Apprendre à apprendre` placés verticalement ;
- ajout de la partie `Les 3 mémoires` :
  - mémoire sensorielle,
  - mémoire de travail,
  - mémoire à long terme ;
- ajout des parties :
  - concentration en classe,
  - travail à la maison,
  - téléphone portable ;
- vérification liens internes : 0 lien(s) cassé(s).


## v32 — Accueil restauré

Modifications :
- page d’accueil restaurée avec la présentation des anciennes versions ;
- suppression sur l’accueil du bloc `Mode hors-ligne` ;
- suppression sur l’accueil du bouton `Apprendre à apprendre` ;
- fonction hors-ligne conservée : manifest + service worker + script d’enregistrement ;
- bouton `Voir le travail à faire` conservé et relié à la page globale ;
- vérification liens internes : 0 lien(s) cassé(s).

- Correctif final accueil : mention `Apprendre à apprendre` restante sur accueil = True ; liens cassés = 0.


## v33 — Réécriture complète de la page Apprendre à apprendre

Modifications :
- réécriture complète de `pages/methodes/apprendre-a-apprendre.html` ;
- contenu renforcé avec une approche sciences cognitives vulgarisée ;
- nouveaux onglets :
  - les 5 règles d’or,
  - courbe d’Ebbinghaus,
  - les 3 mémoires,
  - se concentrer en classe,
  - travailler à la maison,
  - téléphone portable,
  - mathématiques,
  - physique-chimie,
  - NSI,
  - plan de semaine ;
- ajout de tableaux, checklists, routines et conseils directement utilisables par les élèves ;
- vérification liens internes : 0 lien(s) cassé(s).


## v34 — Intégration de la page fournie

Modifications :
- utilisation du fichier HTML fourni par l’utilisateur comme nouvelle page `pages/methodes/apprendre-a-apprendre.html` ;
- conservation de la page dans l’emplacement concerné ;
- correction automatique des chemins relatifs vers `dist/`, `assets/`, `pages/`, `manifest.webmanifest` et les scripts ;
- carte `Apprendre à apprendre` conservée dans la page `Méthodes` ;
- vérification liens internes : 0 lien(s) cassé(s).


## v35 — Page Apprendre à apprendre plus lisible + Font Awesome

Modifications :
- restructuration complète de `pages/methodes/apprendre-a-apprendre.html` ;
- conservation des contenus principaux : règles d’or, Ebbinghaus, 3 mémoires, concentration, travail à la maison, téléphone, maths, physique-chimie, NSI, plan de semaine ;
- remplacement des onglets par un sommaire latéral sticky plus lisible ;
- découpage en sections courtes, cartes, checklists, tableaux et callouts ;
- ajout de Font Awesome via CDN ;
- ajout d’icônes dans le sommaire, les titres et les cartes ;
- amélioration de la lisibilité mobile ;
- vérification liens internes : 0 lien(s) cassé(s).


## v36 — Forme v35 + contenu du fichier fourni

Modifications :
- conservation de la forme lisible de la v35 : sommaire latéral sticky, cartes, checklists, sections courtes, Font Awesome ;
- récupération du contenu depuis `/mnt/data/apprendre-a-apprendre(1).html` ;
- réorganisation du contenu fourni en sections :
  - règles d’or,
  - oubli et rappels espacés,
  - 3 mémoires,
  - métacognition,
  - concentration en classe,
  - travail à la maison,
  - téléphone portable,
  - sommeil et corps,
  - maths,
  - physique-chimie,
  - NSI,
  - plan de semaine ;
- blocs/titres repris : 45/45 ;
- vérification liens internes : 0 lien(s) cassé(s).


## v37 — Apprendre à apprendre en une seule colonne

Modifications :
- page `Apprendre à apprendre` forcée en une seule colonne ;
- suppression de la grille 2 colonnes / 3 colonnes sur cette page ;
- sommaire placé au-dessus du contenu, non latéral ;
- cartes affichées les unes sous les autres ;
- largeur de lecture limitée pour améliorer le confort ;
- vérification liens internes : 0 lien(s) cassé(s).


## v38 — Correction CSS GitHub Pages

Problème corrigé :
- `dist/output.css` contenait seulement un commentaire placeholder.
- GitHub chargeait donc un fichier CSS quasiment vide.

Corrections :
- génération d’un vrai `dist/output.css` avec les styles du site ;
- ajout de classes utilitaires minimales pour remplacer les classes Tailwind les plus utilisées ;
- chargement de Tailwind Browser CDN comme filet de sécurité ;
- aucune commande `npm install` ou `npm run build` nécessaire ;
- vérification liens internes : 0 lien(s) cassé(s).


## v39 — UI premium, mobile et robustesse GitHub Pages

Modifications :
- ajout d'une page d'accueil plus forte avec accès directs aux niveaux, au travail à faire et aux méthodes ;
- modernisation globale des cartes, boutons, badges, états hover/active/focus et contrastes ;
- navigation mobile plus propre avec défilement tactile horizontal ;
- cohérence visuelle renforcée entre Mathématiques, Physique-Chimie et NSI ;
- retrait du runtime Tailwind Browser CDN : le site s'appuie sur le CSS statique `dist/output.css` ;
- correction de l'enregistrement du service worker depuis les sous-pages ;
- mise à jour du cache offline en `cours-gedion-v39` ;
- vérification chemins relatifs/assets/CSS/JS/PDF : 0 lien(s) local(aux) cassé(s).


## v40 — Corrections responsive globales

Modifications :
- menu principal harmonisé : `5e` et `3e` sont maintenant dans un menu `Collège` avec images ;
- navigation mobile complétée avec tous les niveaux ;
- page `Travail à faire` corrigée : filtres, champs, listes déroulantes, badges et cartes ne débordent plus sur mobile/tablette ;
- protections globales contre les débordements horizontaux sur les images, tableaux, blocs de code, formules et cartes ;
- ajustement des actions et cases à cocher dans les devoirs ;
- mise à jour du cache offline en `cours-gedion-v40` ;
- vérification liens internes et chemins relatifs : 0 lien(s) local(aux) cassé(s).


## v41 — Accueil plus compact

Modifications :
- réduction de la zone de recherche sur la page d'accueil ;
- grille des niveaux forcée en 3 colonnes sur écran large ;
- repli responsive conservé : 2 colonnes sur tablette, 1 colonne sur mobile ;
- hauteur des images de cartes légèrement réduite pour un accueil plus équilibré ;
- mise à jour du cache offline en `cours-gedion-v41`.


## v42 — Champs de recherche compacts

Modifications :
- réduction globale de la taille des champs de recherche ;
- application sur l'accueil, les pages de niveaux, les méthodes, les cahiers et le travail à faire ;
- visuel plus propre : champ moins haut, moins large, bordure plus douce et focus plus discret ;
- pleine largeur conservée uniquement sur très petits écrans ;
- mise à jour du cache offline en `cours-gedion-v42`.


## v43 — Espacement et visuels de niveaux

Modifications :
- ajout d'un espace plus confortable sous les champs de recherche ;
- amélioration des images des pages de niveau avec un libellé centré/lisible ;
- ajout d'un bandeau contrasté sur les images pour mieux voir le niveau et la classe ;
- recadrage plus stable des images sur desktop et mobile ;
- mise à jour du cache offline en `cours-gedion-v43`.


## v44 — QCM et exercices

Modifications :
- bonne réponse QCM clairement verte sur les QCM statiques et interactifs ;
- lettre de la bonne réponse également verte, avec contraste renforcé ;
- ajout d'un indicateur `Bonne réponse` sur desktop et d'une coche compacte sur mobile ;
- mauvaise réponse plus lisible en rouge ;
- cartes d'exercices et corrections visuellement améliorées ;
- mise à jour du cache offline en `cours-gedion-v44`.


## v45 — Guidage et filtres rapides

Modifications :
- ajout d'un bloc `Je veux...` sur l'accueil avec accès directs aux chapitres, QCM et travail à faire ;
- ajout d'une synthèse de ressources sur chaque page de niveau : chapitres, QCM, flashcards et PDF ;
- ajout de filtres rapides sur `Travail à faire` : tout, à faire, faits, cette semaine, QCM, exercices et TP ;
- amélioration visuelle des cartes de chapitres et boutons d'action ;
- conservation des contenus et fonctions existants ;
- mise à jour du cache offline en `cours-gedion-v45`.


## v46 — Interface plus clean

Modifications :
- suppression du bouton redondant `Voir le travail à faire` sur l'accueil ;
- design plus sobre avec moins d'ombres et des cartes plus plates ;
- bloc `Je veux...` rendu plus compact ;
- synthèse des pages de niveau simplifiée ;
- filtres rapides du travail à faire transformés en boutons segmentés plus propres ;
- cartes de chapitres et boutons d'action légèrement densifiés ;
- mise à jour du cache offline en `cours-gedion-v46`.

## V47 - Etiquettes de niveaux colorees

- etiquettes `level-visual-label` synchronisees avec la couleur de chaque niveau ;
- contraste renforce sur les images de niveaux ;
- bordures et halos des visuels harmonises avec 5e, 3e, 2de PC, 2de Maths, 1re PC et 1re NSI ;
- mise a jour du cache offline en `cours-gedion-v47`.

## V48 - Timer de classe et feu sonore

- nouvelle page `Timer classe` avec horloge et compte a rebours grand affichage ;
- sonnerie style Nokia generee dans le navigateur, sans asset audio externe ;
- feu tricolore cliquable pour signaler le niveau sonore de la classe ;
- raccourci Timer ajoute dans la navigation desktop, mobile et sur l'accueil ;
- mise a jour du cache offline en `cours-gedion-v48`.

## V49 - Timer plus discret dans le site

- menu simplifie : libelle `Timer` ;
- accueil nettoye : retrait du bloc outil ajoute en V48 ;
- feu tricolore agrandi sur la page Timer ;
- mise a jour du cache offline en `cours-gedion-v49`.

## V50 - CSS centralise

- retrait des blocs CSS embarques dans les pages HTML ;
- style commun regroupe dans `dist/output.css` ;
- styles du Timer integres au fichier CSS central ;
- pages HTML beaucoup plus courtes et plus faciles a maintenir ;
- mise a jour du cache offline en `cours-gedion-v50`.

## V51 - Premier socle PHP/MySQL

- ajout d'une architecture PHP pour hebergement mutualise ;
- ajout des pages dynamiques `index.php`, `niveau.php`, `chapitre.php` et `travail-a-faire.php` ;
- ajout d'un espace admin minimal : installation du premier compte, connexion, tableau de bord et gestion du travail a faire ;
- ajout du schema MySQL complet pour niveaux, matieres, chapitres, cours, exercices, QCM, flashcards, fichiers, annonces et devoirs ;
- integration de `assignments`, `assignment_files` et `assignment_links` pour le travail a faire ;
- conservation de KaTeX pour LaTeX dans les contenus dynamiques ;
- cache offline mis a jour en `cours-gedion-v51`.

## V52 - Admin chapitres et cours

- ajout d'une vraie gestion admin des chapitres dans `/admin/chapitres.php` ;
- edition du titre, du resume, du contenu de cours, du niveau, de la matiere et du statut publie/brouillon ;
- affichage public du contenu dynamique dans `chapitre.php` quand il existe ;
- conservation du lien vers la version statique pendant la migration ;
- ajout de `database/migrate_v51_to_v52.sql` pour les installations V51 deja en place ;
- cache offline mis a jour en `cours-gedion-v52`.

## V53 - Travail a faire + fichiers

- ajout de `/admin/fichiers.php` pour envoyer et gerer les documents ;
- upload securise : PDF, images, documents Office, TXT, ZIP, maximum 20 Mo ;
- ajout de pieces jointes et liens externes dans `/admin/assignments.php` ;
- affichage public des fichiers et liens sur `travail-a-faire.php` ;
- tableau de bord admin enrichi avec le compteur et le raccourci fichiers ;
- ajout de `database/migrate_v52_to_v53.sql` ;
- cache offline mis a jour en `cours-gedion-v53`.

## V54 - QCM, flashcards et exercices dynamiques

- ajout des pages publiques dynamiques `qcm.php`, `flashcards.php` et `exercices.php` ;
- correction automatique des QCM avec bonne reponse en vert ;
- gestion admin des QCM, questions, reponses A/B/C/D et bonne lettre ;
- gestion admin des decks de flashcards et des cartes ;
- gestion admin des exercices, enonces, corrections, difficultes et publication ;
- LaTeX conserve dans les questions, cartes, exercices et corrections ;
- tableau de bord admin enrichi avec compteurs et raccourcis ;
- ajout de `database/migrate_v53_to_v54.sql` ;
- cache offline mis a jour en `cours-gedion-v54`.

## V55 - Admin plus propre

- ajout de filtres dans `/admin/qcm.php`, `/admin/flashcards.php` et `/admin/exercices.php` ;
- filtrage par niveau, statut publie/brouillon et recherche texte ;
- aide LaTeX ajoutee dans les formulaires de questions, cartes et exercices ;
- badges publie/brouillon plus lisibles ;
- formulaires admin renforces pour le responsive ;
- ajout de `database/migrate_v54_to_v55.sql` sans changement de table ;
- cache offline mis a jour en `cours-gedion-v55`.

## V56 - Structure dynamique

- ajout de `/admin/niveaux.php` pour creer/modifier/supprimer les niveaux ;
- upload d'image pour les niveaux dans `uploads/levels` ;
- ajout de `/admin/matieres.php` pour creer/modifier/supprimer les disciplines ;
- upload d'image pour les matieres dans `uploads/subjects` ;
- couleur, resume, ordre et statut publie/brouillon geres depuis l'admin ;
- menu public dynamique : les niveaux affiches viennent de MySQL ;
- tableau de bord enrichi avec niveaux et matieres ;
- schema MySQL mis a jour pour les metadonnees de matieres ;
- ajout de `database/migrate_v55_to_v56.sql` ;
- cache offline mis a jour en `cours-gedion-v56`.

## V57 - Chapitres complets

- ajout de `/admin/lecons.php` pour gerer les blocs de cours par chapitre ;
- blocs types : cours, definition, methode, exemple, activite, bilan, a retenir ;
- affichage public des blocs dynamiques dans `chapitre.php` ;
- affichage des fichiers lies au chapitre dans `chapitre.php` ;
- lien rapide depuis les chapitres admin vers les blocs de cours ;
- mediatheque utilisable avec un chapitre preselectionne ;
- ajout du champ `kind` a la table `lessons` ;
- ajout de `database/migrate_v56_to_v57.sql` ;
- cache offline mis a jour en `cours-gedion-v57`.

## V58 - Correctif CSS hebergement mutualise

- correction des chemins generes par PHP quand `base_url` est vide ;
- chargement de `dist/output.css` depuis l'accueil, les pages publiques et `/admin` ;
- liens JS, images de niveaux/matieres et fichiers uploades rendus compatibles avec une installation en sous-dossier ;
- ajout de `/admin/sauvegarde.php` pour exporter/importer les contenus dynamiques en JSON ;
- export du dossier `uploads` en ZIP si l'extension PHP ZipArchive est disponible ;
- exclusion volontaire des comptes admin dans les exports de contenus ;
- documentation d'installation clarifiee : laisser `base_url` vide dans la plupart des cas ;
- ajout de `database/migrate_v57_to_v58.sql` sans changement de table ;
- cache offline mis a jour en `cours-gedion-v58`.

## V59 - Sauvegardes securisees et etat du systeme

- sauvegarde complete en un ZIP avec `contenus.json` et le dossier `uploads` ;
- apercu obligatoire du JSON avant restauration ;
- controle du format, de la version, des tables, de la taille et du nombre de lignes ;
- restauration transactionnelle avec confirmation `RESTAURER` ;
- mode d'import `AJOUTER` pour conserver les contenus deja presents ;
- historique des exports, verifications et restaurations ;
- ajout de `/admin/systeme.php` pour verifier l'hebergement ;
- ajout de la table `backup_logs` et de `database/migrate_v58_to_v59.sql` ;
- comptes admin toujours exclus des sauvegardes ;
- cache offline mis a jour en `cours-gedion-v59`.

## V60 - Editeur pedagogique et administration unifiee

- menu admin lateral et responsive sur toutes les pages de gestion ;
- editeur avec barre d'outils et apercu LaTeX ;
- insertion des fichiers et images de la mediatheque dans les contenus ;
- recherche globale dans les niveaux, matieres, cours, activites et fichiers ;
- duplication complete des chapitres, QCM et decks en brouillon ;
- descriptions accessibles des images de niveaux et matieres ;
- ajout de `database/migrate_v59_to_v60.sql` ;
- cache offline mis a jour en `cours-gedion-v60`.

## V61 - Navigation, devoirs, timer et flashcards

- correction des devoirs sans matiere associee et suppression de l'avertissement `subject` ;
- dates publiques et administratives affichees au format europeen `JJ-MM-AAAA` ;
- menu public simplifie : classes, Timer et Admin ;
- session admin conservee pendant 12 heures et redirection directe vers le tableau de bord ;
- suppression complete du mode horloge dans l'outil Timer ;
- nouveau parcours flashcards : recto, verso, auto-evaluation, carte suivante et bilan ;
- navigation clavier et etats de focus ameliores pour les flashcards ;
- marges des bandeaux de chapitre harmonisees avec les autres pages ;
- ajout de `database/migrate_v60_to_v61.sql` sans changement de table ;
- cache offline mis a jour en `cours-gedion-v61`.

## V62 - Mise a niveau de la base des blocs de cours

- detection de la colonne `kind` dans la table `lessons` ;
- ajout automatique de cette colonne depuis la page Blocs de cours lorsqu'elle manque ;
- remplacement de l'erreur SQL brute par une consigne claire si l'hebergeur refuse la modification automatique ;
- controle de cette colonne dans `/admin/systeme.php` ;
- ajout de `database/migrate_v61_to_v62.sql` pour une correction manuelle avec phpMyAdmin ;
- aucune suppression ni modification des blocs de cours existants ;
- cache offline mis a jour en `cours-gedion-v62`.

## V63 - Creation et recuperation du compte admin

- correction du formulaire `/admin/setup.php` qui pouvait echouer sans message clair ;
- creation automatique de la table `admins` lorsqu'elle est absente ;
- verification explicite de la connexion et des droits MySQL ;
- confirmation obligatoire du nouveau mot de passe ;
- protection du formulaire par jeton de session ;
- connexion automatique apres la creation reussie du compte ;
- messages adaptes pour un email deja present ou une table incompatible ;
- formulaire responsive et accessible ;
- cache offline mis a jour en `cours-gedion-v63`.

## V64 - Chapitres organises par ressources

- retrait de la section Cours sur les pages publiques ;
- nouvel ordre : documents, exercices de preparation, videos, QCM, flashcards, savoirs et savoir-faire ;
- categories de documents : activite, correction d'activite, correction des exercices, feuille d'exercices et carte mentale ;
- videos YouTube et Vimeo affichees dans une grille responsive de trois colonnes ;
- savoirs et savoir-faire cochables avec progression memorisee dans le navigateur ;
- nouvelle administration `/admin/ressources-chapitre.php` ;
- notifications visuelles apres enregistrement et messages d'erreur renforces ;
- prise en charge LaTeX explicite dans les questions, reponses et explications des QCM ;
- marges des cartes et etiquettes Chapitre corrigees ;
- migration automatique ou manuelle avec `database/migrate_v63_to_v64.sql` ;
- sauvegardes et cache offline mis a jour pour la V64.
