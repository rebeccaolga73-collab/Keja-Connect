<?php
// Set up logging
$logfile = __DIR__ . '/setup_log.txt';
$log = fopen($logfile, 'w');

function write_log($msg) {
    global $log;
    fwrite($log, date('[Y-m-d H:i:s] ') . $msg . "\n");
    fflush($log);
}

try {
    write_log("Starting database setup...");
    
    // Connect to MySQL
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    write_log("Connected to MySQL");
    
    // Drop and recreate database
    $pdo->exec("DROP DATABASE IF EXISTS kejaconnect");
    write_log("Dropped old database");
    
    $pdo->exec("CREATE DATABASE kejaconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    write_log("Created new database");
    
    // Connect to new database
    $pdo = new PDO('mysql:host=localhost;dbname=kejaconnect', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    write_log("Connected to kejaconnect database");
    
    // Read SQL file
    $sql_file = __DIR__ . '/kejaconnect new.sql';
    $sql = file_get_contents($sql_file);
    write_log("Read SQL file: " . strlen($sql) . " bytes");
    
    // Parse into statements
    $statements = array();
    $current = '';
    
    foreach (explode("\n", $sql) as $line) {
        $line = rtrim($line);
        
        // Skip comments and empty lines
        if (empty($line) || strpos(ltrim($line), '--') === 0) {
            continue;
        }
        
        $current .= $line . " ";
        
        // Check if statement ends with semicolon
        if (substr(rtrim($line), -1) === ';') {
            $statements[] = $current;
            $current = '';
        }
    }
    
    if (!empty(trim($current))) {
        $statements[] = $current;
    }
    
    write_log("Parsed " . count($statements) . " statements");
    
    // Execute statements
    $count = 0;
    $errors = 0;
    
    foreach ($statements as $i => $stmt) {
        $stmt = trim($stmt);
        if (empty($stmt)) {
            continue;
        }
        
        try {
            $pdo->exec($stmt);
            $count++;
            if ($count % 5 == 0) {
                write_log("Executed $count statements");
            }
        } catch (PDOException $e) {
            $errors++;
            write_log("Error at statement " . ($i+1) . ": " . $e->getMessage());
        }
    }
    
    write_log("Completed! Executed: $count, Errors: $errors");
    
    // Verify
    $result = $pdo->query("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = 'kejaconnect'");
    $table_count = $result->fetch()['cnt'];
    write_log("Final table count: $table_count");
    
} catch (Exception $e) {
    write_log("FATAL ERROR: " . $e->getMessage());
    write_log($e->getTraceAsString());
}

fclose($log);

// Return result
if (file_exists($logfile)) {
    header('Content-Type: text/plain');
    readfile($logfile);
} else {
    echo "Error: Could not create log file";
}
?>
