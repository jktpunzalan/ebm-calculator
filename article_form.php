<?php
// article_form.php
// Form to collect study inputs + 2x3 table, with DOI autofetch into the DB.
// This version:
//  - requires db.php (connects to ebm)
//  - checks if ebm.articles / ebm.studies exist; if not, runs schema.sql once

require_once __DIR__ . '/db.php'; // ensures $pdo exists

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

/**
 * Ensure schema exists by checking core tables; if missing, execute schema.sql
 */
function ensure_schema(PDO $pdo): void {
    try {
        // Check if core tables exist
        $needSchema = false;

        $chk1 = $pdo->query("SHOW TABLES LIKE 'articles'")->fetchColumn();
        $chk2 = $pdo->query("SHOW TABLES LIKE 'studies'")->fetchColumn();

        if (!$chk1 || !$chk2) {
            $needSchema = true;
        }

        if ($needSchema) {
            $schemaPath = __DIR__ . '/schema.sql';
            if (!is_readable($schemaPath)) {
                throw new RuntimeException("schema.sql not found or not readable.");
            }
            $sql = file_get_contents($schemaPath);
            if ($sql === false || trim($sql) === '') {
                throw new RuntimeException("schema.sql is empty or cannot be read.");
            }

            // Execute statements one by one for compatibility
            $statements = array_filter(array_map('trim', preg_split('/;(\R|$)/', $sql)));
            foreach ($statements as $stmt) {
                if ($stmt === '' || stripos($stmt, '--') === 0) continue; // skip empties / comments
                $pdo->exec($stmt);
            }
        }
    } catch (Throwable $e) {
        // Fail fast with a helpful message
        http_response_code(500);
        echo "<pre style='color:#b00020;font-family:monospace'>".
             "Schema check/creation failed.\n".
             "Error: " . h($e->getMessage()) . "\n".
             "Hint: Ensure MySQL user has privileges and schema.sql is present.\n".
             "</pre>";
        exit;
    }
}

// Run schema check/creation
ensure_schema($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>EBM Study Entry — RR/ARR/NNT & Individualization</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  :root { --gap:14px; }
  body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; line-height: 1.45; background:#fcfcfc; }
  .container { max-width: 1040px; margin: 0 auto; }
  .card { background:#fff; border:1px solid #e6e6e6; border-radius: 12px; padding: 1.2rem 1.4rem; margin-bottom: 16px; }
  h1 { margin:.2rem 0 1rem; font-size: 1.5rem; }
  h2 { margin:.2rem 0 .8rem; font-size: 1.2rem; }
  .muted { color:#666; font-size:.92rem; }
  .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(260px,1fr)); gap: var(--gap); }
  label { display:block; font-weight:600; margin:.2rem 0 .25rem; }
  input[type="text"], input[type="number"] { width:100%; padding:.6rem .65rem; border:1px solid #ccc; border-radius:8px; background:#fff; }
  input[readonly] { background:#f8f8f8; color:#444; }
  table { width:100%; border-collapse: collapse; }
  th, td { padding:.6rem .55rem; border:1px solid #ececec; text-align:center; }
  th { background:#fafafa; }
  .actions { display:flex; flex-wrap:wrap; gap:10px; }
  .btn { padding:.65rem .95rem; border-radius:8px; border:1px solid #0d6efd; background:#0d6efd; color:#fff; text-decoration:none; display:inline-block; cursor:pointer; }
  .btn.secondary { background:#f4f4f4; color:#222; border-color:#999; }
  .toolbar { display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom: 16px; }
</style>
</head>
<body>
<div class="container">

  <div class="toolbar">
    <h1>EBM Study Entry</h1>
    <a class="btn secondary" href="studies_list.php">View Previous Studies</a>
  </div>

  <form method="post" action="compute_results.php" novalidate>

    <!-- Section A: DOI & Auto-Fetched Article Details -->
    <div class="card">
      <h2>Step 1 — DOI & Article Details</h2>
      <p class="muted">Enter the DOI then click <em>Fetch from DOI</em>. Article metadata will be auto-filled and stored in <kbd>ebm.articles</kbd>.</p>
      <div class="grid">
        <div style="grid-column:1/-1;">
          <label for="doi">DOI</label>
          <div style="display:flex; gap:8px;">
            <input type="text" id="doi" name="doi" placeholder="e.g., 10.1001/jama.2024.12345">
            <button class="btn" type="button" onclick="fetchDOI()">Fetch from DOI</button>
          </div>
        </div>

        <!-- Read-only, auto-filled -->
        <div>
          <label for="article_title">Article Title</label>
          <input type="text" id="article_title" name="article_title" readonly>
        </div>
        <div>
          <label for="journal">Journal</label>
          <input type="text" id="journal" name="journal" readonly>
        </div>
        <div>
          <label for="pub_year">Publication Year</label>
          <input type="number" id="pub_year" name="pub_year" readonly>
        </div>
        <div>
          <label for="publisher">Publisher</label>
          <input type="text" id="publisher" name="publisher" readonly>
        </div>
      </div>

      <!-- Hidden ID for linking studies -> articles -->
      <input type="hidden" id="article_id" name="article_id">
    </div>

    <!-- Section B: Clinical Labels (Treatment/Control/Outcome) + Baseline Risk -->
    <div class="card">
      <h2>Step 2 — Treatment / Control / Outcome & Baseline Risk</h2>
      <div class="grid">
        <div>
          <label for="treatment">Treatment of Interest (Exposure)</label>
          <input type="text" id="treatment" name="treatment" placeholder="e.g., Drug A" required>
        </div>
        <div>
          <label for="control">Control / Placebo</label>
          <input type="text" id="control" name="control" placeholder="e.g., Placebo" required>
        </div>
        <div style="grid-column:1/-1;">
          <label for="outcome">Outcome of Interest</label>
          <input type="text" id="outcome" name="outcome" placeholder="e.g., myocardial infarction" required>
        </div>
        <div>
          <label for="baseline_risk">Baseline Risk for Individualizing (0–1)</label>
          <input type="number" step="0.0001" min="0" max="1" id="baseline_risk" name="baseline_risk" placeholder="e.g., 0.12">
        </div>
      </div>
    </div>

    <!-- Section C: 2×3 Table -->
    <div class="card">
      <h2>Step 3 — 2×3 Table (Treatment as Exposure; Outcome as Outcome)</h2>
      <p class="muted">Provide either <strong>(A &amp; C)</strong> or <strong>(B &amp; D)</strong> and both totals <strong>N1</strong> and <strong>N0</strong>.</p>
      <table aria-label="2x3 table">
        <thead>
          <tr>
            <th></th>
            <th>Outcome = Yes</th>
            <th>Outcome = No</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th>Exposed (Treatment)</th>
            <td>
              <label class="muted" for="A">Exposed with outcome (A)</label>
              <input type="number" min="0" id="A" name="A" placeholder="A">
            </td>
            <td>
              <label class="muted" for="B">Exposed with no outcome (B)</label>
              <input type="number" min="0" id="B" name="B" placeholder="B">
            </td>
            <td>
              <label class="muted" for="N1">Total Exposed (N1)</label>
              <input type="number" min="0" id="N1" name="N1" placeholder="N1" required>
            </td>
          </tr>
          <tr>
            <th>Unexposed (Control)</th>
            <td>
              <label class="muted" for="C">Unexposed with outcome (C)</label>
              <input type="number" min="0" id="C" name="C" placeholder="C">
            </td>
            <td>
              <label class="muted" for="D">Unexposed with no outcome (D)</label>
              <input type="number" min="0" id="D" name="D" placeholder="D">
            </td>
            <td>
              <label class="muted" for="N0">Total Unexposed (N0)</label>
              <input type="number" min="0" id="N0" name="N0" placeholder="N0" required>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Section D: Actions -->
    <div class="card">
      <div class="actions">
        <button class="btn" type="submit">Compute</button>
        <button class="btn secondary" type="reset">Clear</button>
        <a class="btn secondary" href="studies_list.php">View Previous Studies</a>
      </div>
    </div>

  </form>
</div>

<script>
async function fetchDOI() {
  const doiEl = document.getElementById('doi');
  const doi = (doiEl.value || '').trim();
  if (!doi) { alert('Enter a DOI first.'); return; }

  const fd = new FormData();
  fd.append('doi', doi);

  try {
    const res = await fetch('doi_autofetch_save.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (!data.ok) {
      alert('DOI lookup failed: ' + (data.error || 'Unknown error'));
      return;
    }

    const a = data.article || {};
    document.getElementById('article_id').value   = a.id || '';
    document.getElementById('article_title').value= a.article_title || '';
    document.getElementById('journal').value      = a.journal_title || '';
    document.getElementById('pub_year').value     = a.pub_year || '';
    document.getElementById('publisher').value    = a.publisher || '';

  } catch (e) {
    alert('Network error while fetching DOI.');
  }
}
</script>
</body>
</html>
