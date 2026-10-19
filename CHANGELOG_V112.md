# V112 — Marque MG, audit complet et préparation à la mise en ligne

## 1. Le site ne porte plus qu'un logo MG

- **en-tête** : le bloc de texte (« Les cours de M. Gedion » + matières) est
  supprimé, il ne reste que la marque **MG** ;
- **pied de page** : le nom et la **version** sont retirés, il ne reste que
  `© 2026` ;
- **titre des pages** : « Accueil - MG » ;
- **manifeste PWA** : nom `MG` ;
- l'accroche de la page d'accueil devient « Physique-Chimie · Maths · NSI ».

Vérifié dans le HTML servi : « Les cours de M. Gedion » n'apparaît plus.

## 2. Audit complet du site — nouvel outil

`php tools/audit.php [url]` visite le site et contrôle :

- toutes les **pages publiques** (déduites de la base : niveaux, chapitres,
  méthodes, QCM, séries de cartes) ;
- les **erreurs PHP** visibles dans le HTML ;
- les **liens internes** (188 vérifiés) ;
- les **ressources** CSS, JS, images, polices (29 vérifiées) ;
- les pages lentes (> 2,5 s).

**Résultat sur le site actuel :**

```
Pages conformes      : 71 / 71
Liens internes       : 188 — aucun cassé
Ressources           : 29 — aucune manquante
Problème détecté     : aucun
```

## 3. Pages d'administration

Les **17 pages** d'administration et l'éditeur de chapitre répondent en HTTP 200
sans aucune erreur PHP.

## 4. Préparation de la mise en ligne

Nouveau document **`DEPLOIEMENT.md`** : hébergement requis, fichiers à envoyer
et à exclure, import de la base, configuration, création du compte admin,
vérifications, sécurité après déploiement, procédure de mise à jour.

Le `.htaccess` protège déjà : `config.local.php`, `*.sql`, `*.md`, `*.zip`
(réponse **403** vérifiée), compression, cache, en-têtes de sécurité, page 404.

## Bugs trouvés pendant ce passage

| Constat | Suite |
| --- | --- |
| 3 liens `examen.php` signalés en 404 | **faux positif de l'outil** (entités `&amp;` non décodées) — corrigé dans `tools/audit.php`, les liens réels répondent en 200 |

Aucun autre défaut détecté sur les pages publiques, les liens, les ressources et
l'administration.

## Mise à niveau

Aucune migration SQL. CSS `?v=112`, cache service worker `cours-gedion-v112`.
Aperçus : `apercus/accueil-logo-mg.jpg`, `pied-de-page.jpg`.
