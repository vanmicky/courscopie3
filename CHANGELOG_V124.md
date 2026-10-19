# V124 — Service worker : plus jamais d'ancien fichier bloqué

## Le problème constaté

« Je supprime les flashcards, ça ne marche pas. »
Puis : **« j'ai supprimé le cache et ça a marché »**.

Cela confirme la cause : le **service worker** servait une **ancienne version du
JavaScript** (`chapter-workspace.js`). Le fichier corrigé était bien sur le
serveur, mais le navigateur ne l'utilisait pas — le bouton « Supprimer »
restait donc inopérant.

## Pourquoi

L'ancien service worker appliquait la règle « **cache d'abord** » à *tout* :

```js
caches.match(req).then(cached => cached || fetch(req))   // cache d'abord
```

Un fichier déjà mis en cache n'était ** jamais rechargé**, même quand il changeait
sur le serveur. Il fallait vider le cache à la main — ce que personne ne fait.

## La nouvelle stratégie

| Contenu | Stratégie |
| --- | --- |
| Pages HTML / PHP | **jamais en cache** — réseau d'abord, page hors-ligne en secours |
| JS et CSS | **réseau d'abord**, cache en secours → une correction s'applique au rechargement suivant |
| Polices, images | cache d'abord (elles changent rarement) |
| `/admin/` | **jamais mis en cache** |

Le préchargement obligatoire passe de 22 fichiers à **6** (l'essentiel
hors-ligne). Le nom du cache change (`cours-gedion-v124`), donc l'ancien est
supprimé automatiquement à l'activation.

## Deuxième bug corrigé

`tools/build-deploy.php` n'incluait pas les fichiers `.js` de la racine : le
**`service-worker.js` était absent** des archives `site-complet.zip` et
`site-a-envoyer.zip`. Corrigé — les trois archives le contiennent désormais.

## Vérifications

| Contrôle | Résultat |
| --- | --- |
| `node --check service-worker.js` | sans erreur |
| Service worker enregistré et actif | oui (portée `/cours/`) |
| Service worker dans les 3 archives | `cours-gedion-v124` partout |
| Suppressions en HTTP | **2 → 1 → 0 carte** (302 à chaque étape) |
| Versions | `output.css?v=124`, `chapter-workspace.js?v=124`, cache `v124` |

## À retenir

Après cette mise à jour, **plus besoin de vider le cache** : un fichier corrigé
est pris en compte dès le rechargement de la page.

## Mise à niveau

Trois archives régénérées :

| Archive | Fichiers | Usage |
| --- | --- | --- |
| `mise-a-jour.zip` | 15 | mise à jour rapide |
| `site-complet.zip` | 108 | renvoyer tout, **sans** `includes/config.php` |
| `site-a-envoyer.zip` | 109 | première installation |
