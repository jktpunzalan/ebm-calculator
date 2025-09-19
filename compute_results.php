<?php
// compute_results.php — supports two modes:
// 1) VIEW (GET): compute_results.php?study_id=123  -> loads saved study + article, shows results
// 2) POST: from article_form.php -> validates, computes, saves to ebm.studies, shows results

require_once __DIR__ . '/db.php'; // resilient bootstrap (auto-creates ebm + schema if missing)

// ---------- session+token -----------
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ---------- helpers ----------
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function fmt3($v){ return $v===null ? '—' : number_format((float)$v, 3, '.', ''); }
function safe_div($a,$b){ return ($b===0 || $b===0.0 || $b===null) ? null : ($a / $b); }
function format_authors($authors_json, $max_show = 10){
  if (!$authors_json) return '—';
  $arr = json_decode($authors_json, true);
  if (!is_array($arr)) return '—';
  $names = [];
  foreach ($arr as $a) {
    $given  = trim($a['given']  ?? '');
    $family = trim($a['family'] ?? '');
    if ($family !== '' || $given !== '') {
      $initials = '';
      if ($given !== '') {
        $parts = preg_split('/\s+/', $given);
        foreach ($parts as $p) { if ($p !== '') $initials .= mb_strtoupper(mb_substr($p,0,1)); }
      }
      $names[] = trim($family . ($initials ? ' ' . $initials : ''));
    }
  }
  if (!$names) return '—';
  if (count($names) > $max_show) return implode(', ', array_slice($names,0,$max_show)) . ' et al.';
  return implode(', ', $names);
}

// ---------- default vars ----------
$mode_view = false;   // true when loaded by study_id
$errors = [];

$article_row = null;
$study_row   = null;

// Data fields (filled by either POST compute or DB load)
$article_id = null; $doi = ''; $title='—'; $journal='—'; $pub_year=null; $publisher='—'; $authors_s='—';
$treatment=''; $control=''; $outcome='';
$A=null; $B=null; $C=null; $D=null; $N1=null; $N0=null;
$baseline=null;
$Re=null; $Ru=null; $RR=null; $ARR=null; $NNT=null; $NNH=null;
$TreatedRisk_ind=null; $ARR_ind=null;
$interp=''; $interp2='';

// ---------- MODE 1: VIEW (GET ?study_id=...) ----------
if (isset($_GET['study_id'])) {
  $mode_view = true;
  $sid = (int)$_GET['study_id'];
  $q = $pdo->prepare("
    SELECT s.*,
           a.id AS article_id, a.doi, a.article_title, a.journal_title, a.authors_json,
           a.pub_year, a.publisher
    FROM studies s
    JOIN articles a ON a.id = s.article_id
    WHERE s.id = :id
    LIMIT 1
  ");
  $q->execute([':id' => $sid]);
  $study_row = $q->fetch();

  if (!$study_row) {
    http_response_code(404);
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Not found</title>
          <style>body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;margin:2rem}</style></head>
          <body><h2>Study not found.</h2><p><a href='studies_list.php'>Back to list</a></p></body></html>";
    exit;
  }

  // Populate fields from DB
  $article_id = (int)$study_row['article_id'];
  $treatment  = $study_row['treatment'];
  $control    = $study_row['control'];
  $outcome    = $study_row['outcome'];

  $A = (int)$study_row['A_exposed_yes'];
  $B = (int)$study_row['B_exposed_no'];
  $C = (int)$study_row['C_unexposed_yes'];
  $D = (int)$study_row['D_unexposed_no'];
  $N1 = (int)$study_row['N1_exposed_total'];
  $N0 = (int)$study_row['N0_unexposed_total'];

  $baseline = $study_row['baseline_risk']!==null ? (float)$study_row['baseline_risk'] : null;

  // Use stored metrics if present; otherwise compute
  $Re  = $study_row['risk_exposed']   !== null ? (float)$study_row['risk_exposed']   : safe_div($A,$N1);
  $Ru  = $study_row['risk_unexposed'] !== null ? (float)$study_row['risk_unexposed'] : safe_div($C,$N0);
  $RR  = $study_row['rr']  !== null ? (float)$study_row['rr']  : (($Ru>0 && $Re!==null && $Ru!==null) ? $Re/$Ru : null);
  $ARR = $study_row['arr'] !== null ? (float)$study_row['arr'] : (($Re!==null && $Ru!==null) ? ($Ru-$Re) : null);
  $NNT = $study_row['nnt'] !== null ? (int)$study_row['nnt'] : ( ($ARR>0) ? (int)ceil(1/abs($ARR)) : null );
  $NNH = $study_row['nnh'] !== null ? (int)$study_row['nnh'] : ( ($ARR<0) ? (int)ceil(1/abs($ARR)) : null );

  $TreatedRisk_ind = $study_row['treated_risk_ind']!==null ? (float)$study_row['treated_risk_ind'] : (($baseline!==null && $RR!==null) ? max(0,min(1,$baseline*$RR)) : null);
  $ARR_ind         = $study_row['arr_ind']         !== null ? (float)$study_row['arr_ind']         : (($baseline!==null && $TreatedRisk_ind!==null) ? ($baseline - $TreatedRisk_ind) : null);

  // Interpretations
  if ($ARR === null) {
    $interp = "Insufficient data to compute interpretation.";
  } elseif ($ARR > 0 && $NNT !== null) {
    $interp = "For every {$NNT} treated with {$treatment}, you can prevent one {$outcome}.";
  } elseif ($ARR < 0 && $NNH !== null) {
    $interp = "Caution: For every {$NNH} treated with {$treatment}, one additional {$outcome} may occur (harm).";
  } else {
    $interp = "No absolute risk difference detected between {$treatment} and {$control} for {$outcome}.";
  }

  if ($ARR_ind !== null && $ARR_ind > 0) {
    $interp2 = "For every ".(int)ceil(1/$ARR_ind)." like your patient (baseline risk ".fmt3($baseline).") treated with {$treatment}, one {$outcome} may be prevented.";
  } elseif ($ARR_ind !== null && $ARR_ind < 0) {
    $interp2 = "Caution: For every ".(int)ceil(1/abs($ARR_ind))." like your patient (baseline risk ".fmt3($baseline).") treated with {$treatment}, one additional {$outcome} may occur (harm).";
  } elseif ($baseline !== null && $RR === null) {
    $interp2 = "Risk Ratio (RR) was not computable from the journal counts, so individualized estimates cannot be generated.";
  } else {
    $interp2 = "No Baseline Risk provided or no absolute risk difference expected.";
  }

  // Article metadata
  $doi       = $study_row['doi'] ?? '';
  $title     = $study_row['article_title'] ?? '—';
  $journal   = $study_row['journal_title'] ?? '—';
  $pub_year  = $study_row['pub_year'] ?? null;
  $publisher = $study_row['publisher'] ?? '—';
  $authors_s = format_authors($study_row['authors_json'] ?? null);

} else {
  // ---------- MODE 2: POST (compute & save) ----------
  // small input parsers
  $article_id = $_POST['article_id'] ?? null;
  $doi        = trim($_POST['doi'] ?? '');
  $treatment  = trim($_POST['treatment'] ?? '');
  $control    = trim($_POST['control'] ?? '');
  $outcome    = trim($_POST['outcome'] ?? '');
  $baseline   = isset($_POST['baseline_risk']) && $_POST['baseline_risk'] !== '' ? (float)$_POST['baseline_risk'] : null;

  $A  = isset($_POST['A'])  && $_POST['A']  !== '' ? (int)$_POST['A']  : null;
  $B  = isset($_POST['B'])  && $_POST['B']  !== '' ? (int)$_POST['B']  : null;
  $C  = isset($_POST['C'])  && $_POST['C']  !== '' ? (int)$_POST['C']  : null;
  $D  = isset($_POST['D'])  && $_POST['D']  !== '' ? (int)$_POST['D']  : null;
  $N1 = isset($_POST['N1']) && $_POST['N1'] !== '' ? (int)$_POST['N1'] : null;
  $N0 = isset($_POST['N0']) && $_POST['N0'] !== '' ? (int)$_POST['N0'] : null;

  // validation
  if ($N1 === null) $errors[] = "Total Exposed (N1) is required.";
  if ($N0 === null) $errors[] = "Total Unexposed (N0) is required.";
  $hasAC = ($A !== null && $C !== null);
  $hasBD = ($B !== null && $D !== null);
  if (!$hasAC && !$hasBD) $errors[] = "Provide either (A & C) or (B & D).";
  if ($A !== null && $A > $N1) $errors[] = "A cannot exceed N1.";
  if ($B !== null && $B > $N1) $errors[] = "B cannot exceed N1.";
  if ($C !== null && $C > $N0) $errors[] = "C cannot exceed N0.";
  if ($D !== null && $D > $N0) $errors[] = "D cannot exceed N0.";

  // derive missing
  if ($N1 !== null) {
    if ($A === null && $B !== null) $A = $N1 - $B;
    if ($B === null && $A !== null) $B = $N1 - $A;
    if ($A !== null && $B !== null && ($A + $B) !== $N1) $errors[] = "For Exposed row, A + B must equal N1.";
  }
  if ($N0 !== null) {
    if ($C === null && $D !== null) $C = $N0 - $D;
    if ($D === null && $C !== null) $D = $N0 - $C;
    if ($C !== null && $D !== null && ($C + $D) !== $N0) $errors[] = "For Unexposed row, C + D must equal N0.";
  }
  if ($A === null || $B === null || $C === null || $D === null) $errors[] = "Unable to complete the 2×2 counts. Ensure totals and one valid pair ((A & C) or (B & D)) are provided.";
  if ($baseline !== null && ($baseline < 0 || $baseline > 1)) $errors[] = "Baseline Risk must be between 0 and 1.";

  if ($errors) {
    http_response_code(400);
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Input Error</title>
          <style>body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;margin:2rem}
          .card{max-width:900px;margin:0 auto;padding:1rem 1.25rem;border:1px solid #f5c2c7;background:#fff3f3;color:#842029;border-radius:12px}
          a.btn{display:inline-block;margin-top:.8rem;padding:.55rem .8rem;border:1px solid #0d6efd;background:#0d6efd;color:#fff;border-radius:8px;text-decoration:none}
          </style></head><body>
          <div class='card'><h1>There were issues with your inputs</h1><ul>";
    foreach($errors as $e){ echo "<li>".h($e)."</li>"; }
    echo "</ul><a class='btn' href='javascript:history.back()'>Go Back</a></div></body></html>";
    exit;
  }

  // compute
  $Re = safe_div($A,$N1);
  $Ru = safe_div($C,$N0);
  $RR  = ($Re !== null && $Ru !== null && $Ru > 0) ? ($Re/$Ru) : null;
  $ARR = ($Re !== null && $Ru !== null) ? ($Ru - $Re) : null;
  if ($ARR !== null && $ARR != 0) {
    $x = ceil(1/abs($ARR));
    if ($ARR > 0) $NNT = (int)$x; else $NNH = (int)$x;
  }

  if ($ARR === null) {
    $interp = "Insufficient data to compute interpretation.";
  } elseif ($ARR > 0 && $NNT !== null) {
    $interp = "For every {$NNT} treated with {$treatment}, you can prevent one {$outcome}.";
  } elseif ($ARR < 0 && $NNH !== null) {
    $interp = "Caution: For every {$NNH} treated with {$treatment}, one additional {$outcome} may occur (harm).";
  } else {
    $interp = "No absolute risk difference detected between {$treatment} and {$control} for {$outcome}.";
  }

  if ($baseline !== null && $RR !== null) {
    $TreatedRisk_ind = max(0, min(1, $baseline * $RR));
    $ARR_ind = $baseline - $TreatedRisk_ind;
  }
  if ($ARR_ind !== null && $ARR_ind > 0) {
    $interp2 = "For every ".(int)ceil(1/$ARR_ind)." like your patient (baseline risk ".fmt3($baseline).") treated with {$treatment}, one {$outcome} may be prevented.";
  } elseif ($ARR_ind !== null && $ARR_ind < 0) {
    $interp2 = "Caution: For every ".(int)ceil(1/abs($ARR_ind))." like your patient (baseline risk ".fmt3($baseline).") treated with {$treatment}, one additional {$outcome} may occur (harm).";
  } elseif ($baseline !== null && $RR === null) {
    $interp2 = "Risk Ratio (RR) was not computable from the journal counts, so individualized estimates cannot be generated.";
  } else {
    $interp2 = "No Baseline Risk provided or no absolute risk difference expected.";
  }

  // link to article by id or via doi
  if (!$article_id && $doi !== '') {
    $st = $pdo->prepare("SELECT id FROM articles WHERE doi = :doi LIMIT 1");
    $st->execute([':doi'=>$doi]);
    $article_id = $st->fetchColumn();
  }

  // save study
  if ($article_id) {
    $ins = $pdo->prepare("
      INSERT INTO studies
      (article_id, treatment, control, outcome,
       A_exposed_yes, B_exposed_no, C_unexposed_yes, D_unexposed_no,
       N1_exposed_total, N0_unexposed_total,
       baseline_risk, risk_exposed, risk_unexposed, rr, arr, nnt, nnh,
       treated_risk_ind, arr_ind)
      VALUES
      (:article_id, :treat, :ctrl, :outcome,
       :A, :B, :C, :D,
       :N1, :N0,
       :baseline, :Re, :Ru, :RR, :ARR, :NNT, :NNH,
       :TRi, :ARRi)
    ");
    $ins->execute([
      ':article_id'=>$article_id, ':treat'=>$treatment, ':ctrl'=>$control, ':outcome'=>$outcome,
      ':A'=>$A, ':B'=>$B, ':C'=>$C, ':D'=>$D, ':N1'=>$N1, ':N0'=>$N0,
      ':baseline'=>$baseline, ':Re'=>$Re, ':Ru'=>$Ru, ':RR'=>$RR, ':ARR'=>$ARR,
      ':NNT'=>$NNT, ':NNH'=>$NNH, ':TRi'=>$TreatedRisk_ind, ':ARRi'=>$ARR_ind
    ]);
  }

  // get article metadata for display
  if ($article_id) {
    $q2 = $pdo->prepare("SELECT * FROM articles WHERE id = :id LIMIT 1");
    $q2->execute([':id'=>$article_id]);
    $article_row = $q2->fetch();
  }
  $doi       = $article_row['doi'] ?? $doi;
  $title     = $article_row['article_title'] ?? '—';
  $journal   = $article_row['journal_title'] ?? '—';
  $pub_year  = $article_row['pub_year'] ?? null;
  $publisher = $article_row['publisher'] ?? '—';
  $authors_s = format_authors($article_row['authors_json'] ?? null);
}

// ---------- render ----------
$doi_disp = $doi ?: '—';
$doi_url  = ($doi_disp && $doi_disp !== '—') ? 'https://doi.org/' . rawurlencode($doi_disp) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Results — RR/ARR/NNT & Individualized</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 2rem; line-height:1.45; background:#fcfcfc; }
  .wrap { max-width: 1040px; margin: 0 auto; }
  .section { background:#fff; border:1px solid #e6e6e6; border-radius:12px; padding:1rem 1.25rem; margin-bottom:1rem; }
  h2 { margin:.2rem 0 .6rem; font-size:1.2rem; }
  .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(230px,1fr)); gap:10px; }
  .muted { color:#666; font-size:.92rem; }
  table { border-collapse: collapse; width:100%; margin:.4rem 0 1rem; background:#fff; }
  th, td { border:1px solid #ececec; padding:.55rem .6rem; text-align:center; }
  th { background:#fafafa; }
  .emph { border: 1px solid #cfe2ff; background:#f5f9ff; padding:.8rem 1rem; border-radius:10px; font-weight:600; }
  .emph strong { font-size:1.02rem; }
  .toolbar { display:flex; gap:10px; }
  .btn { padding:.6rem .9rem; border-radius:8px; border:1px solid #0d6efd; background:#0d6efd; color:#fff; text-decoration:none; display:inline-block; }
  .btn.secondary { background:#f4f4f4; color:#222; border-color:#999; }
  kbd { background:#eee; border-radius:4px; padding:0 .3rem; }
</style>
</head>
<body>
<div class="wrap">

  <div class="section">
    <h2>Study Summary</h2>
    <div class="grid">
      <div><strong>Title:</strong> <?= h($title) ?></div>
      <div><strong>Authors:</strong> <?= h($authors_s) ?></div>
      <div><strong>Journal:</strong> <?= h($journal) ?></div>
      <div><strong>Year:</strong> <?= $pub_year ? (int)$pub_year : '—' ?></div>
      <div><strong>Publisher:</strong> <?= h($publisher) ?></div>
      <div><strong>DOI:</strong> <?= $doi_url ? ('<a href="'.h($doi_url).'" target="_blank" rel="noopener">'.h($doi_disp).'</a>') : h($doi_disp) ?></div>
    </div>
  </div>

  <div class="section">
    <h2>2×3 Table</h2>
    <table aria-label="2x3 table">
      <thead>
        <tr>
          <th></th>
          <th><?= h($outcome) ?></th>
          <th><?= 'No ' . h($outcome) ?></th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th>Exposed (Treatment: <?= h($treatment) ?>)</th>
          <td><?= (int)$A ?></td>
          <td><?= (int)$B ?></td>
          <td><?= (int)$N1 ?></td>
        </tr>
        <tr>
          <th>Unexposed (Control: <?= h($control) ?>)</th>
          <td><?= (int)$C ?></td>
          <td><?= (int)$D ?></td>
          <td><?= (int)$N0 ?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="section">
    <h2>Section 1 — Computation based on Journal</h2>
    <div class="grid">
      <div><strong>Risk (Exposed):</strong> <?= fmt3($Re) ?></div>
      <div><strong>Risk (Unexposed):</strong> <?= fmt3($Ru) ?></div>
      <div><strong>Risk Ratio (RR):</strong> <?= fmt3($RR) ?></div>
      <div><strong>Absolute Risk Reduction (ARR = Ru − Re):</strong> <?= fmt3($ARR) ?></div>
      <div><strong>NNT:</strong> <?= $NNT!==null ? (int)$NNT : '—' ?></div>
      <div><strong>NNH (if harm):</strong> <?= $NNH!==null ? (int)$NNH : '—' ?></div>
    </div>
    <p class="emph"><strong>Interpretation:</strong> <?= h($interp) ?></p>
    <p class="muted">Notes: ARR uses <kbd>Ru − Re</kbd>. NNT/NNH uses <kbd>ceil(1/|ARR|)</kbd>. If a denominator is zero, that metric is “—”.</p>
  </div>

  <div class="section">
    <h2>Section 2 — Individualizing Results</h2>
    <?php if ($ARR_ind === null && $baseline === null): ?>
      <p class="emph"><strong>Individualized Interpretation:</strong> No Baseline Risk provided. Enter a Baseline Risk (0–1) in the form to individualize.</p>
    <?php elseif ($ARR_ind === null && $RR === null): ?>
      <p class="emph"><strong>Individualized Interpretation:</strong> RR not computable from journal counts, so individualized estimates cannot be generated.</p>
    <?php else: ?>
      <div class="grid">
        <div><strong>Baseline Risk (Control):</strong> <?= fmt3($baseline) ?></div>
        <div><strong>Study RR:</strong> <?= fmt3($RR) ?></div>
        <div><strong>Estimated Treated Risk:</strong> <?= fmt3($TreatedRisk_ind) ?></div>
        <div><strong>Individualized ARR:</strong> <?= fmt3($ARR_ind) ?></div>
      </div>
      <?php
        if ($ARR_ind !== null && $ARR_ind > 0) {
          $nnt_ind = (int)ceil(1/$ARR_ind);
          $interp2 = "For every {$nnt_ind} like your patient (baseline risk ".fmt3($baseline).") treated with {$treatment}, one {$outcome} may be prevented.";
        } elseif ($ARR_ind !== null && $ARR_ind < 0) {
          $nnh_ind = (int)ceil(1/abs($ARR_ind));
          $interp2 = "Caution: For every {$nnh_ind} like your patient (baseline risk ".fmt3($baseline).") treated with {$treatment}, one additional {$outcome} may occur (harm).";
        }
      ?>
      <p class="emph"><strong>Individualized Interpretation:</strong> <?= h($interp2) ?></p>
      <p class="muted">Method: Treated Risk<sub>individual</sub> = BaselineRisk × RR; Individualized ARR = BaselineRisk − TreatedRisk<sub>individual</sub>.</p>
    <?php endif; ?>
  </div>

  <div class="section" style="display:flex;justify-content:space-between;align-items:center;">
    <div class="toolbar">
      <a class="btn" href="article_form.php">← New Study</a>
      <a class="btn" href="studies_list.php">View Previous Studies</a>
      <?php if ($mode_view && isset($sid) && $sid > 0): ?>
  <form method="post" action="delete_study.php"
        onsubmit="return confirm('Delete study #<?= (int)$sid ?>? This cannot be undone.');"
        style="margin-left:10px;">
    <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="study_id" value="<?= (int)$sid ?>">
    <a class="btn" style="border-color:#b00020;background:#b00020;">Delete</a>
  </form>
<?php endif; ?>
    </div>
  </div>

</div>
</body>
</html>
