-- ============================================================================
-- Chapitre de démonstration — « Atomes, ions et molécules » (Troisième, PC)
-- ----------------------------------------------------------------------------
-- Objectif : remplir COMPLÈTEMENT un chapitre pour voir le rendu du site :
-- documents, blocs de cours, exercices (dont un type Brevet), vidéos, QCM,
-- flashcards, savoirs et travail à faire.
--
-- SÛR À EXÉCUTER PLUSIEURS FOIS : les insertions sont idempotentes
-- (aucune donnée existante n'est supprimée, sauf les questions/choix/cartes des
-- démos de QCM et de flashcards référencées ci-dessous, repérées par leur slug).
-- Aucune backslash dans le LaTeX : les formules restent simples ($H_2O$, $Na^+$…).
-- ============================================================================

SET NAMES utf8mb4;

SET @ch  := (SELECT id FROM chapters WHERE slug='troisieme-ch01-atomes-ions-et-molecules');
SET @lv  := (SELECT level_id  FROM chapters WHERE id=@ch);
SET @sub := (SELECT subject_id FROM chapters WHERE id=@ch);

-- ---------------------------------------------------------------------------
-- 1. DOCUMENTS
-- ---------------------------------------------------------------------------
INSERT INTO files (chapter_id, level_id, original_name, stored_name, path, mime_type, size_bytes, label, resource_type, sort_order, is_published)
SELECT @ch, @lv, t.original_name, t.stored_name, t.path, 'application/pdf', t.size_bytes, t.label, t.resource_type, t.sort_order, 1
FROM (
  SELECT 'cours-atomes-ions.pdf' AS original_name, 'cours-atomes-ions.pdf' AS stored_name, 'fichiers/pdf/exemple-tp.pdf' AS path, 1563 AS size_bytes, 'Cours — Atomes, ions et molécules' AS label, 'cours' AS resource_type, 1 AS sort_order
  UNION ALL SELECT 'tp-identification-ions.pdf', 'tp-identification-ions.pdf', 'fichiers/pdf/exemple-tp.pdf', 1563, 'TP — Identification des ions', 'tp', 2
  UNION ALL SELECT 'correction-exercices.pdf', 'correction-exercices.pdf', 'fichiers/pdf/chapitres/troisieme-ch01-correction-exercices.pdf', 1573, 'Corrigé des exercices', 'correction', 3
  UNION ALL SELECT 'correction-tp.pdf', 'correction-tp.pdf', 'fichiers/pdf/chapitres/troisieme-ch01-correction-tp.pdf', 1563, 'Corrigé du TP', 'correction', 4
  UNION ALL SELECT 'fiche-methode-formules.pdf', 'fiche-methode-formules.pdf', 'fichiers/pdf/exemple-correction.pdf', 1573, 'Fiche méthode — Écrire une formule chimique', 'methode', 5
) t
WHERE NOT EXISTS (SELECT 1 FROM files WHERE chapter_id=@ch AND label=t.label);

-- ---------------------------------------------------------------------------
-- 2. BLOCS DE COURS
-- ---------------------------------------------------------------------------
INSERT INTO lessons (chapter_id, title, kind, content, sort_order, is_published)
SELECT @ch, t.title, t.kind, t.content, t.sort_order, 1
FROM (
  SELECT '1. La structure de l’atome' AS title, 'Cours' AS kind,
'## Le noyau et les électrons
Un atome est formé d’un **noyau** central, très petit, autour duquel se déplacent des **électrons**.

- Le noyau contient les **protons** (charge positive) et les **neutrons** (charge nulle).
- L’atome est **électriquement neutre** : il a autant de protons que d’électrons.
- Le numéro atomique $Z$ compte les protons ; le nombre de masse $A$ compte les nucléons.

> L’atome de carbone $^{12}_{6}C$ possède 6 protons, 6 neutrons et 6 électrons.

La masse de l’atome est concentrée dans son noyau : les électrons sont environ 2000 fois plus légers que les nucléons.' AS content, 1 AS sort_order
  UNION ALL SELECT '2. Les ions : des atomes chargés', 'Cours',
'## Cations et anions
Un **ion** est un atome (ou un groupe d’atomes) qui a gagné ou perdu un ou plusieurs électrons.

- Un **cation** est positif : il a **perdu** des électrons. Exemple : $Na^+$.
- Un **anion** est négatif : il a **gagné** des électrons. Exemple : $Cl^-$.

La charge d’un ion se calcule ainsi : charge = Z − nombre d’électrons.

> L’ion cuivre $Cu^{2+}$ a perdu deux électrons : il porte deux charges positives.' , 2
  UNION ALL SELECT '3. Molécules et formules chimiques', 'Cours',
'## Écrire une formule chimique
Une **molécule** est un assemblage d’atomes liés entre eux. Sa formule indique la nature et le nombre de chaque atome.

- Eau : $H_2O$ — deux atomes d’hydrogène et un atome d’oxygène.
- Dioxyde de carbone : $CO_2$ — un atome de carbone et deux d’oxygène.
- Dioxygène : $O_2$ — deux atomes d’oxygène.

Le petit chiffre en bas à droite (l’indice) donne le nombre d’atomes de l’élément qui le précède.' , 3
) t
WHERE NOT EXISTS (SELECT 1 FROM lessons WHERE chapter_id=@ch AND title=t.title);

-- ---------------------------------------------------------------------------
-- 3. EXERCICES
-- ---------------------------------------------------------------------------
INSERT INTO exercises (chapter_id, title, statement, correction, difficulty, category, sort_order, is_published)
SELECT @ch, t.title, t.statement, t.correction, t.difficulty, t.category, t.sort_order, 1
FROM (
  SELECT 'Structure de l’atome de chlore' AS title,
'Le noyau de l’atome de chlore s’écrit $^{35}_{17}Cl$.

## Questions
- Combien de protons contient ce noyau ?
- Combien de neutrons contient-il ?
- Combien d’électrons possède l’atome (neutre) de chlore ?' AS statement,
'## Correction
- Le numéro atomique $Z = 17$ donne le nombre de protons : **17 protons**.
- Les neutrons : $A - Z = 35 - 17 = 18$ neutrons.
- L’atome est neutre : il possède autant d’électrons que de protons, soit **17 électrons**.' AS correction,
'normal' AS difficulty, 'ds' AS category, 1 AS sort_order
  UNION ALL SELECT 'Écrire la formule des ions',
'## Énoncé
- L’atome de sodium $Na$ possède 11 protons. L’ion sodium a perdu un électron : quelle est sa formule ?
- L’atome de chlore $Cl$ possède 17 protons. L’ion chlorure a gagné un électron : quelle est sa formule ?' ,
'## Correction
- En perdant un électron (négatif), l’ion sodium devient positif : $Na^+$.
- En gagnant un électron, l’ion chlorure devient négatif : $Cl^-$.',
'facile', 'ds', 2
  UNION ALL SELECT 'Type Brevet — L’atome et la matière',
'## Énoncé (d’après le sujet de Brevet)
Le sodium est un métal. L’ion sodium $Na^+$ est présent dans le sel de cuisine.

- Que signifie le symbole $Na^+$ ?
- Le noyau de l’atome de sodium porte $Z = 11$. Combien d’électrons possède l’ion $Na^+$ ?
- Le numéro atomique de l’atome de chlore est $Z = 17$. Expliquer pourquoi l’ion chlorure s’écrit $Cl^-$.' ,
'## Correction
- $Na^+$ désigne un ion sodium : un atome de sodium qui a perdu un électron, il porte donc une charge positive.
- L’ion a perdu un électron : il en possède $11 - 1 = 10$ électrons.
- Le chlore gagne un électron pour saturer sa couche externe : il porte alors une charge négative, d’où $Cl^-$.',
'difficile', 'brevet', 3
) t
WHERE NOT EXISTS (SELECT 1 FROM exercises WHERE chapter_id=@ch AND title=t.title);

-- ---------------------------------------------------------------------------
-- 4. VIDÉOS
-- ---------------------------------------------------------------------------
INSERT INTO chapter_videos (chapter_id, title, url, description, sort_order, is_published)
SELECT @ch, t.title, t.url, t.description, t.sort_order, 1
FROM (
  SELECT 'L’atome en 5 minutes' AS title, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' AS url, 'Une introduction visuelle à la structure de l’atome.' AS description, 1 AS sort_order
  UNION ALL SELECT 'Les ions expliqués', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'Pourquoi un atome devient-il un ion ? Cation et anion.' , 2
  UNION ALL SELECT 'Construire une formule chimique', 'https://vimeo.com/76979871', 'Une méthode pas à pas pour écrire les formules des molécules.', 3
) t
WHERE NOT EXISTS (SELECT 1 FROM chapter_videos WHERE chapter_id=@ch AND title=t.title);

-- ---------------------------------------------------------------------------
-- 5. QCM (5 questions, réponses justifiées)
-- ---------------------------------------------------------------------------
INSERT INTO qcms (chapter_id, level_id, title, slug, sort_order, is_published)
VALUES (@ch, @lv, 'QCM — Atomes, ions et molécules', 'qcm-atomes-ions-molecules', 1, 1)
ON DUPLICATE KEY UPDATE title=VALUES(title), chapter_id=VALUES(chapter_id), is_published=1;

SET @q := (SELECT id FROM qcms WHERE slug='qcm-atomes-ions-molecules');

DELETE FROM qcm_choices WHERE question_id IN (SELECT id FROM qcm_questions WHERE qcm_id=@q);
DELETE FROM qcm_questions WHERE qcm_id=@q;

INSERT INTO qcm_questions (qcm_id, question_text, explanation, sort_order) VALUES
(@q, 'Que trouve-t-on dans le noyau d’un atome ?', 'Le noyau contient les protons (positifs) et les neutrons (neutres). Les électrons tournent autour.', 1),
(@q, 'Un atome est électriquement neutre car…', 'Il possède autant de protons (positifs) que d’électrons (négatifs).', 2),
(@q, 'Un ion chlorure $Cl^-$ est un atome de chlore qui a…', 'Un anion est négatif : il a gagné un ou plusieurs électrons.', 3),
(@q, 'La formule de la molécule d’eau est :', 'Deux atomes d’hydrogène et un atome d’oxygène : $H_2O$.', 4),
(@q, 'L’ion cuivre $Cu^{2+}$ a perdu…', 'La charge $2+$ signifie qu’il a perdu deux électrons.', 5);

SET @q1 := (SELECT id FROM qcm_questions WHERE qcm_id=@q AND sort_order=1);
SET @q2 := (SELECT id FROM qcm_questions WHERE qcm_id=@q AND sort_order=2);
SET @q3 := (SELECT id FROM qcm_questions WHERE qcm_id=@q AND sort_order=3);
SET @q4 := (SELECT id FROM qcm_questions WHERE qcm_id=@q AND sort_order=4);
SET @q5 := (SELECT id FROM qcm_questions WHERE qcm_id=@q AND sort_order=5);

INSERT INTO qcm_choices (question_id, choice_label, choice_text, is_correct, sort_order) VALUES
(@q1,'A','Des protons et des neutrons',1,1),(@q1,'B','Des électrons et des neutrons',0,2),(@q1,'C','Uniquement des protons',0,3),(@q1,'D','Uniquement des électrons',0,4),
(@q2,'A','Il possède plus de protons que d’électrons',0,1),(@q2,'B','Il possède autant de protons que d’électrons',1,2),(@q2,'C','Il ne possède que des neutrons',0,3),(@q2,'D','Il possède plus d’électrons que de protons',0,4),
(@q3,'A','perdu un électron',0,1),(@q3,'B','gagné un électron',1,2),(@q3,'C','perdu un proton',0,3),(@q3,'D','gagné un proton',0,4),
(@q4,'A','$H_2O$',1,1),(@q4,'B','$HO_2$',0,2),(@q4,'C','$H_2O_2$',0,3),(@q4,'D','$OH$',0,4),
(@q5,'A','deux neutrons',0,1),(@q5,'B','deux protons',0,2),(@q5,'C','deux électrons',1,3),(@q5,'D','deux atomes',0,4);

-- ---------------------------------------------------------------------------
-- 6. FLASHCARDS (8 cartes)
-- ---------------------------------------------------------------------------
INSERT INTO flashcard_decks (chapter_id, level_id, title, slug, sort_order, is_published)
VALUES (@ch, @lv, 'Flashcards — Atomes et ions', 'flashcards-atomes-ions', 1, 1)
ON DUPLICATE KEY UPDATE title=VALUES(title), chapter_id=VALUES(chapter_id), is_published=1;

SET @d := (SELECT id FROM flashcard_decks WHERE slug='flashcards-atomes-ions');
DELETE FROM flashcards WHERE deck_id=@d;

INSERT INTO flashcards (deck_id, front_text, back_text, sort_order) VALUES
(@d, 'Où se concentre la masse d’un atome ?', 'Dans le **noyau** : les électrons sont environ 2000 fois plus légers que les nucléons.', 1),
(@d, 'Quelle est la charge d’un proton ? D’un électron ?', 'Proton : **positive** (+e). Électron : **négative** (-e). Neutron : nulle.', 2),
(@d, 'Que représente le numéro atomique $Z$ ?', 'Le nombre de **protons** dans le noyau.', 3),
(@d, 'Comment appelle-t-on un atome qui a perdu un électron ?', 'Un **cation** (ion positif).', 4),
(@d, 'Donne la formule de l’ion chlorure.', 'Cl avec un électron gagné : $Cl^-$.', 5),
(@d, 'Donne la formule de la molécule d’eau.', '$H_2O$ : deux hydrogènes et un oxygène.', 6),
(@d, 'Comment calcule-t-on le nombre de neutrons ?', 'Neutrons = $A - Z$ (masse moins protons).', 7),
(@d, 'L’ion cuivre $Cu^{2+}$ a gagné ou perdu des électrons ?', 'Il a **perdu deux électrons** (charge 2+).', 8);

-- ---------------------------------------------------------------------------
-- 7. SAVOIRS ET SAVOIR-FAIRE
-- ---------------------------------------------------------------------------
INSERT INTO chapter_skills (chapter_id, category, label, sort_order, is_published)
SELECT @ch, t.category, t.label, t.sort_order, 1
FROM (
  SELECT 'Savoir' AS category, 'Connaître la structure de l’atome et le vocabulaire (noyau, proton, neutron, électron).' AS label, 1 AS sort_order
  UNION ALL SELECT 'Savoir', 'Distinguer atome, ion et molécule.', 2
  UNION ALL SELECT 'Savoir', 'Connaître les ordres de grandeur des dimensions de l’atome.', 3
  UNION ALL SELECT 'Savoir-faire', 'Écrire la formule d’un ion à partir de sa composition.', 4
  UNION ALL SELECT 'Savoir-faire', 'Écrire la formule d’une molécule à partir de son nom ou d’une description.', 5
  UNION ALL SELECT 'Savoir-faire', 'Utiliser le tableau périodique pour prévoir la charge d’un ion.', 6
) t
WHERE NOT EXISTS (SELECT 1 FROM chapter_skills WHERE chapter_id=@ch AND label=t.label);

-- ---------------------------------------------------------------------------
-- 8. TRAVAIL À FAIRE (lié au chapitre)
-- ---------------------------------------------------------------------------
INSERT INTO assignments (level_id, subject_id, chapter_id, title, kind, instructions, due_date, priority, is_published)
SELECT @lv, @sub, @ch, 'Pour lundi — Exercices 5 à 9 p. 42', 'Devoir maison',
'## À faire
- Exercices 5 et 6 : structure de l’atome.
- Exercice 7 : écrire la formule des ions.
- Exercice 8 : type Brevet, à rédiger.

> Penser à justifier chaque réponse avec le vocabulaire du cours (proton, neutron, électron).',
DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'haute', 1
WHERE NOT EXISTS (SELECT 1 FROM assignments WHERE chapter_id=@ch AND title='Pour lundi — Exercices 5 à 9 p. 42');
