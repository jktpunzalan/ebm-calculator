<?php
// studies_list.php

// NEW: CSRF/session setup for safe deletes (place BEFORE any output)
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/db.php';

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function fmt3($v){ return $v===null ? '—' : number_format((float)$v, 3, '.', ''); }

// Search (treatment or outcome)
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$params = [];
$where = '';
if ($q !== '') {
  // Use distinct placeholders to avoid HY093
  $where = "WHERE s.treatment LIKE :like1 OR s.outcome LIKE :like2";
  $params[':like1'] = '%' . $q . '%';
  $params[':like2'] = '%' . $q . '%';
}

// Query (only the requested columns)
$sql = "
SELECT
  s.id,
  s.treatment,
  s.control,
  s.outcome,
  s.rr,
  s.arr,
  s.nnt,
  s.arr_ind,   -- for individualized NNT (if > 0)
  a.doi
FROM studies s
JOIN articles a ON a.id = s.article_id
{$where}
ORDER BY s.created_at DESC
LIMIT 300
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Studies — EBM</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  :root { --pad: 16px; }
  html, body { margin:0; padding:0; }
  body {
    font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    background:#fcfcfc; color:#111; overflow-x: hidden; /* prevent horizontal scroll */
  }
  .wrap {
  width: 75vw;              /* <-- main change: use three-quarters of viewport */
  max-width: 1600px;        /* guardrail for very large screens */
  margin: 0 auto;
  padding: 20px var(--pad);
}

/* Keep it comfy on smaller screens without horizontal scroll */
@media (max-width: 1024px) {
  .wrap {
    width: 95vw;            /* use most of the viewport on tablets/smaller laptops */
    max-width: none;        /* allow it to expand within 95vw */
  }
}
  h1 { margin: 6px 0 12px; font-size: 1.5rem; }
  .toolbar { display:flex; gap:10px; align-items:center; justify-content:space-between; flex-wrap: wrap; }
  .btn { padding:.55rem .85rem; border-radius:8px; border:1px solid #0d6efd; background:#0d6efd; color:#fff; text-decoration:none; display:inline-block; }
  .btn.muted { background:#f4f4f4; color:#222; border-color:#999; }
  .search { display:flex; gap:8px; flex-wrap: wrap; width:100%; max-width: 520px; }
  .search input[type="text"] { flex:1; min-width: 200px; padding:.55rem .65rem; border:1px solid #ccc; border-radius:8px; }
  .card { background:#fff; border:1px solid #e6e6e6; border-radius:12px; padding: 12px; margin-top:12px; }
  table { width:100%; border-collapse: collapse; table-layout: fixed; } /* fixed layout helps wrapping */
  th, td {
    border:1px solid #ececec; padding:.55rem .6rem; text-align:left; vertical-align: top;
    white-space: normal; word-break: break-word; overflow-wrap: anywhere; /* wrap long text/DOIs */
  }
  th { background:#fafafa; font-weight:600; }
  td a { color:#0d6efd; text-decoration:none; }
  td a:hover { text-decoration:underline; }
  .muted { color:#666; font-size:.92rem; }
  .danger-link { background:none;border:none;color:#b00020;padding:0;cursor:pointer; }
  .idcell { display:flex; flex-direction:column; gap:6px; align-items:flex-start; }
</style>
</head>
<body>
<div class="wrap">
  <div class="toolbar">
    <h1>Studies</h1>
    <a class="btn" href="article_form.php">+ New Study</a>
  </div>

  <!-- NEW: flash notice after delete redirect -->
  <?php if (isset($_GET['deleted'])): ?>
    <div class="muted" style="margin:6px 0 0;">
      Deleted study #<?= (int)$_GET['deleted'] ?>.
    </div>
  <?php endif; ?>

  <form class="search" method="get" action="studies_list.php">
    <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search treatment or outcome">
    <button class="btn" type="submit">Search</button>
    <?php if ($q !== ''): ?>
      <a class="btn muted" href="studies_list.php">Clear</a>
    <?php endif; ?>
  </form>

  <div class="card">
    <div class="muted" style="margin-bottom:8px;">Showing <?= count($rows) ?> result<?= count($rows)==1?'':'s' ?>.</div>
    <table aria-label="Studies table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Treatment</th>
          <th>Control</th>
          <th>Outcome</th>
          <th>RR</th>
          <th>ARR</th>
          <th>NNT</th>
          <th>Indiv. NNT</th>
          <th>DOI</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="9" class="muted">No studies found.</td></tr>
      <?php else: ?>
        <?php foreach ($rows as $r):
          // Individualized NNT from arr_ind (if > 0)
          $ind_nnt = '—';
          if ($r['arr_ind'] !== null && (float)$r['arr_ind'] > 0) {
            $ind_nnt = (int)ceil(1 / (float)$r['arr_ind']);
          }
          $doi = $r['doi'] ?? '';
          $doi_url = $doi ? ('https://doi.org/' . rawurlencode($doi)) : '';
        ?>
          <tr>
            <td class="idcell">
              <a href="compute_results.php?study_id=<?= (int)$r['id'] ?>" title="Open study #<?= (int)$r['id'] ?>">
                <?= (int)$r['id'] ?>
              </a>
              <!-- NEW: inline delete form under the ID (keeps table narrow; no new column) -->
              <form method="post" action="delete_study.php"
                    onsubmit="return confirm('Delete study #<?= (int)$r['id'] ?>? This cannot be undone.');">
                <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="study_id" value="<?= (int)$r['id'] ?>">
                <button type="submit" class="danger-link">Delete</button>
              </form>
            </td>
            <td><?= h($r['treatment']) ?></td>
            <td><?= h($r['control']) ?></td>
            <td><?= h($r['outcome']) ?></td>
            <td><?= fmt3($r['rr']) ?></td>
            <td><?= fmt3($r['arr']) ?></td>
            <td><?= $r['nnt'] ? (int)$r['nnt'] : '—' ?></td>
            <td><?= $ind_nnt ?></td>
            <td>
              <?php if ($doi_url): ?>
                <a href="<?= h($doi_url) ?>" target="_blank" rel="noopener"><?= h($doi) ?></a>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
