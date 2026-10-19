-- Migration V52 -> V53
-- Travail a faire + fichiers.
-- Les tables files, assignment_files et assignment_links existent deja depuis V51.
-- Ces index accelerent l'affichage public et l'admin lorsque les documents augmentent.

CREATE INDEX idx_files_level ON files(level_id);
CREATE INDEX idx_files_chapter ON files(chapter_id);
CREATE INDEX idx_assignment_files_assignment ON assignment_files(assignment_id);
CREATE INDEX idx_assignment_links_assignment ON assignment_links(assignment_id);
CREATE INDEX idx_assignments_due_date ON assignments(due_date);
