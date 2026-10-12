<?php
/**
 * Database Connection via PHP Data Objects (PDO)
 * 
 * Demonstrates:
 * 1. Safe PDO instantiation
 * 2. Prepared statements with emulation disabled (prevents SQL Injection)
 * 3. Graceful error handling without leaking database credentials
 */

require_once __DIR__ . '/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on SQL errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Return rows as associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements (SQLi protection)
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // In production/academic defense, never output raw DB errors containing credentials
    die("<div style='font-family:sans-serif; padding:20px; background:#fff3cd; border:1px solid #ffeeba; border-radius:5px; margin:20px;'>
            <h3 style='color:#856404;'>Database Connection Error</h3>
            <p>Could not connect to the database <strong>" . htmlspecialchars(DB_NAME) . "</strong>.</p>
            <p>Make sure MySQL is running in XAMPP, and you have imported <code>database.sql</code> or run <code><a href='setup_db.php'>setup_db.php</a></code>.</p>
         </div>");
}
