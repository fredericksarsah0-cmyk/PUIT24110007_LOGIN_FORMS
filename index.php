<?php
/**
 * Application Entry Point / Router
 * Redirects authenticated users to their corresponding dashboard,
 * or guest users to the login screen.
 */

require_once __DIR__ . '/config.php';

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
        exit;
    } else {
        header('Location: user_dashboard.php');
        exit;
    }
}

// Redirect guest visitors to login
header('Location: login.php');
exit;
