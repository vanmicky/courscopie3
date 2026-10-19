# V59 - Sauvegardes securisees

- Ajout d'une sauvegarde complete en un seul ZIP : `contenus.json` et dossier `uploads`.
- Verification d'un fichier JSON avant toute restauration avec version, date et nombres de contenus.
- Restauration protegee par une seconde selection du fichier et la confirmation `RESTAURER`.
- Ajout d'un mode `AJOUTER` qui conserve l'existant, recree les liens entre contenus et renomme les slugs en conflit.
- Validation stricte du format, de la version, des tables autorisees, de la taille et du nombre de lignes.
- Restauration transactionnelle : en cas d'erreur, les contenus precedents sont conserves.
- Ajout du journal des exports, verifications et restaurations dans `backup_logs`.
- Ajout de `/admin/systeme.php` pour verifier PHP, MySQL, uploads, ZipArchive et les limites d'envoi.
- Les comptes administrateurs restent exclus des sauvegardes et ne sont jamais remplaces.
- Interface responsive completee dans l'unique fichier `dist/output.css`.
- Ajout de `database/migrate_v58_to_v59.sql`.
- Cache offline mis a jour en `cours-gedion-v59`.
