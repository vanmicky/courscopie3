# Design system V91

La feuille servie par le site est générée par `tools/build-css.sh` à partir de quatre
sources. La couche historique (`src/legacy-v73.css`) n'est pas modifiée : elle assure la
compatibilité des pages statiques. Tout le reste vient de V74.

```
src/ds-1-base.css        jetons, remise à zéro, typographie, utilitaires
src/ds-2-components.css  boutons, cartes, formulaires, tableaux, états, lecture
src/ds-3-pages.css       pages publiques, administration, outils, impression
src/ds-4-modern.css      V76 : dégradés, profondeur, héros, cartes modernes
src/ds-5-warm.css        V77 : palette crème chaude + titres Fraunces
                         V78 : motif de fond sur les pages intérieures (body.page-inner)
src/ds-6-reading.css     V79 : lecture longue (fiches méthodes : colonne unique, sommaire, encadrés)
src/ds-7-tabs.css        V80 : onglets horizontaux des pages chapitre
src/ds-8-outils.css      V81 : outils de classe (minuteur, groupes, tirage, consignes, dés)
```

## Principes

- une surface structure, elle ne décore pas ;
- la couleur du niveau est un **accent**, jamais un aplat porteur de texte ;
- l'indigo (`--ds-brand`) identifie les actions générales et l'administration ;
- vert, ambre et rouge sont réservés aux états réussite / vigilance / erreur ;
- une seule échelle typographique, fluide entre 390 px et 1440 px ;
- les animations restent discrètes et ne déplacent jamais la mise en page.

## Jetons

| Famille | Jetons |
| --- | --- |
| Icônes | `includes/icons.php` : 32 symboles SVG 24×24 en `currentColor`, sprite inséré une fois par page |
| Typographie | texte : **Inter** ; titres : **Krub** — jetons `--ds-font-sans`, `--ds-font-display`, `--ds-font-mono` |
| Gris | `--ds-grey-0` (blanc) à `--ds-grey-950` (quasi noir), 14 paliers |
| Couleurs | `--ds-bg`, `--ds-surface`, `--ds-surface-2`, `--ds-text`, `--ds-text-soft`, `--ds-text-faint`, `--ds-border`, `--ds-border-strong` |
| Marque | `--ds-brand`, `--ds-brand-strong`, `--ds-brand-soft`, `--ds-brand-ink` |
| États | `--ds-success`, `--ds-warning`, `--ds-danger`, `--ds-info` (+ variantes `-soft`, `-ink`, `-border`) |
| Accent contextuel | `--accent` (défini par la page), `--accent-ink`, `--accent-deep`, `--accent-soft`, `--accent-line` |
| Espaces | `--ds-space-1` à `--ds-space-16` (pas de 0,25 rem) |
| Rayons | `--ds-radius-xs` (6), `-sm` (8), `--ds-radius` (12), `-lg` (16), `-xl` (22), `-pill` |
| Ombres | `--ds-shadow-xs`, `-sm`, `-md`, `-lg`, `-xl` |
| Focus | `--ds-ring`, `--ds-ring-contrast` |
| Mouvement | `--ds-motion-fast` (130 ms), `--ds-motion` (180 ms), `--ds-ease` |
| Lecture | `--ds-measure` (68 ch), `--ds-header-h` |

### Polices

- **Inter** porte tout le texte courant (paragraphes, listes, formulaires,
  tableaux). Auto-hébergée en version variable, sous-ensembles latin et
  latin-ext, préchargée.
- **Krub** porte les titres et les grands affichages (`--ds-font-display`), en
  deux graisses auto-hébergées : 600 (sous-titres) et 700 (titres de page et de
  section). Le 700 latin est préchargé.
- Aucune requête vers Google Fonts : tout est servi depuis `assets/fonts/`.
- Le serif Fraunces utilisé en V77 a été retiré en V83.

### Règle importante sur `--accent`

Une variable déclarée sur `:root` est **calculée** à cet endroit : `--accent-deep`
défini sur `:root` resterait l'indigo même si `--accent` change plus bas. Les variantes
dérivées sont donc recalculées sur tout élément qui redéfinit `--accent` :

```css
[style*="--accent"], .level-color-theme, .card, .chapter-tile, .level-card, .exam-entry { … }
```

Pour ajouter un accent à un nouveau composant, poser `--accent` sur le conteneur et
ajouter son sélecteur à cette liste (ou utiliser directement
`color-mix(in srgb, var(--accent) 42%, #0d1526)` dans la règle concernée).

## Composants

- **Boutons** : `.btn-primary`, `.btn-secondary`, `.btn-danger`, `.btn-ghost`,
  modificateurs `.btn-sm`, `.btn-lg`, `.btn-block`. `.btn-primary` utilise l'accent
  assombri (`--accent-deep`) : le texte blanc reste lisible quel que soit le niveau
  (ambre et orange inclus).
- **Étiquettes** : `.badge`, `.status-pill` (+ `is-live`, `is-draft`, `is-danger`),
  `.chapter-number`, `.document-type`, `.resource-count`.
- **Cartes** : `.card`, `.chapter-tile`, `.document-resource-card`,
  `.chapter-activity-card`, `.chapter-video-card`, `.exercise-prep-card`,
  `.exam-exercise-card`, `.work-card`, `.stat-card`, `.timer-card` — même bordure, même
  rayon, même ombre.
- **Formulaires** : tous les `input`/`select`/`textarea` ont la même hauteur (2,75 rem),
  le même rayon et le même anneau de focus. Un bouton de validation se place dans
  `.filter-submit` (jamais dans un `<label>Action</label>`).
- **États vides** : `.student-empty`, `.chapter-empty` (compact avec `.is-compact`),
  `.work-empty`, `.editorial-empty`, `.admin-empty-state`.
- **Lecture** : `.lesson-content` limite la mesure à `--ds-measure`, interligne 1,75,
  titres et listes internes stylés, formules KaTeX défilables horizontalement.

## Composants V75

- **`.qcm-identity`** : bloc facultatif nom + classe en tête d'un QCM, dans un
  `fieldset` étiqueté. Les valeurs sont mémorisées dans un cookie (`qcm_name`,
  `qcm_class`) et renvoyées en champs cachés à la re-soumission.
- **`.qcm-review`** : liste des questions ratées après correction, avec le lien vers le
  chapitre. C'est la remédiation : une erreur doit mener quelque part.
- **`.method-card`, `.method-icon`, `.method-tag`** : fiches méthodes. L'icône est un emoji
  sur un aplat d'accent assombri (plus aucune police d'icônes externe).
- **`.work-done`** : case « Fait » du travail à faire, état conservé dans `localStorage`
  (`assets/js/work-done.js`). `.work-card.is-done` barre le titre.
- **`.notfound`, `.notfound-levels`** : page 404 et raccourcis vers les niveaux.
- **`.stat-card`** : statistique cliquable (tableau de bord, résultats des QCM).

## Règles d'usage

- utiliser `--accent` uniquement pour la couleur du niveau ou de la matière ;
- réutiliser les classes de boutons existantes plutôt que d'ajouter des couleurs locales ;
- un seul contrôle principal par formulaire ;
- éviter les cartes imbriquées et les ombres sur les sections de page ;
- tester chaque nouvel écran à 390 px et 1440 px, en clair et en sombre ;
- toute couleur de texte doit atteindre 4,5:1 (3:1 pour le grand texte).
