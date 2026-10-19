# V113 — Crédit en pied de page

Le pied de page affiche désormais **« M. Gedion © 2026 »**.

L'année est générée automatiquement (`date('Y')`) : elle changera seule au
1er janvier.

Le reste du pied de page reste inchangé (logo MG, liens de navigation, aucune
mention de version).

## Mise à niveau

Aucune migration SQL. CSS `?v=113`, cache service worker `cours-gedion-v113`.
Aperçu : `apercus/pied-de-page.jpg`.
