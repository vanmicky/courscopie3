# V92 — Recherche par niveau en direct

La recherche de l'accueil devait filtrer **à la frappe**, comme celle d'une page
niveau : on tape, les cartes disparaissent, sans appuyer sur Entrée.

## Le diagnostic

Le JavaScript faisait déjà son travail : au `input`, il passait bien les cartes
non correspondantes à `hidden = true` et mettait le compteur à jour
(« 1 résultat »). Mais **aucune carte ne disparaissait**.

Cause : sur l'accueil, `.editorial-home .level-card` impose
`display: flex !important` (couche des pages, spécificité 0,2,0). La règle
globale `[hidden] { display: none !important }` a une spécificité inférieure
(0,1,0) : elle perd, et la carte reste affichée malgré l'attribut `hidden`.

Sur une page niveau, `.chapter-tile` n'a pas de `display` forcé : `hidden`
fonctionnait, d'où l'impression que seule la recherche par niveau était cassée.

## La correction

Règle ajoutée en fin de cascade, avec une spécificité supérieure à celle qui
imposait `display: flex` :

```css
.editorial-home .level-card[hidden],
.editorial-home .level-card.hidden,
.chapter-tile[hidden],
.chapter-tile.hidden,
[data-card][hidden],
[data-card].hidden { display: none !important; }
```

Elle couvre les deux pages et tout futur élément `data-card`.

## Vérifications fonctionnelles

**Accueil** (6 niveaux) :

| Action | Résultat mesuré |
| --- | --- |
| état initial | 6 cartes visibles |
| saisie « cinquieme » | **1 carte visible : « Cinquième »** (accent ignoré) |
| effacement | 6 cartes visibles, compteur « 6 résultats » |

**Niveau** (6 chapitres) :

| Action | Résultat mesuré |
| --- | --- |
| saisie « atome » | 1 chapitre : « Atomes, ions et molécules » |
| saisie « zzzz » | 0 chapitre, message « aucun résultat » affiché |

Aucun appui sur Entrée n'est nécessaire, et aucun formulaire ne recharge la
page. Contraste AA inchangé en clair et en sombre, 18 pages en HTTP 200,
`php -l` et `node --check` sans erreur.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=92`, cache service worker
`cours-gedion-v92`.
