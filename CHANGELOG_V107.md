# V107 — Vue unifiée « Cours » : niveau → chapitres

Première étape de la feuille de route de `AUDIT-ADMIN.md` : fusionner la
structure du programme en une seule vue.

## Ce qui change

La page `admin/chapitres.php` devient la vue **« Cours »** (entrée de menu
« Programme »). Elle réunit ce qui était dispersé sur trois pages :

| Avant | Après |
| --- | --- |
| un filtre « par niveau » (liste déroulante + bouton Filtrer) | un **rail de niveaux** cliquable, avec compteur et couleur de niveau |
| « Niveaux », « Matières », « Chapitres » séparés | une seule vue, niveau → chapitres |
| statut de publication invisible sur les chapitres | **pastille Publié / Brouillon** + bouton **Publier / Masquer** sur chaque chapitre |

## Détails

- **Rail de niveaux** : « Tous (41) », puis un chip par niveau (`5e · 6`,
  `3e · 6`, …) avec sa pastille de couleur et son nombre de chapitres. Un lien
  `+ Niveau` ouvre l'éditeur de niveaux.
- **Matière visible** : chaque carte de chapitre affiche sa matière
  (`5e · Chapitre 1 · Physique-Chimie`).
- **Publication en un clic** : nouveau handler `action=toggle` sur les chapitres
  (même mécanisme que les contenus). Mesuré : 1 → 0 → 1, HTTP 302.
- Les résumés vides ne laissent plus de paragraphe orphelin.
- Les pages `niveaux.php` et `matieres.php` restent accessibles (éditeurs de
  structure), mais ne sont plus au premier plan.

## Mesures

| Contrôle | Résultat |
| --- | --- |
| Rail : niveaux avec compteurs | 7 chips (« Tous 41 » + 6 niveaux) |
| Cartes avec statut + bouton | oui (41 chapitres) |
| Bascule de publication | 302, état basculé puis restauré |
| Tests | **49/49 au vert** |
| Contraste AA clair + sombre | aucune alerte |
| Débordement horizontal | aucun (desktop et mobile) |

## Mise à niveau

Aucune migration SQL. CSS `?v=107`, cache service worker `cours-gedion-v107`.
Aperçus : `apercus/admin-programme.jpg`, `admin-programme-niveau.jpg`,
`admin-programme-mobile.jpg`.
