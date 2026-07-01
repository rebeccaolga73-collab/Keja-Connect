<?php
set_time_limit(600);

$file = __DIR__ . '/kejaconnect new.sql';
$dsn = 'mysql:host=localhost;dbname=kejaconnect';

try {
    $pdo = new PDO($dsn, 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read SQL file
    $sql = file_get_contents($file);
    
    // Remove the database creation and USE statements
    $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/is', '', $sql);
    $sql = preg_replace('/DROP DATABASE IF EXISTS.*?;/is', '', $sql);
    $sql = preg_replace('/^\s*USE\s+.*?;/im', '', $sql);
    
    // Split statements
    $statements = preg_split('/;(?=\s*$|\s*-|$)/m', $sql);
    
    $executed = 0;
    $skipped = 0;
    
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        // Remove comments
        $stmt = preg_replace('/^--.*$/m', '', $stmt);
        $stmt = trim($stmt);
        
        if (empty($stmt)) {
            $skipped++;
            continue;
        }
        
        try {
            $pdo->exec($stmt);
            $executed++;
            echo ".";
            if ($executed % 50 == 0) {
                echo " [$executed]\n";
            }
        } catch (PDOException $e) {
            echo "\n\nError executing:\n" . substr($stmt, 0, 100) . "\n";
            echo "Message: " . $e->getMessage() . "\n\n";
        }
    }
    
    echo "\n\n✓ Import complete!\n";
    echo "Executed: $executed\n";
    echo "Skipped: $skipped\n";
    
    // Verify
    $result = $pdo->query("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = 'kejaconnect'");
    $tables = $result->fetch()['cnt'];
    echo "Tables created: $tables\n";
    
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
?>
