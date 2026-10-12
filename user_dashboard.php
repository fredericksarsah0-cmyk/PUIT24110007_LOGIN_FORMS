<?php
/**
 * Normal User Dashboard
 * 
 * Demonstrates:
 * 1. Protected Member Access: Requires active authenticated session
 * 2. Role-Based Access Control (RBAC): Restricted to normal user scope
 * 3. Interactive RBAC verification trigger to test protection against unauthorized access
 */

require_once __DIR__ . '/config.php';

// Enforce Authentication Check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'] ?? 'User';
$email    = $_SESSION['email'] ?? 'Not specified';
$userId   = $_SESSION['user_id'] ?? 0;
$role     = $_SESSION['role'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Secure Authentication System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-layout">
    <header class="navbar">
        <div class="nav-brand">
            <span class="logo-icon">&#128100;</span>
            <strong>User Portal</strong>
        </div>
        <div class="nav-user">
            <span class="user-greeting">Welcome, <strong><?= htmlspecialchars($username) ?></strong></span>
            <span class="badge badge-user">Normal User</span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Log Out</a>
        </div>
    </header>

    <main class="container">
        <div class="card card-wide">
            <div class="card-header">
                <h2>User Profile & Security Status</h2>
                <p>You have successfully completed <strong>Two-Factor Authentication</strong>.</p>
            </div>

            <div class="profile-summary">
                <div class="profile-row">
                    <span class="profile-label">User Account ID:</span>
                    <span class="profile-val"><code>#<?= htmlspecialchars($userId) ?></code></span>
                </div>
                <div class="profile-row">
                    <span class="profile-label">Username:</span>
                    <span class="profile-val"><strong><?= htmlspecialchars($username) ?></strong></span>
                </div>
                <div class="profile-row">
                    <span class="profile-label">Email Address:</span>
                    <span class="profile-val"><?= htmlspecialchars($email) ?></span>
                </div>
                <div class="profile-row">
                    <span class="profile-label">Assigned Role:</span>
                    <span class="profile-val"><span class="badge badge-user">Normal User</span></span>
                </div>
                <div class="profile-row">
                    <span class="profile-label">2FA State:</span>
                    <span class="profile-val text-success">&#10003; Verified & One-Time Password invalidated</span>
                </div>
            </div>

            <!-- RBAC Demonstration Box -->
            <div class="rbac-demo-box">
                <h3>&#128274; Role-Based Access Control (RBAC) Demonstration</h3>
                <p>
                    As a <strong>Normal User</strong>, you are strictly prohibited from accessing administrative tools.
                    If you attempt to tamper with the browser URL and visit <code>admin_dashboard.php</code> directly,
                    the server will detect your unauthorized role and block your request.
                </p>
                <div style="margin-top: 15px;">
                    <a href="admin_dashboard.php" class="btn btn-warning">
                        Test RBAC Defense (Try Accessing Admin Dashboard) &rarr;
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
