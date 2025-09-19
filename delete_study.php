<?php
// delete_study.php — deletes a single study by id, with CSRF protection,
// and optional cleanup of orphaned articles.

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

// CSRF check
$csrf = $_POST['csrf'] ?? '';
if (!$csrf || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    http_response_code(403);
    echo "Invalid CSRF token";
    exit;
}

$study_id = isset($_POST['study_id']) ? (int)$_POST['study_id'] : 0;
if ($study_id <= 0) {
    http_response_code(400);
    echo "Invalid study_id";
    exit;
}

try {
    $pdo->beginTransaction();

    // Find the linked article first (for optional cleanup)
    $stmt = $pdo->prepare("SELECT article_id FROM studies WHERE id = :id");
    $stmt->execute([':id' => $study_id]);
    $article_id = $stmt->fetchColumn();

    if (!$article_id) {
        $pdo->rollBack();
        http_response_code(404);
        echo "Study not found";
        exit;
    }

    // Delete the study
    $del = $pdo->prepare("DELETE FROM studies WHERE id = :id");
    $del->execute([':id' => $study_id]);

    // OPTIONAL: If no more studies reference that article, delete the article too
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM studies WHERE article_id = :aid");
    $cnt->execute([':aid' => $article_id]);
    if ((int)$cnt->fetchColumn() === 0) {
        $pdo->prepare("DELETE FROM articles WHERE id = :aid")->execute([':aid' => $article_id]);
    }

    $pdo->commit();

    // Redirect back to list with a status flag
    header("Location: studies_list.php?deleted=" . urlencode($study_id));
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo "Delete failed: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}
