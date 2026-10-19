# V83 — Police Inter + Krub sur tout le site, et fiche méthode à onglets

Deux demandes : une combinaison **Inter + Krub** pour tout le site, et une refonte
de la fiche « Apprendre à apprendre », jugée peu pratique.

## 1. Inter + Krub partout

Le serif Fraunces (mon choix de la V77) est **entièrement retiré**.

| Usage | Police |
| --- | --- |
| Texte courant, paragraphes, listes, formulaires | **Inter** |
| Titres, titres de section, grands affichages | **Krub** |

- Krub est auto-hébergée, en deux graisses seulement : **600** et **700**, en
  sous-ensembles latin et latin-ext (11 Ko par fichier, 46 Ko au total).
- Le jeton `--ds-font-display` porte Krub, avec Inter en repli : aucun élément ne
  peut retomber sur une troisième police.
- Inter est préchargée, et **Krub 700 latin** aussi (le titre de page est
  l'élément le plus visible).
- Graisses et interlettrages ont été réajustés pour Krub : 700 pour les titres de
  page et de section, 600 pour les sous-titres.
- Les fichiers Fraunces (124 Ko) et leur `@font-face` ont été supprimés.

Vérifié en navigateur sur six pages : titres en **Krub**, texte en **Inter**,
`Fraunces` absent du DOM, et les deux graisses Krub effectivement chargées.

## 2. « Apprendre à apprendre » : sections en onglets

Malgré la colonne de lecture unique de la V79, le guide restait un long
défilement : douze sections, plus de 3 000 mots. Il fallait descendre loin pour
retrouver « Réviser les maths » ou « Sommeil ».

Désormais, **une seule section est affichée à la fois**, choisie dans une barre
de pastilles collante. C'est le même principe que les onglets des pages chapitre
que tu avais demandés.

- 12 pastilles horizontales, défilantes, avec la section en cours surlignée ;
- un bouton **« Tout afficher »** pour revenir à la lecture continue ;
- lien direct : `methode.php?slug=apprendre-a-apprendre#maths` ouvre la section ;
- navigation au clavier (flèches, Début, Fin) et attributs ARIA d'onglets ;
- en mobile, les pastilles défilent sur toute la largeur.

### Et toujours un fond de lecture

Le texte reposait sur le fond de page (crème + semis de points), le motif passait
derrière les lignes. Le contenu est maintenant posé sur **deux surfaces pleines** :
le bloc de titre et la colonne de lecture. Mesures relevées : fond ivoire
`rgb(255, 253, 247)`, rayon 20 px, marge intérieure 39 px, mesure de texte 706 px
(≈ 70 caractères), interligne 1,78, `text-wrap: pretty` pour des coupures de ligne
propres.

### Sans JavaScript, rien ne change

Aucune section n'est masquée côté serveur : sans JavaScript, les douze sections
restent visibles et le sommaire redevient une simple liste de liens. À
l'impression, la barre d'onglets disparaît et les **12 sections sont toutes
imprimées**, même celle qui était masquée à l'écran.

## Vérifications

- mode onglets : 12 onglets, 12 sections, une seule visible, clic sur
  « Réviser les mathématiques » → `#maths` et section basculée ;
- « Tout afficher » → 12 sections visibles, bouton basculé ;
- les neuf autres fiches méthodes ne déclenchent pas le mode onglets ;
- contraste AA en clair et en sombre sur 12 pages représentatives : aucune alerte ;
- impression : 12/12 sections sur la fiche, 7/7 sur un chapitre, corrections
  toujours exclues ;
- 18 pages publiques en HTTP 200 sans erreur PHP (`/404.php` renvoie un 404
  volontaire), `php -l` et `node --check` sans erreur.

> La base MySQL n'a pas pu être relancée dans cette session (le bac à sable
> interdit l'écriture dans `/Applications/MAMP/logs` et le dossier de données).
> Les pages qui lisent la base s'affichent en mode dégradé, avec le jeu de
> données de secours : la fiche méthode et les outils sont donc vérifiables, mais
> le chapitre de démonstration apparaît vide (« Chapitre en préparation »).
> Aucun de ces changements ne dépend de la base.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=83`, cache service worker
`cours-gedion-v83` (qui précharge aussi Krub et le script des onglets).
Aperçus dans `apercus/`.
