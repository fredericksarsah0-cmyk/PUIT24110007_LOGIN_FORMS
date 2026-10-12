<?php
/**
 * Administrator Dashboard
 * 
 * Demonstrates:
 * 1. Role-Based Access Control (RBAC): Only role === 'admin' allowed
 * 2. Protection against direct URL tampering (bypassing)
 * 3. Safe querying and display of registered users (passwords never exposed)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// 1. Enforce Authentication Check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// 2. Enforce Role Authorization Check (Admin only)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Normal users attempting to access admin pages are redirected to access_denied.php
    header('Location: access_denied.php?attempted=admin_dashboard.php');
    exit;
}

// Fetch all registered users for administration view (Never retrieve password hashes or salts in UI views)
try {
    $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY id ASC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
    $dbError = "Unable to fetch user list.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Secure Authentication System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-layout">
    <header class="navbar">
        <div class="nav-brand">
            <span class="logo-icon">&#128737;</span>
            <strong>Admin Security Portal</strong>
        </div>
        <div class="nav-user">
            <span class="user-greeting">Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
            <span class="badge badge-admin">Administrator</span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Log Out</a>
        </div>
    </header>

    <main class="container">
        <!-- Security Overview Cards -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-title">Pillar 1: Identification</div>
                <div class="stat-value">Active Session</div>
                <p class="stat-desc">User ID: <code>#<?= htmlspecialchars($_SESSION['user_id']) ?></code></p>
            </div>
            <div class="stat-card">
                <div class="stat-title">Pillar 2: Two-Factor Auth</div>
                <div class="stat-value text-success">&#10003; Verified</div>
                <p class="stat-desc">OTP validated & invalidated in DB</p>
            </div>
            <div class="stat-card">
                <div class="stat-title">Pillar 3: Role Authorization</div>
                <div class="stat-value text-primary">Administrator</div>
                <p class="stat-desc">Full administrative privileges</p>
            </div>
            <div class="stat-card">
                <div class="stat-title">Pillar 4: Password Security</div>
                <div class="stat-value text-purple">Salt + Pepper</div>
                <p class="stat-desc">Server-isolated Pepper key</p>
            </div>
        </div>

        <!-- Users Management Table -->
        <div class="card card-wide" style="margin-top: 25px;">
            <div class="card-header flex-header">
                <div>
                    <h2>Registered System Users</h2>
                    <p>Demonstrating database storage and role-based categorization</p>
                </div>
                <div>
                    <a href="user_dashboard.php" class="btn btn-secondary btn-sm">Preview User Dashboard</a>
                </div>
            </div>

            <?php if (isset($dbError)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($dbError) ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Email Address</th>
                            <th>Assigned Role</th>
                            <th>Registered On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $row): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($row['id']) ?></td>
                                <td><strong><?= htmlspecialchars($row['username']) ?></strong></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td>
                                    <?php if ($row['role'] === 'admin'): ?>
                                        <span class="badge badge-admin">Administrator</span>
                                    <?php else: ?>
                                        <span class="badge badge-user">Normal User</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
