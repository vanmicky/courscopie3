# Direction artistique — Administration

Document de référence pour la partie **administration seulement**. Le site
public conserve sa propre direction (monochrome + couleurs de matières).

---

## Ambiance retenue

> **« Atelier tech et épuré »**

Un outil de travail, pas une vitrine : neutres froids, aucune décoration, une
seule couleur d'accent réservée à l'action et à l'état actif. L'interface doit
s'effacer devant les données.

*Alternatives possibles si l'ambiance doit changer : « chaleureuse et
rassurante » (neutres chauds, accent ambre) ou « sombre et premium » (mode
sombre par défaut, accent cyan). Seuls les jetons de la section Couleurs
changent — le reste du document reste valable.*

---

## 1. Couleurs

| Rôle | Jeton | Valeur (clair) |
| --- | --- | --- |
| Fond de page | `--a-bg` | `#f6f7f9` |
| Surface (cartes, modales) | `--a-surface` | `#ffffff` |
| Surface secondaire | `--a-surface-2` | `#f9fafb` |
| Surface tertiaire (badges) | `--a-surface-3` | `#f2f4f7` |
| Texte principal | `--a-text` | `#14161a` |
| Texte secondaire | `--a-text-2` | `#5b6270` |
| Texte discret | `--a-text-3` | `#6b7280` |
| Bordure | `--a-border` | `#e4e7ec` |
| Bordure appuyée | `--a-border-strong` | `#d0d5dd` |
| **Primaire (action)** | `--a-primary` | `#18181b` |
| Primaire survol | `--a-primary-hover` | `#2c2c31` |
| **Accent** | `--a-accent` | `#2563eb` |
| Accent très clair | `--a-accent-soft` | `#eef3ff` |

**Règle d'usage de la couleur.** L'accent ne sert jamais à décorer : il indique
un lien, un état actif, un focus, ou une donnée à remarquer. Un écran
d'administration ne doit pas contenir plus de 5 % de surface colorée.

### Mode sombre

| Rôle | Valeur |
| --- | --- |
| Fond | `#0b0c0f` |
| Surface | `#131519` |
| Texte | `#f3f4f6` |
| Texte secondaire | `#a8afbd` |
| Bordure | `#242830` |
| Primaire | `#fafafa` (texte foncé) |
| Accent | `#7aa2ff` |

---

## 2. Typographie

| Usage | Taille | Graisse | Détail |
| --- | --- | --- | --- |
| Titre de page | 20 px | 750 | interlettrage −0,02 em |
| Titre de section | 16 px | 700 | −0,01 em |
| Sous-titre / carte | 15 px | 680 | — |
| Corps | 14 px | 400 | interligne 1,55 |
| Libellé de champ | 13 px | 600 | couleur secondaire |
| Étiquette (badge) | 11 px | 700 | majuscules, +0,04 em |
| Chiffre clé (KPI) | 24 px | 750 | chiffres tabulaires |

**Hiérarchie.** Deux niveaux suffisent : un titre (20 px) et un corps (14 px).
Tout le reste est du renfort. Éviter les tailles intermédiaires qui brouillent
la lecture.

---

## 3. Espacements et rythme

Échelle en pas de 4 px : `4 · 8 · 12 · 16 · 24 · 32 · 48`.

| Relation | Espace |
| --- | --- |
| Dans un même groupe (libellé → champ) | 8 px |
| Entre champs d'un formulaire | 12 px |
| Entre deux groupes (loi de proximité) | 24 px |
| Entre deux sections de page | 32 px |
| Marge sous l'en-tête de page | 24 px |
| Largeur de contenu confortable | 62 caractères pour un paragraphe |

**Règle de proximité.** Un espace de 24 px signale « nouveau groupe » ; en
dessous de 12 px, les éléments sont perçus comme liés. Ne jamais utiliser le
même espacement pour deux relations différentes.

---

## 4. Formes

| Élément | Rayon | Hauteur |
| --- | --- | --- |
| Bouton | 8 px | 40–44 px |
| Champ de saisie | 8 px | 40 px |
| Carte / panneau | 14 px | — |
| Badge, pastille | 999 px | 20–24 px |
| Modale | 14 px | — |
| Icône | 20 px | — |

Pas de rayon supérieur à 14 px sur une carte : au-delà, l'interface devient
« ludique » et perd son sérieux professionnel.

---

## 5. Bordures et ombres

- **Une bordure de 1 px** `--a-border` délimite toutes les surfaces.
- **Ombre à deux couches très légère** : `0 1px 2px rgba(16,24,40,.04),
  0 1px 3px rgba(16,24,40,.03)`. Elle sert à détacher, jamais à décorer.
- **Survol** : la bordure fonce (`--a-border-strong`) et le fond passe à
  `--a-surface-2`. Pas d'élévation, pas de translation.
- **Aucun dégradé, aucun halo, aucune ombre colorée** dans l'administration.
- En mode sombre, les ombres sont **supprimées** : la bordure suffit.

---

## 6. Icônes

- Jeu d'icônes **maison** (`includes/icons.php`), tracés 24 × 24, contour
  `currentColor`, épaisseur 1,9 px.
- Tailles : **16 px** dans un texte, **20 px** dans un bouton ou une carte,
  **22 px** pour une action rapide.
- Toujours accompagnées d'un libellé : aucune icône seule comme unique
  information.
- Couleur : `--a-text-2` au repos, `--a-accent` pour une action principale.

---

## 7. Composants

### En-tête de page
Titre (20 px) + description en une phrase, aligné à gauche ; **filet de 1 px**
sous l'ensemble, 24 px avant le contenu. Pas de carton, pas de badge, pas de
grand titre.

### Bandeau de chiffres clés
**Une seule surface** bordée, divisée par des filets de 1 px. Chiffre en 24 px
au-dessus, libellé en petites majuscules en dessous. Dix chiffres tiennent ainsi
sur une ligne, sans bruit.

### Boutons
| Variante | Fond | Bordure | Texte |
| --- | --- | --- | --- |
| Primaire | `--a-primary` | même | blanc |
| Secondaire | `--a-surface` | `--a-border-strong` | `--a-text` |

Un écran ne comporte **qu'un seul bouton primaire**.

### Champs
Hauteur 40 px, bordure `--a-border-strong`, rayon 8 px. Au focus : bordure
`--a-accent` + halo de 3 px à 16 % d'opacité. Libellé au-dessus, 8 px avant le
champ.

### Tableaux
Pas de bordure extérieure. En-tête en 11 px majuscules, gris secondaire, filet
inférieur. Lignes séparées par un filet de 1 px ; survol en `--a-surface-2`.

### Menu latéral
**Toujours visible et entièrement déployé** : « Tableau de bord » puis quatre
sections (Cours, Activités, Résultats, Réglages) avec leur intitulé en petites
majuscules. Aucun accordéon, aucun compteur. Recherche en haut du menu. Sur
téléphone uniquement, un bouton ouvre le panneau.

---

## 8. Ce qui est interdit

- dégradés, halos, ombres colorées ;
- plus d'un bouton primaire par écran ;
- un badge « Admin » ou un titre de page en 2 rem ;
- des cartes flottantes pour des chiffres (préférer une surface unique) ;
- des icônes sans libellé ;
- plus de deux niveaux de gris pour du texte (principal / secondaire).

---

## 9. Mise en œuvre

- Jetons et composants : `src/ds-15-admin.css`, **portée `.admin-area`**.
- Le site public n'est pas touché : sa direction reste monochrome + matières.
- Les jetons du design system (`--ds-*`) sont remappés sur les jetons admin à
  l'intérieur de `.admin-area`, ce qui fait suivre tous les composants existants
  sans les réécrire.
