# V105 — Les contenus enregistrés n'apparaissaient pas sur le site

Symptôme : « l'admin enregistre mais on ne voit rien sur le site vitrine ».

## La cause

Les séries étaient enregistrées en **brouillon** (`is_published = 0`), et le site
public ne montre que les contenus publiés (`get_flashcard_decks()` filtre
`d.is_published=1`).

Deux défauts cumulés :

1. **La case « Publié » était décochée par défaut** pour tout nouveau contenu :
   un enregistrement créait donc un brouillon invisible.
2. **La lecture du champ était fausse** : le code utilisait
   `isset($_POST['is_published'])`, or `isset()` est vrai même quand la valeur
   est `"0"`. Avec un champ explicite, **tout serait devenu publié**, y compris
   ce qu'on voulait masquer.

## Les corrections

| Correction | Détail |
| --- | --- |
| Publication par défaut | un nouveau contenu est coché « Publié » |
| Champ explicite | champ caché `0` + case `1` : le formulaire envoie toujours une valeur |
| Lecture correcte | `workspace_published_flag()` lit la **valeur** (`=== '1'`), plus la présence |
| Libellé clair | « Publié — visible sur le site » |
| Message adapté | enregistrer en brouillon affiche « Enregistré en brouillon : ce contenu reste invisible sur le site. » |

## Preuve par les tests

La suite compte maintenant **49 vérifications**, dont 5 nouvelles sur la
publication :

```
Publication — visibilité sur le site public
  ✓ série publiée visible sur le site
  ✓ série en brouillon masquée sur le site
  ✓ brouillon visible dans l'administration
  ✓ exercice publié visible
  ✓ exercice en brouillon masqué
```

Ce sont ces tests qui ont détecté la régression du `isset()` avant livraison.

## Preuve sur le site réel

Cycle complet rejoué en HTTP sur `chapitre.php?slug=cinquieme-ch01…` :

| État de la série | Visible sur la page chapitre |
| --- | --- |
| Publiée | **oui** |
| Masquée | **non** |
| Republiée | **oui** |

## Ce qu'il te reste à faire

Tes deux séries existantes sont en brouillon :

- `chapitre 1` (4 cartes)
- `dsds` (0 carte)

Sur `/admin/chapitre.php?chapter_id=1&tab=flashcards`, clique sur **Publier**
dans la ligne de la série : elle apparaît immédiatement sur la page du chapitre.
La série « Flashcards — Atomes et ions » est déjà publiée et visible.

## Mise à niveau

Aucune migration SQL. CSS servi avec `?v=105`, cache service worker
`cours-gedion-v105`. Aperçu : `apercus/chapitre-public.jpg`.
