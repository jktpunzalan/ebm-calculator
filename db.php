<?php
// db.php — resilient DB bootstrap for "ebm"
// - Tries to connect to ebm
// - If unknown database, connects to server, creates DB, runs schema.sql, reconnects
// - Exposes $pdo (PDO connection to ebm)

$DB_HOST   = '127.0.0.1';
$DB_USER   = 'root';
$DB_PASS   = 'NewStrongPass!123';
$DB_NAME   = 'ebm';
$DB_CHAR   = 'utf8mb4';

// Turn on verbose errors during setup (you can turn off later)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

function pdo_connect($dbname = null) {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_CHAR;
    $dsn = $dbname
        ? "mysql:host={$DB_HOST};dbname={$dbname};charset={$DB_CHAR}"
        : "mysql:host={$DB_HOST};charset={$DB_CHAR}";
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, $DB_USER, $DB_PASS, $opts);
}

function run_schema_sql(PDO $pdoServer, string $dbName, string $schemaPath) {
    if (!is_readable($schemaPath)) {
        throw new RuntimeException("schema.sql not found/readable at: $schemaPath");
    }
    $sql = file_get_contents($schemaPath);
    if ($sql === false || trim($sql) === '') {
        throw new RuntimeException("schema.sql is empty or cannot be read.");
    }

    // Ensure DB exists
    $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci");

    // Connect specifically to ebm to run table DDLs
    $pdoDB = pdo_connect($dbName);

    // Some MySQL versions choke on JSON; optionally downgrade to MEDIUMTEXT:
    // $sql = str_ireplace(' JSON', ' MEDIUMTEXT', $sql);

    // Remove lines starting with "--" comments to avoid false splits
    $clean = preg_replace('/^--.*$/m', '', $sql);

    // Split on semicolons at end of statements
    $stmts = array_filter(array_map('trim', preg_split('/;\s*[\r\n]+/m', $clean)));

    foreach ($stmts as $st) {
        // Skip empty/USE lines since we're already in the correct DB
        if ($st === '' || stripos($st, 'USE ') === 0) continue;
        $pdoDB->exec($st);
    }
    return $pdoDB;
}

try {
    // 1) Try direct connect to ebm
    try {
        $pdo = pdo_connect($DB_NAME);
    } catch (PDOException $e) {
        // 2) If the DB doesn't exist yet, create it and run schema
        if (strpos($e->getMessage(), 'Unknown database') !== false) {
            $serverPdo = pdo_connect(null);
            $pdo = run_schema_sql($serverPdo, $DB_NAME, __DIR__ . '/schema.sql');
        } else {
            throw $e;
        }
    }

    // 3) Final sanity check: ensure core tables exist; if not, run schema
    $need = false;
    $chk1 = $pdo->query("SHOW TABLES LIKE 'articles'")->fetchColumn();
    $chk2 = $pdo->query("SHOW TABLES LIKE 'studies'")->fetchColumn();
    if (!$chk1 || !$chk2) {
        $serverPdo = pdo_connect(null);
        $pdo = run_schema_sql($serverPdo, $DB_NAME, __DIR__ . '/schema.sql');
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo "<pre style='color:#b00020;font-family:monospace'>DB bootstrap failed:\n" .
         htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "\n</pre>";
    exit;
}
