# V99 — En-tête des pages d'administration corrigé

## Le défaut

Sur toutes les pages de l'administration, le titre était **rogné par une barre
noire verticale** et la description se retrouvait **rejetée à l'extrême droite**,
sur la même ligne que le titre.

Deux causes cumulées :

1. la V97 a supprimé le rembourrage du bandeau d'en-tête, mais la **barre
   d'accent de 4 px** (`.work-global-hero::after`, héritée du site public)
   restait affichée : elle passait par-dessus le titre ;
2. l'en-tête était en `display: flex` avec `justify-content: space-between` :
   le titre et la description devenaient deux colonnes, la description étant
   poussée à droite.

## La correction

- la barre décorative est **supprimée** dans l'administration
  (`::after { display: none }`) ;
- l'en-tête est **empilé verticalement** : titre, puis description sous lui,
  tous deux sur le même axe à gauche.

## Mesures après correction

| Page | `display` | Titre | Description | Même axe | Barre |
| --- | --- | --- | --- | --- | --- |
| `methodes.php` | `block` | (24, 148) | (24, 176) | oui | absente |
| `matieres.php` | `block` | (24, 148) | (24, 176) | oui | absente |
| `chapitres.php` | `block` | (24, 148) | (24, 176) | oui | absente |

Aucun débordement du titre hors du bandeau.

## Avertissement « la table methods n'existe pas »

Il ne s'affiche plus : la table existe et `methods_available()` renvoie `true`.
Le message de la capture datait d'avant la configuration de la base
(`includes/config.local.php`).

> Si un ancien message persiste dans le navigateur, forcer le rechargement
> (**⌘⇧R**) : les anciens fichiers CSS/JS sont en cache.

## Vérifications

- en-tête conforme sur 3 pages mesurées ; aucun débordement ;
- **contraste AA en clair et en sombre** sur `methodes.php`, `matieres.php` et le
  tableau de bord : aucune alerte ;
- aucune occurrence de « n'existe pas » sur les pages d'administration ;
- CSS servi avec `?v=99`, cache service worker `cours-gedion-v99`.
