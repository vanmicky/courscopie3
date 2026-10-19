# V93 — Fiche « Apprendre à apprendre » : index latéral groupé

La fiche était jugée peu pratique. Après analyse, le problème venait du mode
**onglets** mis en place en V83.

## Pourquoi les onglets ne marchaient pas

- **douze pastilles** dans une barre horizontale : il fallait faire défiler
  pour trouver « Réviser les maths » ;
- **une seule section visible** : impossible de scanner le guide, de comparer
  deux méthodes, ou de retrouver une notion en faisant défiler ;
- la recherche du navigateur (Ctrl+F) ne trouvait que la section ouverte.

Pour un document de référence de plus de 3 000 mots, le bon modèle est celui
d'une documentation : **un sommaire latéral toujours visible et tout le contenu
en une colonne**.

## Ce qui a été refait

### Sommaire latéral collant
- **groupé en trois entrées** au lieu de douze : *Les principes* (5 sections),
  *Au quotidien* (4), *Par matière* (3) ;
- **numéroté** 01 à 12 pour scanner vite ;
- libellés **nettoyés** (les emoji des titres sont retirés) ;
- la section en cours est **surlignée** pendant le défilement.

### Contenu en une seule colonne
- les douze sections sont visibles, séparées par un filet ;
- défilement continu, comparaison possible, Ctrl+F efficace ;
- en-tête de fiche compacté (titre, résumé, règle d'or en encadré court) ;
- rythme de lecture resserré : titres de section plus petits, sous-titres à
  1,06 rem, corps à 0,96 rem / interligne 1,72, encadrés plus denses.

### Téléphone
- le sommaire devient un bloc **repliable** (« Sommaire · 12 sections ») ;
- il se referme automatiquement après un choix ;
- tout le contenu reste visible, sans barre horizontale à faire défiler.

## Deux défauts corrigés au passage

1. **Contraste en mode sombre** : dans le sommaire, les couleurs se résolvaient
   avec les valeurs du thème clair (texte sombre sur fond sombre, 1,35:1). Les
   couleurs sont désormais portées par les éléments internes du sommaire et
   déclinées par thème : plus aucune ambiguïté.
2. **Bannière de version du CSS** restée à « V90 » : corrigée.

## Vérifications

- 12 sections toutes visibles, index à 3 groupes et 12 entrées numérotées ;
- repérage de section testé sur 6 sections (règles d'or, métacognition,
  téléphone, maths, NSI, plan de semaine) : **6/6 corrects** ;
- téléphone : sommaire replié par défaut, une seule colonne, 12 sections
  visibles ;
- **contraste AA en clair et en sombre** : aucune alerte sur la fiche, la liste
  des méthodes et une fiche courte ;
- 18 pages publiques en HTTP 200 sans erreur PHP, `php -l` et `node --check`
  sans erreur.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=93`, cache service worker
`cours-gedion-v93`. Aperçus : `apercus/fiche-methode-index.jpg`,
`fiche-methode-lecture.jpg`, `fiche-methode-sombre.jpg`, `fiche-methode-mobile.jpg`.
