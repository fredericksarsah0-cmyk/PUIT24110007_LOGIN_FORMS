<?php
/**
 * Logout & Session Destruction Handler
 * 
 * Demonstrates:
 * 1. Safe clearance of in-memory $_SESSION array
 * 2. Proper invalidation of client session cookies
 * 3. Complete destruction of server-side session file/storage
 * 4. Safe redirection to login screen
 */

require_once __DIR__ . '/config.php';

// 1. Clear all session variables in memory
$_SESSION = [];

// 2. Invalidate the session cookie on client browser if cookies are used
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. Destroy the server-side session storage
session_destroy();

// 4. Redirect to login with logout success notification
header('Location: login.php?logout=success');
exit;
