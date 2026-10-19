# V115 — Statistiques de visites et pays des visiteurs

Demande : ajouter dans l'administration le **nombre de visites** et les **pays
des visiteurs**.

## Ce qui a été construit

### Une page « Trafic du site »

Ajoutée au menu, section **Résultats**. Elle affiche :

- **4 chiffres clés** : visites au total, aujourd'hui, sur la période,
  visiteurs différents ;
- un **histogramme des 14 derniers jours** ;
- le **classement des pays** avec drapeau, nombre de visites et part (%) ;
- les **pages les plus vues** ;
- les **sites d'origine** (provenance externe).

Filtre de période : 7 jours, 30 jours, 90 jours, tout l'historique.

### Un suivi léger

Chaque page publique enregistre une visite : date, pays, page, site d'origine,
empreinte anonyme du visiteur.

| Point | Choix retenu |
| --- | --- |
| Adresse IP | **jamais stockée** : seule une empreinte SHA-256 salée permet de compter les visiteurs uniques |
| Robots | détectés et exclus des statistiques (`is_bot`) |
| Administration | jamais comptée, ni les prévisualisations |
| Méthodes | uniquement les `GET` réussis |
| Purge | historique limité à **365 jours**, nettoyage occasionnel automatique |

### Localisation hors ligne

Aucun service externe, aucune IP envoyée à un tiers :

- jeu de données public **GeoLite2 Country** (CC BY-SA) téléchargé une fois ;
- condensé en une table de **65 536 blocs /16** = **128 Ko** :
  `data/geoip-country.bin` ;
- résolution instantanée par lecture directe du fichier ;
- **196 pays** couverts ; IPv6 local et IPv4 encapsulée reconnus.

Régénération : `php tools/build-geoip.php` (à relancer une à deux fois par an).

## Deux bugs corrigés pendant la mise au point

| Bug | Correction |
| --- | --- |
| Le pays « LOCAL » était tronqué : la colonne `country` était `CHAR(2)` | passée en `VARCHAR(8)` |
| Localhost en IPv6 (`::1`) était compté « inconnu » | IPv6 local et IPv4 encapsulée maintenant reconnus |

## Vérifications

| Contrôle | Résultat |
| --- | --- |
| Enregistrement d'une visite | +1 ligne, pays résolu, empreinte de 64 caractères |
| Googlebot | enregistré avec `is_bot = 1`, **exclu** des chiffres |
| Page d'administration | **aucune** visite comptée |
| Rendu de la page | 9 visites, 8 visiteurs différents, 7 pays, 14 barres |
| Contraste AA clair + sombre | aucune alerte |
| Suite de tests | **60 vérifications** (11 nouvelles) |

## Mise à niveau

La table `visits` est **créée automatiquement** à la première visite ; elle est
aussi ajoutée à `database/schema.sql` pour une installation neuve.

**À envoyer en plus lors du déploiement** : le dossier `data/`.

CSS `?v=115`, cache service worker `cours-gedion-v115`.
Aperçu : `apercus/admin-trafic.jpg`.
