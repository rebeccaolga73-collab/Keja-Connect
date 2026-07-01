<?php
set_time_limit(300); // 5 minutes timeout
ob_start();

try {
    echo "<pre>";
    
    // Connect to MySQL
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to MySQL\n\n";
    
    // Drop and recreate database
    echo "Dropping existing database...\n";
    $pdo->exec("DROP DATABASE IF EXISTS kejaconnect");
    
    echo "✓ Creating new database...\n";
    $pdo->exec("CREATE DATABASE kejaconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    echo "✓ Database created\n\n";
    
    // Now connect to the new database
    $pdo = new PDO('mysql:host=localhost;dbname=kejaconnect', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to kejaconnect database\n\n";
    
    // Read and parse SQL file
    $file = __DIR__ . '/kejaconnect new.sql';
    echo "Reading SQL file: $file\n";
    
    if (!file_exists($file)) {
        echo "ERROR: File not found!\n";
        exit(1);
    }
    
    $sql = file_get_contents($file);
    echo "File size: " . strlen($sql) . " bytes\n\n";
    
    // Remove comments and empty lines
    $lines = explode("\n", $sql);
    $filtered = array();
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || substr($line, 0, 2) === '--') {
            continue;
        }
        $filtered[] = $line;
    }
    
    $sql = implode("\n", $filtered);
    
    // Split by semicolon
    $statements = array_filter(explode(";", $sql), function($s) {
        return !empty(trim($s));
    });
    
    echo "Total statements to execute: " . count($statements) . "\n";
    echo "Executing statements...\n\n";
    
    $count = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $count++;
            echo ".";
            if ($count % 50 == 0) {
                echo " $count\n";
            }
        } catch (PDOException $e) {
            $errors++;
            echo "\nERROR at statement $count:\n";
            echo "Statement: " . substr($statement, 0, 100) . "...\n";
            echo "Error: " . $e->getMessage() . "\n\n";
        }
    }
    
    echo "\n\n✓ Execution completed!\n";
    echo "  Statements executed: $count\n";
    echo "  Errors: $errors\n\n";
    
    // Verify
    $result = $pdo->query("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = 'kejaconnect'");
    $tables_count = $result->fetch()['cnt'];
    echo "✓ Tables created: $tables_count\n";
    
    // List tables
    $result = $pdo->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($tables)) {
        echo "\nTables:\n";
        foreach ($tables as $table) {
            echo "  - $table\n";
        }
    }
    
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<pre>";
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString();
    echo "</pre>";
    exit(1);
}
?>
