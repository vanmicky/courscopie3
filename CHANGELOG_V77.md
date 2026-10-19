# V77 — Refonte éditoriale chaleureuse

Direction : quitter l'allure « tableau de bord SaaS » (fond gris-bleu, tout en
Inter, cartes blanches impersonnelles) pour une **identité éditoriale chaleureuse**,
proche d'un carnet de cours soigné.

## Ce qui change visuellement

- **Palette chaude** : fond crème `#f6f2e8`, encre profonde `#1d1a24`, surfaces
  ivoire, ombres teintées de brun (au lieu du gris-bleu froid).
- **Titres en Fraunces** : un serif à fort caractère, auto-hébergé (latin + latin-ext,
  ~124 Ko), appliqué aux titres de page, titres de section et hiérarchie des héros.
  Le corps reste en Inter pour la lisibilité.
- **Héros agrandis** : titres jusqu'à 3,15 rem (au lieu de 2,75 rem), interligne serré.
- **Surfaces adoucies** : rayons relevés (14 / 20 / 26 px), halo chaud indigo/ambre
  derrière l'en-tête.
- **Cohérence** : le mode sombre reste froid et lisible, avec une encre légèrement
  réchauffée.

## Accessibilité

Le fond crème a fait ressortir un texte secondaire trop clair (`--ds-text-faint`) :
assombri à `#6b6559` pour repasser au-dessus du seuil AA. Audit de contraste complet
(clair et sombre) sur accueil, niveau, chapitre, QCM, flashcards, méthodes, fiche
méthode, travail, examen, minuteur, exercices : aucune alerte.

## Fichiers

- `src/ds-5-warm.css` : nouvelle couche (jetons chauds + titres Fraunces).
- `src/ds-1-base.css` : ajout des `@font-face` Fraunces et du jeton `--ds-font-display`.
- `assets/fonts/fraunces-var-latin.woff2`, `fraunces-var-latin-ext.woff2`.
- `tools/build-css.sh` : assemble désormais 6 sources.

## Réglages rapides si tu veux ajuster

Tout se pilote depuis `src/ds-5-warm.css` :

| Ce que tu veux changer | Où |
| --- | --- |
| Plus / moins crème | `--ds-bg`, `--ds-surface` |
| Couleur de marque | `--ds-brand`, `--ds-brand-strong` |
| Taille des titres | le `clamp()` de `.page-title` / `.home-title` |
| Serif ou pas | la règle `h1, h2, h3 { font-family: var(--ds-font-display) }` |

## Vérifications

`php -l` / `node --check` sans erreur · pages publiques et administration en HTTP 200 ·
contraste AA clair et sombre · aperçus dans `apercus-v77/`.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=77`, cache service worker `cours-gedion-v77`.
