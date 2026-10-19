# V95 — Nettoyage des chapitres et accès administration

Quatre demandes : supprimer le texte de remplissage, retrouver l'accès à
l'administration, centrer la connexion, et réinitialiser le mot de passe oublié.

## 1. Le texte de remplissage est supprimé

Les 41 chapitres importés du site statique portaient tous le même résumé :
« Contenu repris du site statique. Cette fiche pourra être enrichie depuis
l'administration. »

Trois niveaux de correction :

- **jeu de données** : `database/seed.sql` insère désormais `NULL` au lieu de ce
  texte (les 41 lignes sont corrigées) ;
- **affichage** : une fonction `chapter_summary()` écarte ce texte, **même s'il
  reste en base** — utile sur une installation existante ;
- **base de démonstration** : les résumés ont été nettoyés
  (`UPDATE chapters SET summary = NULL WHERE summary LIKE '%repris du site statique%'`).

La carte de chapitre affiche donc simplement le numéro, le titre et les deux
liens (Documents / Activités).

> **À faire sur ta base** (phpMyAdmin → SQL) :
> ```sql
> UPDATE chapters SET summary = NULL
> WHERE summary LIKE '%repris du site statique%';
> ```

## 2. Accès à l'administration

L'accès existait toujours mais était discret. Il est maintenant présent :

- dans le **pied de page** : « Espace enseignant » ;
- dans le **menu mobile** : nouvelle entrée « Espace enseignant » avec icône ;
- accès direct : `/admin/login.php`.

## 3. Connexion admin centrée

La carte de connexion était **décalée de 128 px** vers la droite : elle héritait
de la règle d'administration qui réserve la largeur de la barre latérale
(`.admin-area > main.page-container`), alors que la page de connexion n'a pas de
barre latérale.

Correction : les pages de connexion et d'initialisation sont exclues de cette
règle. **Mesuré : écart au centre = 0 px.**

## 4. Mot de passe oublié : outil de réinitialisation

**Sur MAMP, `php` n'est pas dans le PATH** : `zsh: command not found: php` est
normal. Trois solutions, de la plus simple à la plus technique.

### A. Sans terminal (recommandé)

1. ouvrir **phpMyAdmin** (MAMP → Open start page → phpMyAdmin) ;
2. onglet SQL, exécuter :

```sql
DELETE FROM admins;
```

3. ouvrir `http://localhost/<dossier-du-site>/admin/setup.php` : la page
   d'initialisation réapparaît ;
4. créer le compte avec l'adresse email et le mot de passe de votre choix.

Vérifié : après suppression, `/admin/login.php` redirige bien vers
`/admin/setup.php` (302), qui affiche le formulaire (200).

### B. Terminal, avec le lanceur fourni

```bash
bash tools/reset-admin.sh
bash tools/reset-admin.sh prof@exemple.fr
bash tools/reset-admin.sh prof@exemple.fr --password="MonNouveauMotDePasse"
```

Le lanceur cherche PHP tout seul (MAMP, Homebrew, MacPorts, PATH) et affiche la
version utilisée.

### C. Terminal, avec le chemin complet de MAMP

```bash
/Applications/MAMP/bin/php/php8.2.0/bin/php tools/reset-admin-password.php
```

### Astuce : avoir `php` partout

```bash
echo 'export PATH="/Applications/MAMP/bin/php/php8.2.0/bin:$PATH"' >> ~/.zshrc
source ~/.zshrc
```

- crée la table `admins` si nécessaire ;
- met à jour le mot de passe si l'adresse existe, crée le compte sinon ;
- génère un mot de passe solide si vous n'en fournissez pas ;
- **affiche le mot de passe** à utiliser, puis ne le montre plus.

Le script refuse de s'exécuter depuis le web (403). Aucun accès à
l'administration n'est nécessaire, puisque tout passe par la base.

Autre solution sans terminal : vider la table `admins` dans phpMyAdmin, puis
ouvrir `/admin/setup.php` — la page d'initialisation réapparaît et permet de
créer un nouveau compte.

## Vérifications

- plus aucune occurrence du texte de remplissage sur la page niveau et la page
  chapitre (recherche dans le HTML : 0) ;
- connexion testée de bout en bout : `POST` → 302 vers le tableau de bord,
  `/admin/dashboard.php` → 200, sans erreur PHP ;
- centrage : écart au centre **0 px** (connexion et initialisation) ;
- **18 pages publiques + 17 pages d'administration en HTTP 200** sans erreur ;
- contraste AA de la connexion en clair et en sombre : aucune alerte ;
- `php -l` et `node --check` sans erreur.

## Mise à niveau

Aucune migration obligatoire : le texte est masqué côté code. Pour nettoyer la
base, exécuter l'`UPDATE` ci-dessus. CSS servi avec `?v=95`, cache service worker
`cours-gedion-v95`.
