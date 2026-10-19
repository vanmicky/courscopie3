-- Migration V53 -> V54
-- QCM, flashcards et exercices deviennent gerables depuis l'administration.
-- Les tables existent deja depuis V51 ; ce fichier ajoute un contenu de test utile.

INSERT INTO qcms(level_id, chapter_id, title, slug, is_published)
SELECT l.id, c.id, 'QCM découverte - La matière', 'qcm-decouverte-la-matiere', 1
FROM levels l
JOIN chapters c ON c.slug='cinquieme-ch01-la-matiere-autour-de-nous'
WHERE l.slug='cinquieme'
ON DUPLICATE KEY UPDATE title=VALUES(title), level_id=VALUES(level_id), chapter_id=VALUES(chapter_id), is_published=VALUES(is_published);

INSERT INTO qcm_questions(qcm_id, question_text, explanation, sort_order)
SELECT q.id, 'Un objet est constitué de matière s’il possède une masse et occupe un volume. Cette affirmation est-elle correcte ?', 'Oui : la masse et le volume sont deux indices essentiels pour décrire la matière.', 1
FROM qcms q
WHERE q.slug='qcm-decouverte-la-matiere'
AND NOT EXISTS (SELECT 1 FROM qcm_questions qq WHERE qq.qcm_id=q.id AND qq.sort_order=1);

INSERT INTO qcm_choices(question_id, choice_label, choice_text, is_correct, sort_order)
SELECT qq.id, 'A', 'Oui', 1, 1 FROM qcm_questions qq JOIN qcms q ON q.id=qq.qcm_id WHERE q.slug='qcm-decouverte-la-matiere' AND qq.sort_order=1
AND NOT EXISTS (SELECT 1 FROM qcm_choices qc WHERE qc.question_id=qq.id);
INSERT INTO qcm_choices(question_id, choice_label, choice_text, is_correct, sort_order)
SELECT qq.id, 'B', 'Non', 0, 2 FROM qcm_questions qq JOIN qcms q ON q.id=qq.qcm_id WHERE q.slug='qcm-decouverte-la-matiere' AND qq.sort_order=1
AND NOT EXISTS (SELECT 1 FROM qcm_choices qc WHERE qc.question_id=qq.id AND qc.choice_label='B');

INSERT INTO flashcard_decks(level_id, chapter_id, title, slug, is_published)
SELECT l.id, c.id, 'Flashcards - La matière', 'flashcards-la-matiere', 1
FROM levels l
JOIN chapters c ON c.slug='cinquieme-ch01-la-matiere-autour-de-nous'
WHERE l.slug='cinquieme'
ON DUPLICATE KEY UPDATE title=VALUES(title), level_id=VALUES(level_id), chapter_id=VALUES(chapter_id), is_published=VALUES(is_published);

INSERT INTO flashcards(deck_id, front_text, back_text, sort_order)
SELECT d.id, 'Qu’est-ce que la matière ?', 'La matière est ce qui possède une masse et occupe un volume.', 1
FROM flashcard_decks d
WHERE d.slug='flashcards-la-matiere'
AND NOT EXISTS (SELECT 1 FROM flashcards f WHERE f.deck_id=d.id AND f.sort_order=1);

INSERT INTO exercises(chapter_id, title, statement, correction, difficulty, is_published)
SELECT c.id, 'Identifier la matière', 'Citer deux objets constitués de matière, puis justifier avec les mots masse et volume.', 'Exemple : une règle et un cahier sont constitués de matière car ils possèdent une masse et occupent un volume.', 'facile', 1
FROM chapters c
WHERE c.slug='cinquieme-ch01-la-matiere-autour-de-nous'
AND NOT EXISTS (SELECT 1 FROM exercises e WHERE e.chapter_id=c.id AND e.title='Identifier la matière');
