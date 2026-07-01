<?php
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents('kejaconnect new.sql');
    
    // Remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Split queries by semicolon, but be careful with strings
    $queries = preg_split('/;/', $sql);
    
    $executed = 0;
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            echo "Executing: " . substr($query, 0, 60) . "...\n";
            try {
                $pdo->exec($query);
                $executed++;
            } catch (PDOException $e) {
                echo "  Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n✓ Database import completed! " . $executed . " queries executed.\n";
    
    // Verify tables
    $result = $pdo->query('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema="kejaconnect"');
    $row = $result->fetch();
    echo "✓ Tables created: " . $row['count'] . "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
