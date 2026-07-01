<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=kejaconnect', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo json_encode(['status' => 'connected', 'message' => 'Successfully connected to kejaconnect database']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
