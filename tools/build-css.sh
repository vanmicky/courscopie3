#!/usr/bin/env bash
# Assemble la feuille de style unique servie par le site.
#
#   src/legacy-v73.css   feuille historique (composants anciens conservés tels quels)
#   src/ds-1-base.css    V74 · jetons, remise à zéro, typographie, utilitaires
#   src/ds-2-components.css V74 · composants
#   src/ds-3-pages.css   V74 · pages, administration, outils, impression
#   src/ds-4-modern.css  V76 · refonte moderne : dégradés, profondeur, héros, cartes
#   src/ds-5-warm.css    V77 · éditorial chaleureux : palette crème, titres Fraunces
#                        V78 · motif de fond sur les pages intérieures
#   src/ds-6-reading.css V79 · lecture longue : fiche « Apprendre à apprendre »
#   src/ds-7-tabs.css    V80 · onglets horizontaux des pages chapitre
#   src/ds-8-outils.css  V81 · outils de classe (minuteur, groupes, tirage, consignes)
#   src/ds-9-mono.css   V85 · theme monochrome (noir et blanc, nuances de gris)
#   src/ds-9-fixes.css  V86 · correctifs transverses (contraste, accessibilite)
#   src/ds-10-icones.css V87 · couleur de matiere dans la nav, icones, logo colore
#   src/ds-11-mobile.css V88 · menu mobile en tiroir (bottom sheet)
#   src/ds-12-hyperui.css V89 · composants facon HyperUI (boutons noirs mats, cartes bordees)
#   src/ds-13-heros.css V90 · heros compacts (accueil + niveau)
#   src/ds-14-methode.css V93 · fiche methode : index lateral groupe
#   src/ds-15-admin.css V97 · direction artistique de l'administration
#   src/ds-16-flashcards.css V100 · cartes memoire : recto/verso
#   src/ds-17-projection.css V102 · projection individuelle des outils
#   src/ds-18-qcm.css V109 · QCM : vert et rouge plus fonces
#
#   Theme alternatif NON compile : src/ds-9-mono.css (site en noir et blanc).
#   Pour le reactiver, remplacer ds-9-fixes.css par ds-9-mono.css ci-dessous.
#
# Sortie : dist/output.css (seul fichier à déployer, aucune compilation requise).
set -euo pipefail
cd "$(dirname "$0")/.."

out="dist/output.css"
tmp="$(mktemp)"

{
  echo "/* Styles compilés — Les cours de M. Gedion — V124."
  echo "   Généré par tools/build-css.sh : ne pas modifier dist/output.css à la main. */"
  for part in src/legacy-v73.css src/ds-1-base.css src/ds-2-components.css src/ds-3-pages.css src/ds-4-modern.css src/ds-5-warm.css src/ds-6-reading.css src/ds-7-tabs.css src/ds-8-outils.css src/ds-9-mono.css src/ds-9-fixes.css src/ds-10-icones.css src/ds-11-mobile.css src/ds-12-hyperui.css src/ds-13-heros.css src/ds-14-methode.css src/ds-15-admin.css src/ds-16-flashcards.css src/ds-17-projection.css src/ds-18-qcm.css; do
    echo ""
    echo "/* ===== ${part} ===== */"
    cat "$part"
  done
} > "$tmp"

mv "$tmp" "$out"
printf 'dist/output.css : %s lignes, %s Ko\n' "$(wc -l < "$out" | tr -d ' ')" "$(( $(wc -c < "$out") / 1024 ))"
