<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=kejaconnect', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $result = $pdo->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'status' => 'success',
        'tables_count' => count($tables),
        'tables' => $tables
    ], JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
