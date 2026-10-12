<?php
/**
 * Access Denied Page (HTTP 403 Forbidden)
 * 
 * Demonstrates:
 * 1. Handling unauthorized role access attempts
 * 2. Proper HTTP 403 status code
 * 3. Clear feedback without crashing the application
 */

require_once __DIR__ . '/config.php';

// Set HTTP 403 Forbidden header
http_response_code(403);

$attemptedPage = htmlspecialchars($_GET['attempted'] ?? 'Restricted Resource');
$currentRole   = $_SESSION['role'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="center-layout">
    <div class="card card-wide" style="border-top: 5px solid #dc3545;">
        <div class="card-header text-center">
            <div style="font-size: 3.5rem; margin-bottom: 10px;">&#128683;</div>
            <h2 style="color: #dc3545;">403 - Access Denied</h2>
            <p>Role-Based Access Control (RBAC) Protection Triggered</p>
        </div>

        <div class="alert alert-danger">
            <strong>Security Notice:</strong> You do not possess the required authorization to view 
            <code><?= $attemptedPage ?></code>.
        </div>

        <div class="info-box">
            <h4>Authorization Details</h4>
            <p><strong>Your Current Role:</strong> 
                <span class="badge badge-<?= $currentRole === 'admin' ? 'admin' : 'user' ?>">
                    <?= htmlspecialchars(ucfirst($currentRole)) ?>
                </span>
            </p>
            <p><strong>Required Privilege Level:</strong> <span class="badge badge-admin">Administrator</span></p>
            <p style="margin-top: 10px; font-size: 0.9rem; color: #6c757d;">
                This access attempt was intercepted by server-side authorization guards to prevent privilege escalation.
            </p>
        </div>

        <div class="btn-group" style="margin-top: 20px;">
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin_dashboard.php" class="btn btn-primary">Return to Admin Dashboard</a>
                <?php else: ?>
                    <a href="user_dashboard.php" class="btn btn-primary">Return to User Dashboard</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">Go to Login</a>
            <?php endif; ?>
            
            <a href="logout.php" class="btn btn-outline-danger">Log Out</a>
        </div>
    </div>
</body>
</html>
