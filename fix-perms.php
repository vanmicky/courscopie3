<?php
/**
 * Correction des permissions après un envoi FTP / une extraction d'archive.
 *
 *   https://ton-domaine.fr/fix-perms.php          → simulation (rien n'est modifié)
 *   https://ton-domaine.fr/fix-perms.php?go=1     → applique les corrections
 *
 * Objectif : dossiers en 755, fichiers en 644. C'est ce qu'attend Apache sur un
 * hébergement mutualisé ; un fichier en 600 provoque une erreur 403 et le
 * navigateur refuse alors le CSS et le JS.
 *
 * ⚠️ SUPPRIME CE FICHIER APRÈS USAGE.
 */

declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');

$agir = isset($_GET['go']) && $_GET['go'] === '1';
$racine = __DIR__;

$dossiers = 0;
$fichiers = 0;
$modifies = 0;
$echecs = [];

$chmodPossible = function_exists('chmod') && !in_array('chmod', array_map('trim', explode(',', (string)ini_get('disable_functions'))), true);

if (!$chmodPossible) {
    echo '<!doctype html><meta charset="utf-8"><title>Permissions</title>';
    echo '<body style="font:16px/1.6 system-ui;padding:2rem;max-width:52rem;margin:auto">';
    echo '<h1>chmod désactivé</h1>';
    echo '<p>L’hébergeur interdit <code>chmod()</code> depuis PHP. Passe par le gestionnaire de fichiers :</p>';
    echo '<ol><li>sélectionne tous les fichiers du site ;</li><li>clic droit → <strong>Permissions</strong> ;</li>';
    echo '<li><strong>755</strong> pour les dossiers, <strong>644</strong> pour les fichiers.</li></ol>';
    echo '</body>';
    exit;
}

/** Parcours récursif. */
$parcourir = function (string $dossier) use (&$parcourir, &$dossiers, &$fichiers, &$modifies, &$echecs, $agir): void {
    $entrees = @scandir($dossier);
    if ($entrees === false) {
        $echecs[] = ['chemin' => $dossier, 'raison' => 'lecture impossible'];
        return;
    }
    // Le dossier lui-même (sauf la racine, gérée par l'hébergeur).
    $mode = @fileperms($dossier);
    if ($mode !== false) {
        $actuel = $mode & 0777;
        $dossiers++;
        if ($actuel !== 0755) {
            if ($agir) {
                if (@chmod($dossier, 0755)) $modifies++;
                else $echecs[] = ['chemin' => $dossier, 'raison' => 'chmod refusé'];
            } else {
                $modifies++;
            }
        }
    }
    foreach ($entrees as $entree) {
        if ($entree === '.' || $entree === '..') continue;
        $chemin = $dossier . '/' . $entree;
        if (is_dir($chemin)) {
            $parcourir($chemin);
            continue;
        }
        $fichiers++;
        $mode = @fileperms($chemin);
        if ($mode === false) continue;
        $actuel = $mode & 0777;
        // Les scripts shell doivent rester executables : 755, pas 644.
        $cible = in_array(strtolower(pathinfo($chemin, PATHINFO_EXTENSION)), ['sh', 'command'], true) ? 0755 : 0644;
        if ($actuel === $cible) continue;
        if ($agir) {
            if (@chmod($chemin, $cible)) $modifies++;
            else $echecs[] = ['chemin' => $chemin, 'raison' => 'chmod refusé'];
        } else {
            $modifies++;
        }
    }
};

$parcourir($racine);

// Contrôle final : les fichiers qui posaient problème.
$verifications = [];
foreach (['dist/output.css', 'assets/js/main.js', 'assets/js/mobile-menu.js', 'includes/config.php'] as $f) {
    $chemin = $racine . '/' . $f;
    if (!is_file($chemin)) continue;
    $verifications[] = ['fichier' => $f, 'mode' => substr(sprintf('%o', fileperms($chemin)), -4), 'lisible' => is_readable($chemin)];
}

$echecsAffiches = array_slice($echecs, 0, 15);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Permissions des fichiers</title>
<style>
  body { font: 15px/1.6 -apple-system, "Segoe UI", Roboto, sans-serif; color: #14161a; background: #f6f7f9; margin: 0; padding: 2rem 1rem; }
  .wrap { max-width: 54rem; margin: 0 auto; }
  h1 { font-size: 1.5rem; margin: 0 0 .3rem; }
  .sub { color: #5b6270; margin: 0 0 1.5rem; }
  .bilan { padding: .9rem 1.1rem; border-radius: 10px; font-weight: 700; margin-bottom: 1.4rem; }
  .ok { background: #ecfdf5; border: 1px solid #86efac; color: #14532d; }
  .ko { background: #fffbeb; border: 1px solid #fcd34d; color: #7c2d12; }
  .grille { display: grid; grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr)); gap: 1px; background: #e4e7ec; border: 1px solid #e4e7ec; border-radius: 12px; overflow: hidden; margin-bottom: 1.5rem; }
  .cell { background: #fff; padding: .8rem 1rem; }
  .cell span { display: block; font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; font-weight: 700; }
  .cell strong { font-size: 1.4rem; font-variant-numeric: tabular-nums; }
  table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e4e7ec; border-radius: 12px; overflow: hidden; }
  th, td { padding: .55rem .8rem; text-align: left; border-bottom: 1px solid #e4e7ec; }
  th { background: #f9fafb; font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; }
  tr:last-child td { border-bottom: 0; }
  code { font-family: ui-monospace, Menlo, monospace; background: #f2f4f7; padding: .1rem .3rem; border-radius: 4px; font-size: .85em; }
  a.btn { display: inline-block; margin-top: 1.4rem; padding: .7rem 1.2rem; border-radius: 8px; background: #18181b; color: #fff; text-decoration: none; font-weight: 700; }
  a.btn:hover { background: #2c2c31; }
  .loupe { color: #6b7280; font-size: .85rem; }
</style>
</head>
<body>
<div class="wrap">
  <h1>Permissions des fichiers</h1>
  <p class="sub">Dossier analysé : <code><?= htmlspecialchars($racine) ?></code></p>

  <p class="bilan <?= $agir ? 'ok' : 'ko' ?>">
    <?= $agir
      ? ($echecs ? count($echecs) . ' correction(s) impossible(s).' : 'Corrections appliquées.')
      : $modifies . ' fichier(s) ou dossier(s) à corriger. Aucune modification n’a encore été faite.' ?>
  </p>

  <div class="grille">
    <div class="cell"><span>Dossiers</span><strong><?= $dossiers ?></strong></div>
    <div class="cell"><span>Fichiers</span><strong><?= $fichiers ?></strong></div>
    <div class="cell"><span><?= $agir ? 'Corrigés' : 'À corriger' ?></span><strong><?= $modifies ?></strong></div>
    <div class="cell"><span>Échecs</span><strong><?= count($echecs) ?></strong></div>
  </div>

  <table>
    <thead><tr><th>Fichier</th><th>Droits</th><th>État</th></tr></thead>
    <tbody>
      <?php foreach ($verifications as $v): ?>
        <tr>
          <td><code><?= htmlspecialchars($v['fichier']) ?></code></td>
          <td><?= htmlspecialchars($v['mode']) ?></td>
          <td><?= $v['lisible'] ? '✓' : '✗' ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php if ($echecsAffiches): ?>
    <h2 style="font-size:1rem;margin-top:1.6rem">Fichiers non modifiables</h2>
    <table>
      <thead><tr><th>Chemin</th><th>Raison</th></tr></thead>
      <tbody>
        <?php foreach ($echecsAffiches as $e): ?>
          <tr><td><code><?= htmlspecialchars(str_replace($racine, '', $e['chemin'])) ?></code></td><td><?= htmlspecialchars($e['raison']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <?php if (!$agir): ?>
    <a class="btn" href="?go=1">Appliquer les corrections (755 / 644)</a>
  <?php else: ?>
    <p style="margin-top:1.4rem"><a href="diag.php">→ Relancer le diagnostic</a></p>
  <?php endif; ?>

  <p class="loupe" style="margin-top:2rem">
    <strong>Supprime ce fichier</strong> (<code>fix-perms.php</code>) une fois les permissions corrigées.
  </p>
</div>
</body>
</html>
