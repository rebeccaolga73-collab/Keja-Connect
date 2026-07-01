<?php
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $result = $pdo->query('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema="kejaconnect"');
    $row = $result->fetch();
    echo json_encode(['tables_count' => $row['count']]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
