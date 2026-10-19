# Mise en ligne — mode d'emploi

Guide pas à pas pour installer le site sur un hébergement mutualisé classique
(OVH, Ionos, Infomaniak, Alwaysdata…). Compte environ **30 minutes**.

---

## 1. Ce qu'il faut

| Élément | Minimum | Recommandé |
| --- | --- | --- |
| PHP | 8.0 | **8.2** |
| MySQL / MariaDB | 5.7 / 10.3 | 8.0 / 10.6 |
| Espace disque | 20 Mo | 50 Mo |
| HTTPS (certificat) | — | **obligatoire** |

Extensions PHP nécessaires : `pdo_mysql`, `mbstring`, `fileinfo`, `json`
(présentes par défaut presque partout).

---

## 2. Les fichiers à envoyer

Envoie le contenu du dossier par FTP/SFTP, **sauf** :

| À exclure | Pourquoi |
| --- | --- |
| `apercus/` | 6,5 Mo de captures d'écran, inutiles en ligne |
| `src/` | sources CSS (le site utilise `dist/output.css`) |
| `*.md` (76 fichiers) | documentation de travail |
| `tools/` | scripts de maintenance (tests, audit, réinitialisation) |
| `database/` | scripts SQL : à garder **sur ton ordinateur** |
| `includes/config.local.php` | configuration locale ; ne doit pas partir |

> Le fichier `.htaccess` bloque déjà ces fichiers s'ils sont envoyés, mais mieux
> vaut ne pas les mettre en ligne du tout.

**À envoyer impérativement :**

```
*.php  .htaccess  assets/  dist/  includes/  data/  favicon.ico  manifest.webmanifest
```

> `data/` contient la table de localisation des visiteurs (128 Ko). Sans elle,
> la page « Trafic » fonctionne mais tous les pays apparaissent « Inconnu ».

### FTP : « nombre maximum de connexions atteint »

Ce message vient de l'hébergeur, pas du site : le client FTP ouvre plusieurs
connexions en parallèle et dépasse la limite autorisée (souvent 3 à 5 par
utilisateur).

**1. Réduire le parallélisme dans le client**

| Client | Réglage |
| --- | --- |
| **FileZilla** | *Édition → Paramètres → Transferts* → **Transferts simultanés maximum : 1**<br>Puis *Fichier → Gestion des sites → Transfert* → **Limiter le nombre de connexions simultanées à 1** |
| **Cyberduck** | *Préférences → Transferts* → **Transfers : 1** |
| **Transmit** | *Préférences → Transferts* → réduire les transferts simultanés |

**2. Attendre 10 à 15 minutes** : l'hébergeur libère les sessions expirées
seulement au bout d'un délai.

**3. Fermer les autres clients FTP** et les fenêtres de navigation en FTP.

**4. Utiliser le mode passif** (réglage par défaut) et désactiver la
reconnexion automatique.

**5. Passer en SFTP (port 22)** si l'hébergeur le propose : la limite est
souvent distincte, et la connexion est chiffrée.

### La solution la plus simple : un seul fichier ZIP

Envoyer une centaine de fichiers en FTP est la cause principale du problème.
Le projet génère une archive unique prête à envoyer :

```bash
php tools/build-deploy.php
```

→ crée **`site-a-envoyer.zip`** (≈ 4 Mo, **105 fichiers**) contenant uniquement
ce qui sert en production.

**Procédure :**

1. envoie `site-a-envoyer.zip` à la racine du site (un seul transfert) ;
2. **décompresse-le** avec le gestionnaire de fichiers de l'hébergeur
   (cPanel → *Gestionnaire de fichiers* → clic droit → *Extraire*), ou via une
   page PHP temporaire ;
3. **supprime l'archive** du serveur ensuite ;
4. édite `includes/config.php` avec les identifiants MySQL.

> L'archive **exclut** automatiquement : `includes/config.local.php`
> (identifiants locaux), `apercus/`, `src/`, `tools/`, `database/` et tous les
> `.md`.

### Voir le fichier `.htaccess` sur Mac

macOS **cache tous les fichiers qui commencent par un point** : le `.htaccess`
est bien présent, il n'apparaît simplement pas.

| Méthode | Comment |
| --- | --- |
| **Finder** | `⌘ ⇧ .` (point) affiche / masque les fichiers cachés |
| **Aller au dossier** | `⌘ ⇧ G` puis coller le chemin exact |
| **Terminal** | `ls -la` dans le dossier du site |
| **Affichage permanent** | `defaults write com.apple.finder AppleShowAllFiles -bool true` puis `killall Finder` |

Dans un client FTP, active l'affichage des fichiers cachés :

- **Cyberduck** : menu *Affichage* → *Afficher les fichiers cachés* ;
- **FileZilla** : menu *Serveur* → *Forcer l'affichage des fichiers cachés* ;
- **Transmit** : `⌘ ⇧ .` également.

> Le nom doit rester **exactement** `.htaccess` — sans `.txt` ni autre extension.
> Certains gestionnaires de fichiers d'hébergeur le renomment : vérifie après
> l'envoi.

---

## 3. Créer la base de données

1. Dans le panneau de l'hébergeur, crée une base MySQL et un utilisateur.
   Note : **hôte**, **nom de la base**, **utilisateur**, **mot de passe**.
2. Ouvre **phpMyAdmin** sur cette base.
3. Onglet **Importer** → choisis `database/schema.sql` → **Exécuter**.
   Cela crée les 22 tables.
4. *(facultatif)* Importe `database/seed.sql` pour les 41 chapitres de départ.

---

## 4. Renseigner la configuration

Ouvre `includes/config.php` (éditeur de fichiers de l'hébergeur) et mets :

```php
<?php
return [
    'db_host' => 'sql123.mon-hebergeur.com',   // hôte fourni
    'db_name' => 'mg_cours',                    // nom de la base
    'db_user' => 'mg_cours',                    // utilisateur
    'db_pass' => 'LE_MOT_DE_PASSE_FOURNI',
    'base_url' => '',                           // laisser vide
    'site_name' => 'MG',
];
```

> `base_url` reste vide : les chemins sont relatifs, le site fonctionne aussi
> bien à la racine du domaine que dans un sous-dossier.
>
> Le port est optionnel : sur un mutualisé, ne pas mettre `db_port` (3306 par
> défaut). Sur MAMP, il faut `'db_port' => 8889`.

---

## 5. Créer le compte administrateur

1. Ouvre `https://ton-domaine.fr/admin/setup.php`
2. Renseigne ton adresse e-mail et un mot de passe de **12 caractères minimum**.
3. Tu es redirigé vers le tableau de bord.

> Cette page ne fonctionne **que s'il n'existe aucun administrateur**. Dès que
> le compte est créé, elle renvoie vers la connexion.

---

## 5 bis. Si le CSS ne s'applique pas (page « nue »)

Ouvre **`https://ton-domaine.fr/diag.php`** : la page teste 35 points et indique
en rouge ce qui bloque. Regarde en priorité :

| Vérification | Si elle échoue | Correction |
| --- | --- | --- |
| `dist/output.css` | fichier absent | le dossier `dist/` n'a pas été envoyé ou l'archive a été extraite dans un sous-dossier |
| CSS servi en HTTP | **403** | droits du fichier : `644` (fichiers) et `755` (dossiers) |
| CSS servi en HTTP | **404** | mauvais dossier : le site doit être à la racine où se trouve `index.php` |
| URL du CSS demandée par la page | 404 | compare l'adresse affichée avec l'emplacement réel du fichier |
| Type MIME du CSS | `text/plain` | ajoute `AddType text/css .css` dans `.htaccess` |

### Cas signalé : `MIME type ('text/html')` sur le CSS, `403` sur le JS

Le serveur ne sert pas les fichiers statiques. Deux causes possibles, toutes
deux côté hébergement :

**1. Les droits des fichiers** — après une extraction de ZIP, les fichiers
peuvent se retrouver en `600` : Apache, qui tourne sous un autre compte, ne peut
pas les lire et répond `403` (avec une page HTML, d'où l'erreur de type MIME).

*Via le gestionnaire de fichiers* : sélectionne tout → **Permissions** →
`755` pour les dossiers, `644` pour les fichiers.

*Via SSH :*

```bash
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
```

**Correction automatique** — envoie `fix-perms.php` à la racine et ouvre :

```
https://ton-domaine.fr/fix-perms.php          → simulation (rien n'est modifié)
https://ton-domaine.fr/fix-perms.php?go=1     → applique 755 / 644
```

La page affiche le nombre de fichiers à corriger, applique les droits, puis
vérifie les fichiers sensibles. **Supprime-la ensuite.**

**2. Le dossier `dist/` n'existe pas** — l'archive a été extraite dans un
sous-dossier. Vérifie que `dist/` et `assets/` sont au même niveau que
`index.php`.

**Vérification en trois commandes** (depuis le Mac) :

```bash
curl -I "https://ton-domaine.fr/dist/output.css"
curl -I "https://ton-domaine.fr/assets/js/mobile-menu.js"
curl -s "https://ton-domaine.fr/dist/output.css" | head -c 200
```

| Réponse | Signification |
| --- | --- |
| `200` + `Content-Type: text/css` | le fichier est correctement servi |
| `403` | droits du fichier (`chmod 644`) |
| `404` | fichier absent : mauvais dossier |
| `200` + `text/html` dans le corps `<html>…` | une page d'erreur est renvoyée à la place du fichier |

**Deux causes fréquentes qui ne viennent pas du serveur :**

1. **le cache du navigateur** — recharge avec `⌘⇧R` (Mac) ou `Ctrl+F5` ;
2. **le service worker** — il garde l'ancien CSS. Dans les outils de
   développement → *Application* → *Service workers* → **Unregister**, puis
   recharge.

> Supprime `diag.php` après le diagnostic.

## 6. Vérifier que tout fonctionne

À faire dans l'ordre, depuis un navigateur :

- [ ] la page d'accueil s'affiche, le logo **MG** apparaît ;
- [ ] les niveaux s'ouvrent, un chapitre s'affiche ;
- [ ] `/admin/login.php` : connexion avec ton compte ;
- [ ] le tableau de bord affiche les chiffres (chapitres, exercices…) ;
- [ ] créer un chapitre de test, le publier, vérifier qu'il apparaît sur le site ;
- [ ] `/robots.txt` et `/sitemap.xml` répondent ;
- [ ] `http://` redirige bien vers `https://`.

Côté serveur, si tu as un accès SSH :

```bash
php tools/tests.php            # 49 vérifications
php tools/audit.php https://ton-domaine.fr   # pages, liens, ressources
```

---

## 7. Sécurité après la mise en ligne

| Action | Pourquoi |
| --- | --- |
| **Supprimer `tools/` et `database/`** du serveur | ils ne servent plus en ligne |
| Vérifier que `https://ton-domaine.fr/includes/config.php` renvoie **403** | le fichier n'est jamais servi, mais vérifie |
| Activer **HTTPS** et la redirection | les mots de passe circulent sinon en clair |
| Mettre un mot de passe **long** pour l'admin | seule protection de l'espace enseignant |
| Sauvegarder la base régulièrement | export SQL depuis phpMyAdmin |

---

## 7 bis. Mettre à jour uniquement les fichiers modifiés

Après une évolution du site, inutile de tout renvoyer. L'archive
**`mise-a-jour.zip`** contient les seuls fichiers qui ont changé depuis la
version précédente (12 fichiers, 118 Ko).

**Procédure :**

1. envoyer `mise-a-jour.zip` à la racine du site ;
2. l'extraire (gestionnaire de fichiers ou `unzip mise-a-jour.zip`) — les
   fichiers sont **remplacés** au bon endroit ;
3. **supprimer l'archive** ;
4. ouvrir `https://ton-domaine.fr/fix-perms.php?go=1` (droits 644/755) ;
5. recharger le site avec `⌘⇧R`.

**Ne jamais envoyer `includes/config.local.php`** : ce fichier contient les
identifiants de développement (base locale sur le port 8889). En ligne, c'est
`includes/config.php` qui sert.

### Trois archives, trois usages

| Archive | Contenu | Quand l'utiliser |
| --- | --- | --- |
| `mise-a-jour.zip` | **15 fichiers** modifiés (129 Ko) | mise à jour courante, la plus rapide |
| `site-complet.zip` | **107 fichiers** (4 Mo), **sans `includes/config.php`** | « je renvoie tout » sans risque : tes identifiants MySQL sont préservés |
| `site-a-envoyer.zip` | **107 fichiers** avec `config.php` d'exemple | **première installation seulement** |

> ⚠️ Sur un site **déjà en ligne**, ne pas envoyer `site-a-envoyer.zip` : son
> `includes/config.php` contient `db_name = mysql` et remplacerait tes vrais
> identifiants. Utiliser `site-complet.zip`.

Régénération : `php tools/build-deploy.php` (ou `--sans-config` pour obtenir
`site-complet.zip`).

## 8. Mettre à jour le site plus tard

1. Envoie les fichiers modifiés.
2. **Incrémente la version** dans `includes/helpers.php`
   (`dist/output.css?v=…`) et `service-worker.js` (`CACHE_NAME`).
   Sans cela, les navigateurs gardent l'ancien CSS en cache.
3. Recharge une page avec **⌘⇧R** pour forcer la mise à jour.

---

## 9. Formules mathématiques : aucune dépendance externe

KaTeX est **hébergé sur le site**, dans `assets/vendor/katex/` :

```
assets/vendor/katex/
├── katex.min.css        (styles, polices en woff2 uniquement)
├── katex.min.js         (moteur de rendu)
├── auto-render.min.js   (rendu automatique des $…$)
└── fonts/               (20 fichiers .woff2, ~600 Ko)
```

Le site n'appelle plus aucun CDN : il fonctionne même si le réseau de
l'établissement bloque les domaines externes.

**À vérifier après l'envoi** : que le dossier `assets/vendor/katex/` est bien
complet (sinon les formules s'affichent en texte brut).

**Après une mise à jour de KaTeX** : remplacer ces fichiers, puis incrémenter la
version dans `includes/helpers.php`.
