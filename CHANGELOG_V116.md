# V116 — Déploiement en un seul envoi (FTP saturé)

Problème rencontré : « nombre maximum de connexions FTP atteint ».

## La cause

Le site représente **105 fichiers utiles** (dont 73 dans `assets/` et 20 polices
KaTeX). Un client FTP ouvre plusieurs connexions en parallèle : l'hébergeur
coupe au-delà de 3 à 5 connexions simultanées par utilisateur.

## La solution : une archive unique

Nouveau script :

```bash
php tools/build-deploy.php
```

Il produit **`site-a-envoyer.zip`** (3,9 Mo, **105 fichiers**) contenant
uniquement ce qui sert en production :

| Inclus | Exclu |
| --- | --- |
| tous les `.php` de la racine | `includes/config.local.php` (identifiants locaux) |
| `.htaccess`, `favicon.ico`, `manifest.webmanifest` | `apercus/` (captures) |
| `assets/`, `dist/`, `includes/`, `data/` | `src/`, `tools/`, `database/`, `*.md`, `*.sql` |

**Procédure** : envoyer l'archive (un transfert), la décompresser via le
gestionnaire de fichiers de l'hébergeur, la supprimer, puis renseigner
`includes/config.php`.

## Vérifications de l'archive

| Contrôle | Résultat |
| --- | --- |
| Intégrité ZIP | `unzip -t` : **aucune erreur** |
| Fichiers | 105 inclus, 88 ignorés |
| Fichiers sensibles | `config.local.php`, `config.example.php`, `*.md`, `*.sql`, `src/`, `tools/`, `database/`, `apercus/` : **tous absents** |
| Structure après extraction | `.htaccess`, `index.php`, `includes/*`, `dist/output.css`, `assets/vendor/katex/`, `data/geoip-country.bin` : **tous présents** |
| Syntaxe PHP des fichiers extraits | `php -l` : **aucune erreur** |

## Réglages FTP conseillés (en complément)

- **transferts simultanés maximum : 1** (FileZilla, Cyberduck, Transmit) ;
- mode **passif**, reconnexion automatique désactivée ;
- attendre **10 à 15 minutes** après un blocage (sessions expirées) ;
- préférer **SFTP (port 22)** si disponible.

Tout est détaillé dans `DEPLOIEMENT.md` (section « FTP : nombre maximum de
connexions atteint »).
