<?php
/**
 * Automated Database & Tables Setup Script
 * Run this in your browser: http://localhost/.../setup_db.php
 * 
 * It will:
 * 1. Create the `authentication_system` database if it doesn't exist.
 * 2. Create the `users` table with all indexes.
 * 3. Seed two default accounts (Admin and Normal User) with Salt & Pepper hashes.
 */

require_once __DIR__ . '/config.php';

$message = '';
$status = 'info';

try {
    // Connect to MySQL server without specifying database first
    $rootDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
    $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 1. Create database
    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // 2. Connect to the created database
    $pdo = new PDO($rootDsn . ";dbname=" . DB_NAME, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 3. Create users table
    $tableSql = "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `password_hash` VARCHAR(255) NOT NULL,
        `salt` VARCHAR(64) NOT NULL,
        `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
        `otp` VARCHAR(6) DEFAULT NULL,
        `otp_expiry` DATETIME DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_username` (`username`),
        INDEX `idx_email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($tableSql);

    // 4. Seed sample admin if not present
    $checkAdmin = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $checkAdmin->execute(['admin']);
    if (!$checkAdmin->fetch()) {
        $adminSalt = generateSalt();
        $adminHash = hashPasswordWithSaltAndPepper('Admin@1234', $adminSalt);
        $insertAdmin = $pdo->prepare("INSERT INTO users (username, email, password_hash, salt, role) VALUES (?, ?, ?, ?, 'admin')");
        $insertAdmin->execute(['admin', 'admin@system.local', $adminHash, $adminSalt]);
    }

    // 5. Seed sample user if not present
    $checkUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $checkUser->execute(['student']);
    if (!$checkUser->fetch()) {
        $userSalt = generateSalt();
        $userHash = hashPasswordWithSaltAndPepper('Student@1234', $userSalt);
        $insertUser = $pdo->prepare("INSERT INTO users (username, email, password_hash, salt, role) VALUES (?, ?, ?, ?, 'user')");
        $insertUser->execute(['student', 'student@university.edu', $userHash, $userSalt]);
    }

    $status = 'success';
    $message = "Database and tables created successfully! Sample accounts seeded.";

} catch (PDOException $e) {
    $status = 'danger';
    $message = "Setup Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Simple Login System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="center-layout">
    <div class="card card-wide">
        <div class="card-header">
            <h2>Database Initialization</h2>
            <p>Automated table schema and demo account installer</p>
        </div>

        <div class="alert alert-<?= $status ?>">
            <?= htmlspecialchars($message) ?>
        </div>

        <?php if ($status === 'success'): ?>
            <div class="info-box">
                <h4>Pre-seeded Demo Accounts</h4>
                <p>You can use these credentials to test right away:</p>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Username</th>
                            <th>Password</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge badge-admin">Administrator</span></td>
                            <td><code>admin</code></td>
                            <td><code>Admin@1234</code></td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-user">Normal User</span></td>
                            <td><code>student</code></td>
                            <td><code>Student@1234</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="btn-group">
                <a href="login.php" class="btn btn-primary">Go to Login Page</a>
                <a href="register.php" class="btn btn-secondary">Register New User</a>
            </div>
        <?php else: ?>
            <p>Please make sure the MySQL module is running in your XAMPP Control Panel.</p>
            <a href="setup_db.php" class="btn btn-primary">Retry Setup</a>
        <?php endif; ?>
    </div>
</body>
</html>
