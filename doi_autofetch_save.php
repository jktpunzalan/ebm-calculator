<?php
// doi_autofetch_save.php
// Usage:
// POST doi=10.1001/jama.2024.12345
// Returns JSON with saved article row (including id) or error.

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$doi = trim($_POST['doi'] ?? $_GET['doi'] ?? '');
if ($doi === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing DOI']);
    exit;
}

// Normalize DOI (optional: trim URL prefix if pasted from publisher)
$doi = preg_replace('~^https?://doi\.org/~i', '', $doi);

// Fetch from Crossref
$apiUrl = 'https://api.crossref.org/works/' . rawurlencode($doi);

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    // Polite User-Agent per Crossref etiquette (replace with your identity)
    CURLOPT_HTTPHEADER => [
        'User-Agent: EBM-Calculator/1.0 (mailto:your-email@example.com)'
    ],
]);
$resp = curl_exec($ch);
$err  = curl_error($ch);
$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err || $code < 200 || $code >= 300) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'Crossref request failed', 'detail' => $err ?: "HTTP $code"]);
    exit;
}

$j = json_decode($resp, true);
if (!isset($j['message'])) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Unexpected Crossref response']);
    exit;
}
$m = $j['message'];

// Helpers to safely pick fields
$title = '';
if (!empty($m['title'][0])) $title = $m['title'][0];

$journal = '';
if (!empty($m['container-title'][0])) $journal = $m['container-title'][0];

$authors = [];
if (!empty($m['author']) && is_array($m['author'])) {
    foreach ($m['author'] as $a) {
        $authors[] = [
            'given'  => $a['given']  ?? '',
            'family' => $a['family'] ?? '',
            'affiliation' => $a['affiliation'] ?? []
        ];
    }
}
$authors_json = json_encode($authors, JSON_UNESCAPED_UNICODE);

list($y,$mo,$d) = [null,null,null];
$dateParts = $m['published-print']['date-parts'][0]
    ?? $m['published-online']['date-parts'][0]
    ?? $m['issued']['date-parts'][0]
    ?? null;
if (is_array($dateParts)) {
    $y  = $dateParts[0] ?? null;
    $mo = $dateParts[1] ?? null;
    $d  = $dateParts[2] ?? null;
}

$volume   = $m['volume']   ?? null;
$issue_no = $m['issue']    ?? null;
$pages    = $m['page']     ?? null;
$publisher= $m['publisher']?? null;
$url      = $m['URL']      ?? null;

// Crossref abstracts may be JATS XML (<jats:p> ...). You can strip tags:
$abstract_text = null;
if (!empty($m['abstract'])) {
    $abstract_text = trim(strip_tags($m['abstract']));
}

// Upsert into DB (NO JSON CAST for compatibility)
$sql = "
INSERT INTO articles
  (doi, article_title, journal_title, authors_json, pub_year, pub_month, pub_day, volume, issue_no, pages, publisher, url, abstract_text)
VALUES
  (:doi, :title, :journal, :authors, :y, :mo, :d, :vol, :iss, :pg, :pub, :url, :abs)
ON DUPLICATE KEY UPDATE
  article_title = VALUES(article_title),
  journal_title = VALUES(journal_title),
  authors_json  = VALUES(authors_json),
  pub_year      = VALUES(pub_year),
  pub_month     = VALUES(pub_month),
  pub_day       = VALUES(pub_day),
  volume        = VALUES(volume),
  issue_no      = VALUES(issue_no),
  pages         = VALUES(pages),
  publisher     = VALUES(publisher),
  url           = VALUES(url),
  abstract_text = VALUES(abstract_text),
  updated_at    = CURRENT_TIMESTAMP
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':doi'     => $doi,
        ':title'   => $title,
        ':journal' => $journal,
        ':authors' => $authors_json, // plain string bind; JSON column (if available) will validate it
        ':y'       => $y,
        ':mo'      => $mo,
        ':d'       => $d,
        ':vol'     => $volume,
        ':iss'     => $issue_no,
        ':pg'      => $pages,
        ':pub'     => $publisher,
        ':url'     => $url,
        ':abs'     => $abstract_text,
    ]);

    // Fetch saved row (including id)
    $stmt2 = $pdo->prepare("SELECT * FROM articles WHERE doi = :doi LIMIT 1");
    $stmt2->execute([':doi' => $doi]);
    $row = $stmt2->fetch();

    echo json_encode(['ok' => true, 'article' => $row], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'DB upsert failed',
        'detail'=> $e->getMessage(),           // <— show the real reason
    ], JSON_UNESCAPED_UNICODE);
}
