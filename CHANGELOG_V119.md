# V119 — Correction automatique des permissions

## Le diagnostic (relevé sur le serveur)

`https://cours.mgedion.com/diag.php` a désigné la cause sans ambiguïté :

| Fichier | Droits | Résultat |
| --- | --- | --- |
| `dist/output.css` | **0600** | **HTTP 403** → type `text/html` (page d'erreur) |
| `assets/js/mobile-menu.js` | **0600** | **HTTP 403** |
| `assets/js/main.js` | 0644 | HTTP 200 ✓ |
| `assets/fonts/inter-var-latin.woff2` | 0644 | HTTP 200 ✓ |
| `assets/vendor/katex/katex.min.css` | 0644 | HTTP 200 ✓ |
| `data/geoip-country.bin` | 0644 | HTTP 200 ✓ |

**Conclusion** : ce n'est pas un problème de type MIME ni de dossier manquant.
Les fichiers en `600` sont illisibles pour Apache, qui répond 403 avec une page
HTML — d'où le message trompeur « MIME type text/html ».

## La cause

Les fichiers avaient ces droits **sur le poste de développement** (`600`), et
l'archive ZIP les a conservés. Sur un hébergement mutualisé, Apache tourne sous
un autre compte : 644 est le minimum.

## Ce qui a été livré

### `fix-perms.php`

Page à ouvrir depuis le navigateur :

```
https://ton-domaine.fr/fix-perms.php          → simulation
https://ton-domaine.fr/fix-perms.php?go=1     → applique 755 / 644
```

- parcourt tout le site (dossiers + fichiers) ;
- affiche le nombre d'éléments à corriger **avant** de modifier quoi que ce soit ;
- applique `755` aux dossiers et `644` aux fichiers ;
- vérifie ensuite les fichiers sensibles (`dist/output.css`, `main.js`,
  `mobile-menu.js`, `includes/config.php`) ;
- signale les refus et propose la méthode manuelle si `chmod()` est interdit.

Test local : **76 éléments corrigés**, `dist/output.css` passé de `600` à `644`,
site toujours fonctionnel (200 sur 4 pages).

### Droits corrigés à la source

Les fichiers du projet sont désormais en `644` / `755` : **les prochaines
archives ne reproduiront plus le problème**.

## Deuxième point relevé par le diagnostic

```
✗ Nom de la base renseigné      mysql
✗ Connexion MySQL               échec
```

`includes/config.php` contient encore les identifiants d'exemple. Le site
s'affiche parce qu'il retombe sur les données de démarrage, mais
**l'administration est vide**. Il faut :

1. créer une base MySQL chez l'hébergeur ;
2. importer `database/schema.sql` dans phpMyAdmin ;
3. renseigner `db_host`, `db_name`, `db_user`, `db_pass` dans
   `includes/config.php`.

## Mise à niveau

Envoyer `fix-perms.php` (7 Ko) et l'ouvrir avec `?go=1`, puis supprimer le
fichier. Archive régénérée : **107 fichiers**, droits `644/755`.
