# V39 - UI premium et compatibilite GitHub Pages

- Ajout d'une nouvelle page d'accueil plus structurée avec accès rapides aux niveaux, au travail à faire et aux méthodes.
- Modernisation globale de l'UI : cartes plus nettes, hiérarchie typographique renforcée, meilleurs contrastes, états hover/focus/active et micro-interactions discrètes.
- Amélioration responsive/mobile-first de la navigation mobile avec défilement tactile plus propre.
- Cohérence visuelle renforcée entre Mathématiques, Physique-Chimie et NSI grâce aux accents de couleur existants.
- Retrait du runtime Tailwind charge depuis CDN dans les pages HTML : le site s'appuie sur le CSS statique `dist/output.css`, plus fiable pour GitHub Pages.
- Correction de l'enregistrement du service worker depuis les sous-pages pour respecter les chemins relatifs du site.
- Mise à jour du cache offline en `cours-gedion-v39`.
- Vérification des chemins relatifs HTML/CSS/JS/images/PDF : aucune reference locale manquante detectee.
