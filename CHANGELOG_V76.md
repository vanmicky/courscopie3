# V76 — Refonte visuelle moderne

Objectif : donner au site une allure plus moderne, plus attractive, tout en restant
pratique (lisibilité, contraste, performance). Aucune fonctionnalité modifiée : c'est
une couche visuelle ajoutée au design system, pas une réécriture des gabarits.

## Direction artistique

- **Signature dégradée indigo → violet** (`--ds-grad-brand`) : logo, boutons principaux,
  filet des héros, liseré du pied de page, mot-clé du titre d'accueil.
- **Profondeur** : fond de page avec un halo lumineux (indigo/cyan) au-dessus de
  l'en-tête, en-tête « verre dépoli », cartes à ombre étagée, survol qui soulève.
- **Surfaces plus douces** : rayons relevés (cartes 18 px, héros 24 px, boutons 10 px).
  La couche historique forçait 8 px sur les pages élèves via `--student-radius` : c'est
  corrigé.
- **Héros plus affirmés** : dégradé teinté de l'accent du niveau + halo radial, grand
  rayon, filet dégradé à gauche.
- **Cartes de niveau** : ombre au repos, zoom de l'illustration au survol, étiquette
  en aplat d'accent avec halo, lien « Accéder aux chapitres » aligné.

## Fichiers

- `src/ds-4-modern.css` : nouvelle couche, chargée après ds-1/ds-2/ds-3.
- `tools/build-css.sh` : assemble désormais les 5 sources et génère `dist/output.css` V76.
- `index.php` : le titre d'accueil porte un mot en dégradé (`<span class="grad-text">`).

## Accessibilité

- contrastes revérifiés en clair et en sombre sur toutes les pages : aucune alerte ;
- en sombre, les étiquettes de méthode (`.method-tag`) et les panneaux de formules
  (`.method-formula`) passaient en texte sombre sur fond clair : corrigés ;
- `prefers-reduced-motion` neutralise zooms et translations.

## Vérifications

- `php -l` sur tous les fichiers PHP, `node --check` sur tous les JS : sans erreur ;
- 27 URL publiques + 23 URL d'administration : HTTP 200, aucune erreur PHP ;
- audit de contraste automatique (ratio réel sur fond effectif) en clair et en sombre :
  accueil, niveau, chapitre, exercices, QCM, flashcards, travail, examen, méthodes,
  fiche méthode, minuteur, connexion — tout est au-dessus du seuil AA.

## Mise à niveau

Aucune migration SQL. Le CSS est servi avec `?v=76` et le service worker utilise le
cache `cours-gedion-v76` : l'ancien cache est supprimé automatiquement au premier
chargement.
