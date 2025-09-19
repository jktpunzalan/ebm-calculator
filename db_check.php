<?php
require_once __DIR__ . '/db.php';
header('Content-Type: text/plain; charset=utf-8');
echo "Connected.\n";
print_r($pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN));
