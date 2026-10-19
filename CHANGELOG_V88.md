# V88 — Menu mobile en tiroir

Demande : améliorer l'utilisation sur téléphone, en priorité le menu.

## Le problème

Sur téléphone, la navigation était une **bande horizontale défilante** : on ne
voyait qu'une partie des liens (6 niveaux + Méthodes + Outils), les cibles
étaient petites et l'ensemble était exigu.

## La solution : un panneau coulissant

Le menu devient un **bottom sheet** — un panneau qui glisse depuis le bas de
l'écran, ouvert par un bouton hamburger (☰) à droite de l'en-tête.

- **Grandes cibles** : chaque lien fait toute la largeur, avec une hauteur de
  **53 px** (au-dessus des 44 px recommandés), une taille de texte accrue ;
- **couleur de la matière** : chaque niveau garde sa pastille colorée, et le
  survol/appui teinte le lien de sa couleur ;
- **fermeture** par quatre moyens : le fond assombri, la croix, Échap, ou un
  clic sur un lien ;
- **verrouillage du défilement** de la page tant que le menu est ouvert ;
- **zone de sécurité** iPhone (safe area) respectée en bas ;
- **accessibilité** : `aria-expanded`, `aria-controls`, `aria-hidden`, retour du
  focus au bouton à la fermeture, focus sur le premier lien à l'ouverture.

## Sans JavaScript, rien ne casse

La classe `js-nav` est posée sur `<html>` **avant le rendu** (dans theme-init).
Sans JavaScript, le tiroir reste une bande horizontale visible : aucune
navigation perdue.

## Un piège technique évité

Le tiroir est en `position: fixed`, mais il était d'abord placé **dans**
l'en-tête, dont le `backdrop-filter` (verre dépoli) crée un bloc contenant pour
les éléments en position fixe : le panneau ne serait pas resté collé à l'écran.
Il est donc émis **hors** du `<header>`, juste après.

## Vérifications

- état initial (390 px) : bouton visible, tiroir hors champ (translaté de 112 %),
  fond assombri transparent, 8 liens, cibles à 53 px, pastilles colorées ;
- ouverture : `is-open`, `aria-expanded=true`, fond à 1, défilement verrouillé ;
- fermeture testée par fond, croix et Échap : tout revient à l'état initial ;
- bureau (≥1280 px) : bouton et tiroir masqués (`display:none`), navigation
  habituelle conservée ;
- **9/9 cibles tactiles ≥ 44 px** dans le menu ;
- contraste AA en clair et en sombre sur 10 pages : aucune alerte ;
- 18 pages publiques en HTTP 200 sans erreur PHP, `php -l` et `node --check`
  sans erreur.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=88`, cache service worker
`cours-gedion-v88`. Aperçus dans `apercus/`.
