# V117 — Diagnostic de déploiement (`diag.php`)

Symptôme signalé après envoi sur le serveur : **le CSS ne s'applique pas**.

## Le problème

À distance, impossible de savoir *pourquoi* : fichier absent, droits refusés,
mauvais dossier, type MIME incorrect, cache du navigateur… La cause la plus
fréquente (dossier `dist/` non envoyé) ressemble à toutes les autres.

## La solution : une page de diagnostic

Nouveau fichier **`diag.php`**, à ouvrir une fois le site en ligne :

```
https://ton-domaine.fr/diag.php
```

Il exécute **35 vérifications** et affiche en rouge celles qui échouent, avec la
correction à appliquer :

| Famille | Contrôles |
| --- | --- |
| Environnement | version de PHP, extensions `pdo_mysql`, `mbstring`, `fileinfo` |
| Configuration | fichier de config trouvé, nom de base renseigné, `base_url` |
| Fichiers | `.htaccess`, `index.php`, `dist/output.css`, `main.js`, `includes/*`, KaTeX, `data/` |
| **CSS** | présence, taille, droits, lecture, **accès HTTP réel**, **type MIME** |
| **URL exacte** | le lien `<link>` que la page écrit (`./dist/output.css?v=…`) est appelé tel quel et son code HTTP est affiché |
| Page d'accueil | aucune erreur PHP |
| Écriture | `assets/` accessible en écriture |
| Base | connexion, nombre de tables, tables clés, présence d'un administrateur |
| Serveur | HTTPS, page 404 personnalisée |

## Vérifié en local

```
35 vérifications — 34 OK
✗ HTTPS actif (normal en local)
```

Contrôles CSS :

```
✓ CSS lisible par PHP           lecture ok
✓ CSS non vide                  458 255 octets
✓ CSS servi en HTTP             HTTP 200
✓ Type MIME du CSS              text/css
✓ URL du CSS demandée par page  ./dist/output.css?v=115 → HTTP 200, 458 255 octets
```

## Les deux causes les plus fréquentes

1. **Le cache du navigateur** → `⌘⇧R` (Mac) ou `Ctrl+F5`.
2. **Le service worker** qui garde l'ancien CSS → outils de développement →
   *Application* → *Service workers* → **Unregister**.

Côté serveur, un **403** signifie des droits incorrects (`644` pour les
fichiers, `755` pour les dossiers) ; un **404** signifie que le dossier `dist/`
n'est pas au bon endroit.

## Mise à niveau

`diag.php` est ajouté automatiquement à `site-a-envoyer.zip` (106 fichiers).

> **Supprime `diag.php` après le diagnostic** : il expose des informations sur
> ta configuration.
