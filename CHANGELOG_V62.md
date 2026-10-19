# V62 - Mise a niveau des blocs de cours

- Correction de l'erreur `Unknown column 'kind' in 'field list'`.
- Detection automatique de la colonne `lessons.kind`.
- Creation automatique de la colonne manquante depuis l'administration.
- Message d'aide explicite si le compte MySQL ne possede pas le droit de modifier la table.
- Controle de compatibilite ajoute dans la page Etat du systeme.
- Migration manuelle fournie dans `database/migrate_v61_to_v62.sql`.
- Conservation integrale des contenus existants.
