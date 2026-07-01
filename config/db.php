<?php
/**
 * KejaConnect - PDO Database Configuration
 */

// Production/Local DB credentials
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'kejaconnect');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Get established database connection handle
 * @return PDO
 */
function get_db_connection() {
    static $pdo = null;
    
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log locally or show formatted error
        error_log("Database connection failure: " . $e->getMessage());
        
        // Render stylized error page when DB is offline
        die('
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Offline | KejaConnect</title>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
            <style>
                body {
                    font-family: "Inter", sans-serif;
                    background-color: #f7fafc;
                    color: #2d3748;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    margin: 0;
                }
                .card {
                    background: white;
                    border-radius: 12px;
                    padding: 40px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                    max-width: 500px;
                    text-align: center;
                    border-top: 5px solid #1a6b3c;
                }
                h1 { color: #1a6b3c; font-size: 24px; margin-bottom: 10px; }
                p { color: #718096; line-height: 1.6; font-size: 15px; }
                .badge {
                    background: #fffaf0;
                    color: #f0a500;
                    border: 1px solid #feebc8;
                    padding: 6px 12px;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: 600;
                    display: inline-block;
                    margin-bottom: 20px;
                }
                .btn {
                    margin-top: 20px;
                    background-color: #1a6b3c;
                    color: white;
                    text-decoration: none;
                    font-weight: 600;
                    padding: 10px 24px;
                    border-radius: 6px;
                    display: inline-block;
                    transition: opacity 0.2s;
                }
                .btn:hover { opacity: 0.9; }
            </style>
        </head>
        <body>
            <div class="card">
                <span class="badge">Connection Offline</span>
                <h1>KejaConnect Database Offline</h1>
                <p>We are having trouble loading our database backend. Please configure your DB credentials in <code>/config/db.php</code> or ensure that your local MySQL server is currently running.</p>
                <a href="javascript:window.location.reload();" class="btn">Retry Connection</a>
            </div>
        </body>
        </html>
        ');
    }
}
