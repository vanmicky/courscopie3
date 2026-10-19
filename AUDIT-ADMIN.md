# Audit UX & refonte de l'administration

Document de travail du directeur produit / UX. Il explique **pourquoi**
l'administration paraissait « pas intuitive », et ce qui a été changé.

---

## 1. Le constat (audit)

### Problème n° 1 — « Enregistrer » ne veut pas dire « visible »

Pour un enseignant, la question centrale est : **« est-ce visible par mes
élèves ? »**. Or l'interface la traitait en second plan :

- la case « Publié » était **décochée par défaut** : enregistrer créait un
  brouillon invisible, sans avertissement ;
- la lecture du champ était fausse (`isset()` vrai pour `"0"`) : impossible de
  fiabiliser la distinction publié / brouillon ;
- le statut n'était qu'une **pastille minuscule** perdue dans une ligne.

**Corrigé (V105 + V106)** : publication par défaut, champ explicite, lecture
correcte, et surtout **un statut et un bouton « Publier » impossibles à manquer**
sur le chapitre comme sur chaque contenu.

### Problème n° 2 — Le parcours « série → cartes » était invisible

Les flashcards et les QCM ont une structure à deux niveaux (série, puis cartes /
questions). Le bloc des cartes se trouvait **tout en bas** de la page et
n'apparaissait **qu'après avoir sélectionné une série** : rien ne le signalait.

**Corrigé (V104)** : bandeau « Série sélectionnée » + bouton « Ajouter une
carte » + retour direct sur les cartes après enregistrement.

### Problème n° 3 — L'en-tête ne répondait pas à la vraie question

Le titre et un bouton « Prévisualiser » existaient, mais pas de réponse directe
à « le chapitre est-il en ligne ? ».

**Corrigé (V106)** : l'en-tête porte désormais le **statut du chapitre**, un
bouton **« Publier le chapitre » / « Passer en brouillon »** en un clic, et un
lien **« Voir sur le site ↗ »** quand il est publié.

### Problème n° 4 — Ajouter ou modifier ? ambigu

Le formulaire utilisait le même en-tête pour créer et modifier.

**Corrigé (V106)** : un **badge coloré** « AJOUTER » (vert) ou « MODIFIER »
(bleu) précède le titre, et l'objet modifié est rappelé (« Modifier · Documents
« Fiche … » »).

---

## 2. Principes retenus

1. **La visibilité est une action de premier plan, pas une option.** Un
   enseignant doit pouvoir répondre « mes élèves voient ça » en un regard.
2. **Un écran = une intention.** L'éditeur de chapitre a une seule mission :
   remplir un chapitre et le rendre visible.
3. **L'état compte plus que le formulaire.** Le statut (brouillon / publié) est
   affiché avant le contenu.
4. **Proximité et rythme.** La loi de proximité est appliquée (8 / 12 / 24 / 32
   px) ; un groupe visuel = une idée.
5. **Jamais d'action cachée.** Chaque action importante a un bouton nommé, pas
   une icône seule ou un libellé ambigu.

---

## 3. Ce qui reste recommandé (feuille de route)

Ces chantiers ne sont pas encore faits ; ils suivront le même fil conducteur.

1. **Fusionner la structure** — « Niveaux », « Matières », « Chapitres » et
   « Vidéos et savoirs » décrivent une même chose : le programme. Une vue
   hiérarchique unique (niveau ▸ chapitre) remplacerait quatre pages.
2. **Éditeur de contenu unifié** — les onglets actuels sont corrects mais
   pourraient devenir une **colonne latérale de sections** sur une seule page
   défilante, alignée sur l'ordre de lecture des élèves.
3. **Assistant de publication** — au moment de publier un chapitre, un récap
   « il manque X documents, Y QCM » évite les contenus incomplets.
4. **Tests de rendu** — la suite couvre les enregistrements et la publication ;
   ajouter des tests qui vérifient que chaque page publique répond 200 et
   contient les bons blocs.

---

## 4. Mesure des résultats

- Suite de tests : **49 vérifications au vert** (`bash tools/run-tests.sh`) ;
- parcours publication rejoué sur le site réel : publié → visible, masqué →
  caché, republié → visible ;
- contraste AA en clair et en sombre sur l'éditeur : aucune alerte ;
- 10 pages d'administration répondent sans erreur serveur.
