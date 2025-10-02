<?php
require_once __DIR__ . '/../src/db.php';

$pdo = db();

$sql = readline("sqlite> ");
while ($sql !== 'exit') {
    try {
        $stmt = $pdo->query($sql);
        $rows = $stmt ? $stmt->fetchAll() : [];
        print_r($rows);
    } catch (Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    $sql = readline("sqlite> ");
}
