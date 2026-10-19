# V114 — KaTeX hébergé en local

Demande : « rapatrier KaTeX en local ».

## Le problème

Les formules mathématiques étaient rendues via un **CDN externe**
(`cdn.jsdelivr.net`). Deux conséquences :

- si le réseau de l'établissement bloque les CDN, **plus aucune formule** ne
  s'affiche ;
- le site dépendait d'un service tiers pour une fonction essentielle.

## Ce qui a été fait

Les fichiers sont désormais servis par le site :

```
assets/vendor/katex/
├── katex.min.css        23 Ko   (styles)
├── katex.min.js        277 Ko   (moteur)
├── auto-render.min.js    3 Ko   (rendu des $…$)
└── fonts/              596 Ko   (20 polices .woff2)
```

- les 20 polices ont été téléchargées en **woff2 uniquement** (format lu par
  tous les navigateurs actuels) ;
- le CSS a été réécrit : **60 références de police → 20**, plus aucune référence
  à un fichier absent (les formats `woff` et `ttf` ont été retirés) ;
- `includes/helpers.php` pointe vers `assets/vendor/katex/…` via `base_path()` :
  cela fonctionne à la racine du domaine comme dans un sous-dossier ;
- le cache `.htaccess` applique déjà un an aux `.woff2`.

**Plus aucune référence à `cdn.jsdelivr.net` dans le code.**

## Vérifications dans le navigateur

| Contrôle | Résultat |
| --- | --- |
| Fichiers servis | CSS, JS et polices en **HTTP 200** |
| `window.katex` | chargé |
| `renderMathInElement` | disponible |
| Formules rendues | **4**, aucune en erreur |
| Police réellement chargée | `KaTeX_Main : loaded` |
| Feuilles de style | `output.css` + `katex.min.css` (locale) |

L'audit vérifie maintenant aussi les **polices déclarées dans le CSS** :
`52 ressources contrôlées, aucune manquante`.

## Mise à niveau

Aucune migration SQL. CSS `?v=114`, cache service worker `cours-gedion-v114`.
Nouveau dossier à envoyer lors du déploiement : `assets/vendor/`.
