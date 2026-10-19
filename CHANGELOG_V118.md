# V118 — Types MIME explicites et diagnostic des fichiers statiques

Cas rencontré sur `https://cours.mgedion.com` :

```
Refused to apply style from '.../dist/output.css?v=115'
  because its MIME type ('text/html') is not a supported stylesheet MIME type
.../assets/js/mobile-menu.js  → 403
```

## Analyse

Le serveur **ne sert pas les fichiers statiques** :

- le CSS reçoit une **page HTML** à la place du fichier (d'où le type
  `text/html`) ;
- le JS reçoit un **403**.

Le HTML des pages, lui, fonctionne : PHP s'exécute. C'est donc un problème
d'accès aux **fichiers statiques**, pas du site.

Deux causes possibles, toutes deux côté hébergement :

1. **droits des fichiers** — une extraction d'archive laisse souvent les
   fichiers en `600`, illisibles pour Apache qui tourne sous un autre compte ;
2. **dossier `dist/` absent** — archive décompressée dans un sous-dossier.

## Ce qui a été ajouté

### `.htaccess` : types MIME explicites

```apache
<IfModule mod_mime.c>
  AddType text/css .css
  AddType application/javascript .js
  AddType application/manifest+json .webmanifest
  AddType font/woff2 .woff2
  AddType image/svg+xml .svg
</IfModule>
```

Certains hébergements servent le CSS en `text/plain` : le navigateur refuse
alors la feuille de style même si le fichier est correct.

### `diag.php` : batterie de fichiers statiques

Six fichiers sont désormais testés un par un, avec **les droits sur le disque**,
le **code HTTP** et le **type MIME** reçus :

| Fichier | Contrôlé |
| --- | --- |
| `dist/output.css` | présent, mode, HTTP, MIME |
| `assets/js/main.js` | idem |
| `assets/js/mobile-menu.js` | idem |
| `assets/fonts/inter-var-latin.woff2` | idem |
| `assets/vendor/katex/katex.min.css` | idem |
| `data/geoip-country.bin` | idem |

Chaque échec affiche la correction : « droits refusés → 644/755 », « fichier
absent → mauvaise extraction », « page HTML renvoyée → droits ou réécriture ».

## Correction à appliquer sur le serveur

```bash
find . -type d -exec chmod 755 {} \\;
find . -type f -exec chmod 644 {} \\;
```

Ou, via le gestionnaire de fichiers : sélectionner tout → **Permissions** →
`755` (dossiers) / `644` (fichiers).

## Mise à niveau

`.htaccess` et `diag.php` modifiés → **renvoyer ces deux fichiers**, puis
recharger `diag.php`.
