<?php
try {
    // Connect without specifying a database first
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test 1: Drop and recreate database
    echo "Test 1: Recreating kejaconnect database...<br>";
    $pdo->exec("DROP DATABASE IF EXISTS kejaconnect");
    echo "✓ Old database dropped<br>";
    
    $pdo->exec("CREATE DATABASE kejaconnect");
    echo "✓ New database created<br>";
    
    // Test 2: Connect to the new database
    echo "<br>Test 2: Connecting to kejaconnect database...<br>";
    $pdo = new PDO('mysql:host=localhost;dbname=kejaconnect', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to kejaconnect<br>";
    
    // Test 3: Create a simple test table
    echo "<br>Test 3: Creating test table...<br>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS test_table (id INT PRIMARY KEY, name VARCHAR(100))");
    echo "✓ Test table created<br>";
    
    // Test 4: List tables
    echo "<br>Test 4: Tables in database:<br>";
    $result = $pdo->query('SHOW TABLES');
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        echo "  - " . $row[0] . "<br>";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
    echo "Trace: " . $e->getTraceAsString() . "<br>";
}
?>
