<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);

// Read SQL file
$sql_file = __DIR__ . '/kejaconnect.sql';
if (!file_exists($sql_file)) {
    die("SQL file not found: $sql_file");
}

$sql_content = file_get_contents($sql_file);

// Connect to database
$mysqli = new mysqli("localhost", "root", "", "");

if ($mysqli->connect_errno) {
    die("Connection failed: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");

// Split SQL statements by semicolon
$statements = array_filter(array_map('trim', explode(';', $sql_content)));

echo "<h1>Database Setup</h1>";
echo "<h2>Executing SQL statements...</h2>";
echo "<ul>";

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    if (empty($statement)) {
        continue;
    }
    
    // Add semicolon back if needed
    if (!str_ends_with($statement, ';')) {
        $statement .= ';';
    }
    
    // Extract readable name for display
    if (stripos($statement, 'CREATE TABLE') !== false) {
        preg_match('/CREATE TABLE.*?`(\w+)`/i', $statement, $matches);
        $name = $matches[1] ?? 'unknown';
        $action = "Creating table: $name";
    } elseif (stripos($statement, 'INSERT INTO') !== false) {
        preg_match('/INSERT INTO\s+`?(\w+)`?/i', $statement, $matches);
        $name = $matches[1] ?? 'unknown';
        $action = "Inserting into: $name";
    } elseif (stripos($statement, 'CREATE DATABASE') !== false) {
        $action = "Creating database";
    } elseif (stripos($statement, 'USE') !== false) {
        $action = "Switching database";
    } elseif (stripos($statement, 'DROP TABLE') !== false) {
        preg_match('/DROP TABLE.*?`?(\w+)`?/i', $statement, $matches);
        $name = $matches[1] ?? 'unknown';
        $action = "Dropping table: $name";
    } else {
        $action = substr($statement, 0, 50) . "...";
    }
    
    // Execute statement
    if ($mysqli->query($statement)) {
        echo "<li style='color: green;'>✓ $action</li>";
        $success_count++;
    } else {
        echo "<li style='color: red;'>✗ $action - Error: " . htmlspecialchars($mysqli->error) . "</li>";
        $error_count++;
    }
}

echo "</ul>";

// Verify results
echo "<h2>Verification</h2>";

// Check tables
$result = $mysqli->query("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = 'kejaconnect'");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p><strong>Tables created:</strong> " . $row['cnt'] . "/10</p>";
}

// Check users
$result = $mysqli->query("SELECT COUNT(*) as cnt FROM kejaconnect.users");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p><strong>Users inserted:</strong> " . $row['cnt'] . "/3</p>";
} else {
    echo "<p style='color: red;'><strong>Error checking users:</strong> " . htmlspecialchars($mysqli->error) . "</p>";
}

echo "<h2>Summary</h2>";
echo "<p style='color: green;'><strong>Successful:</strong> $success_count statements</p>";
if ($error_count > 0) {
    echo "<p style='color: red;'><strong>Errors:</strong> $error_count statements</p>";
}

$mysqli->close();
?>
