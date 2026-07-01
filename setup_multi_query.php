<?php
set_time_limit(300);

$conn = new mysqli("localhost", "root", "", "kejaconnect");

if ($conn->connect_error) {
    die(json_encode(['error' => $conn->connect_error]));
}

// Read SQL file
$sql_file = __DIR__ . '/kejaconnect new.sql';
$sql = file_get_contents($sql_file);

// Remove database creation statements
$sql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/is', '', $sql);
$sql = preg_replace('/DROP DATABASE IF EXISTS.*?;/is', '', $sql);
$sql = preg_replace('/^\s*USE\s+.*?;/im', '', $sql);

echo "<pre>";
echo "SQL file size: " . strlen($sql) . " bytes\n";

if ($conn->multi_query($sql)) {
    $count = 0;
    do {
        if ($conn->store_result()) {
            $conn->free_result();
        }
        $count++;
    } while ($conn->next_result());
    
    echo "✓ Executed successfully!\n";
    echo "Queries processed: " . $count . "\n";
} else {
    echo "✗ Error: " . $conn->error . "\n";
}

// Verify
$result = $conn->query("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = 'kejaconnect'");
$row = $result->fetch_assoc();
echo "Tables created: " . $row['cnt'] . "\n";

$result = $conn->query("SHOW TABLES");
if ($result->num_rows > 0) {
    echo "\nTables:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  - " . $row['Tables_in_kejaconnect'] . "\n";
    }
}

echo "</pre>";
$conn->close();
?>
