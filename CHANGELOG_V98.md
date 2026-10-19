# V98 — Barre latérale permanente et « se souvenir de moi »

Deux demandes : une barre latérale toujours visible (sans bouton ni accordéon)
et une connexion admin qui se souvienne du mot de passe.

## 1. Barre latérale permanente

Les sections repliables (`<details>`) obligeaient à cliquer pour découvrir les
pages. Elles sont supprimées : **tout le menu est visible d'emblée**.

- « Tableau de bord » reste une entrée simple, en tête ;
- quatre sections suivies de leurs pages : **Cours**, **Activités**,
  **Résultats**, **Réglages** ;
- chaque section porte un intitulé en petites majuscules et une icône ;
- la page courante est mise en évidence par un fond d'accent clair ;
- **plus aucune balise `<details>`** dans le menu (vérifié : 0).

Sur ordinateur, le bouton « Menu admin » est **masqué** (`display: none` à
1440 px) : le menu est là, en permanence. Sur téléphone (390 px), le bouton
reste indispensable pour ouvrir le panneau — il est le seul élément conservé de
l'ancien système.

## 2. « Se souvenir de moi »

Case à cocher sur la page de connexion : **« Se souvenir de moi pendant
30 jours »**, cochée par défaut.

### Comment c'est stocké

Table dédiée, créée automatiquement :

```sql
CREATE TABLE admin_remember (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NOT NULL,
  selector   CHAR(32) NOT NULL UNIQUE,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_admin_remember_admin FOREIGN KEY (admin_id)
    REFERENCES admins(id) ON DELETE CASCADE
);
```

Le cookie `cours_admin_remember` contient `sélecteur:validateur` :

- le **sélecteur** (32 caractères) retrouve la ligne ;
- le **validateur** (64 caractères) n'est **jamais stocké en clair** : seule son
  empreinte SHA-256 est en base.

### Sécurité

| Mesure | Détail |
| --- | --- |
| Cookie | `HttpOnly`, `SameSite=Lax`, `Secure` si HTTPS, durée 30 jours |
| Comparaison | `hash_equals` (pas de comparaison sensible au temps) |
| Rotation | le validateur change à **chaque connexion automatique** |
| Vol de jeton | un validateur incohérent invalide **toutes** les sessions mémorisées du compte |
| Expiration | lignes périmées supprimées à chaque connexion |
| Déconnexion | le jeton est supprimé en base **et** le cookie effacé |

### Vérifié de bout en bout

1. connexion avec la case cochée → `302`, cookie posé, **1 jeton** en base
   expirant à 30 jours ;
2. suppression du cookie de session, conservation du seul cookie « souvenir » →
   `/admin/dashboard.php` répond **200** (reconnexion automatique) ;
3. **rotation confirmée** : l'empreinte en base change après cette reconnexion ;
4. déconnexion → `302`, **0 jeton** restant.

## Vérifications

- tableau de bord et connexion : **contraste AA en clair et en sombre**,
  aucune alerte ;
- 17 pages d'administration + 18 pages publiques en HTTP 200 ;
- `php -l` et `node --check` sans erreur ;
- aucune erreur PHP sur les pages d'administration.

## Mise à niveau

Aucune migration SQL : la table `admin_remember` est créée à la demande à la
première connexion. CSS servi avec `?v=98`, cache service worker
`cours-gedion-v98`.

> **Note** : la case est cochée par défaut. Pour ne pas mémoriser la session, il
> suffit de la décocher — le jeton existant est alors supprimé à la connexion.
