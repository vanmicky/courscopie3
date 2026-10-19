<?php
require_once __DIR__ . '/includes/repository.php';
$filters = ['level' => trim((string)($_GET['level'] ?? '')), 'q' => trim((string)($_GET['q'] ?? ''))];
$levels = get_levels();
$assignments = get_assignments($filters);
$hasFilters = $filters['level'] !== '' || $filters['q'] !== '';
page_head('Travail à faire', 'Devoirs dynamiques par niveau avec support LaTeX.');
site_header();
?>
<main class="page-container student-page" data-work-global>
  <a href="<?= h(base_path('index.php')) ?>" class="nav-link">← Accueil</a>
  <section class="work-global-hero mt-5">
    <span class="badge">Agenda</span>
    <h1 class="page-title mt-4">Travail à faire</h1>
    <p class="muted">Ce qui est à préparer pour les prochaines séances : devoirs, lectures, TP et révisions.</p>
    <p class="library-count"><?= count($assignments) ?> travail<?= count($assignments) > 1 ? 'x' : '' ?> au programme</p>
  </section>
  <form class="work-filter-panel mt-8" method="get">
    <div class="work-filter-grid">
      <label>Rechercher<input type="search" name="q" value="<?= h($filters['q']) ?>" placeholder="Chapitre, QCM, TP, formule…"></label>
      <label>Niveau<select name="level"><option value="">Tous</option><?php foreach ($levels as $level): ?><option value="<?= h($level['slug']) ?>" <?= $filters['level'] === $level['slug'] ? 'selected' : '' ?>><?= h($level['short'] . ' — ' . $level['name']) ?></option><?php endforeach; ?></select></label>
      <div class="filter-submit"><button class="btn-primary" type="submit">Filtrer</button></div>
    </div>
    <?php if ($hasFilters): ?><a class="filter-reset" href="<?= h(base_path('travail-a-faire.php')) ?>">Réinitialiser les filtres</a><?php endif; ?>
  </form>
  <section class="work-board mt-8">
    <?php foreach ($assignments as $a): ?>
      <?php
        $files = !empty($a['id']) ? get_assignment_files((int)$a['id']) : [];
        $links = !empty($a['id']) ? get_assignment_links((int)$a['id']) : [];
        $due = !empty($a['due_date']) ? strtotime((string)$a['due_date']) : null;
        $isLate = $due !== null && $due < strtotime('today');
      ?>
      <article class="work-card<?= $isLate ? ' is-past' : '' ?>" data-work-card style="--card-accent:<?= h($a['accent'] ?? '#2563eb') ?>">
        <time class="work-date-badge" datetime="<?= h($a['due_date'] ?? '') ?>"><?= h(format_date_fr($a['due_date'] ?? null)) ?></time>
        <div>
          <div class="flex flex-wrap gap-2">
            <span class="work-level-badge"><?= h(($a['level_short'] ?? $a['level_slug'] ?? '') . ' · ' . ($a['level_name'] ?? '')) ?></span>
            <?php if (!empty($a['subject'])): ?><span class="work-kind-badge"><?= h($a['subject']) ?></span><?php endif; ?>
            <span class="work-kind-badge"><?= h($a['kind'] ?? 'Travail') ?></span>
            <?php if ($isLate): ?><span class="work-kind-badge is-past">Échéance passée</span><?php endif; ?>
          </div>
          <h2><?= h($a['title']) ?></h2>
          <div class="work-bullets mt-3"><?= render_text($a['instructions']) ?></div>
          <?php if ($files || $links): ?>
            <div class="file-chip-list mt-4">
              <?php foreach ($files as $file): ?><a class="file-chip" href="<?= h(base_path($file['path'])) ?>" target="_blank" rel="noopener"><?= h($file['assignment_label'] ?: $file['label'] ?: $file['original_name']) ?></a><?php endforeach; ?>
              <?php foreach ($links as $link): ?><a class="file-chip is-link" href="<?= h($link['url']) ?>" target="_blank" rel="noopener"><?= h($link['label']) ?></a><?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="work-actions">
          <label class="work-done">
            <input type="checkbox" data-work-done="<?= h(($a['level_slug'] ?? '') . '|' . ($a['due_date'] ?? '') . '|' . ($a['id'] ?? '')) ?>">
            <span>Fait</span>
          </label>
          <?php if (!empty($a['chapter_slug'])): ?><a href="<?= h(base_path('chapitre.php?slug=' . $a['chapter_slug'])) ?>">Chapitre</a><?php endif; ?>
          <?php if (!empty($a['level_slug'])): ?><a href="<?= h(base_path('niveau.php?slug=' . $a['level_slug'])) ?>">Niveau</a><?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
  <?php if ($assignments): ?>
    <p class="work-done-count" data-work-done-count role="status" aria-live="polite"></p>
  <?php endif; ?>
  <?php if (!$assignments): ?>
    <div class="student-empty mt-8">
      <h2>Aucun travail à afficher</h2>
      <p><?= $hasFilters ? 'Aucun devoir ne correspond à ces filtres.' : 'Rien n’est à préparer pour le moment.' ?></p>
      <?php if ($hasFilters): ?><a class="btn-secondary" href="<?= h(base_path('travail-a-faire.php')) ?>">Voir tout le travail</a><?php endif; ?>
    </div>
  <?php endif; ?>
</main>
<script src="<?= h(base_path('assets/js/work-done.js')) ?>" defer></script>
<?php site_footer(); ?>
