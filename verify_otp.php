<?php
/**
 * Two-Factor Authentication (Factor 2: OTP Verification)
 * 
 * Demonstrates:
 * 1. Second factor authentication requirement
 * 2. OTP expiration check (5-minute window)
 * 3. Timing-attack resistant verification (hash_equals)
 * 4. Immediate invalidation of OTP after single use
 * 5. Session ID regeneration to prevent Session Fixation attacks
 * 6. Role-based routing upon successful verification
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Enforce that Step 1 (Password Authentication) has been completed
if (!isset($_SESSION['2fa_pending']) || $_SESSION['2fa_pending'] !== true || !isset($_SESSION['2fa_user_id'])) {
    header('Location: login.php');
    exit;
}

$userId   = $_SESSION['2fa_user_id'];
$username = $_SESSION['2fa_username'] ?? 'User';
$error    = '';
$success  = '';

// Handle "Resend / Request New OTP" Action
if (isset($_POST['action']) && $_POST['action'] === 'resend_otp') {
    try {
        $newOtp = generateOTP();
        $newExpiry = date('Y-m-d H:i:s', time() + OTP_EXPIRATION_SECONDS);

        $updateStmt = $pdo->prepare("UPDATE users SET otp = :otp, otp_expiry = :expiry WHERE id = :id");
        $updateStmt->execute([
            ':otp'    => $newOtp,
            ':expiry' => $newExpiry,
            ':id'     => $userId
        ]);

        $_SESSION['demo_otp_display'] = $newOtp;
        $success = "A new 6-digit OTP has been generated with a fresh 5-minute validity!";
    } catch (PDOException $e) {
        $error = "Failed to regenerate OTP. Please try again.";
    }
}

// Handle OTP Submission & Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $enteredOtp = trim($_POST['otp']);

    if (empty($enteredOtp)) {
        $error = "Please enter the 6-digit verification code.";
    } else {
        try {
            // Retrieve latest user record with stored OTP and expiration
            $stmt = $pdo->prepare("SELECT id, username, email, role, otp, otp_expiry FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();

            if (!$user || empty($user['otp'])) {
                $error = "No active OTP found. Please request a new code.";
            } else {
                $currentTime = time();
                $expiryTime  = strtotime($user['otp_expiry']);

                // 1. Check if OTP is expired
                if ($currentTime > $expiryTime) {
                    $error = "The OTP code has expired (5-minute limit exceeded). Please request a new one.";
                }
                // 2. Timing-safe comparison of entered OTP vs stored OTP
                elseif (!hash_equals($user['otp'], $enteredOtp)) {
                    $error = "Invalid OTP code. Please check and try again.";
                } 
                // 3. OTP is valid and within time limit!
                else {
                    // Security Step A: Invalidate OTP immediately in database (Single-Use enforcement)
                    $invalidateStmt = $pdo->prepare("UPDATE users SET otp = NULL, otp_expiry = NULL WHERE id = :id");
                    $invalidateStmt->execute([':id' => $user['id']]);

                    // Security Step B: Prevent Session Fixation by regenerating Session ID
                    session_regenerate_id(true);

                    // Security Step C: Establish full authenticated session
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['username']  = $user['username'];
                    $_SESSION['email']     = $user['email'];
                    $_SESSION['role']      = $user['role'];

                    // Clear temporary 2FA intermediate session variables
                    unset($_SESSION['2fa_pending']);
                    unset($_SESSION['2fa_user_id']);
                    unset($_SESSION['2fa_username']);
                    unset($_SESSION['2fa_role']);
                    unset($_SESSION['demo_otp_display']);

                    // Security Step D: Role-Based Authorization Redirection
                    if ($user['role'] === 'admin') {
                        header('Location: admin_dashboard.php');
                    } else {
                        header('Location: user_dashboard.php');
                    }
                    exit;
                }
            }
        } catch (PDOException $e) {
            $error = "An error occurred while validating your OTP.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Verification - Secure Authentication</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="center-layout">
    <div class="card">
        <div class="card-header">
            <span class="security-tag">Pillar 2: Two-Factor Authentication (OTP)</span>
            <h2>Two-Factor Verification</h2>
            <p>Hello, <strong><?= htmlspecialchars($username) ?></strong>! Please verify your second factor.</p>
        </div>

        <!-- Demonstration Banner for Academic Evaluation -->
        <?php if (!empty($_SESSION['demo_otp_display'])): ?>
            <div class="demo-otp-banner">
                <span class="demo-badge">University Project Demonstration</span>
                <p>In a production system, this OTP is sent via SMS or encrypted email.<br>
                   Your generated 6-digit OTP is:</p>
                <div class="otp-code"><?= htmlspecialchars($_SESSION['demo_otp_display']) ?></div>
                <small>Valid for 5 minutes (300 seconds)</small>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="verify_otp.php" class="form">
            <div class="form-group">
                <label for="otp">Enter 6-Digit OTP</label>
                <input type="text" id="otp" name="otp" class="form-control otp-input" 
                       maxlength="6" pattern="[0-9]{6}" inputmode="numeric" 
                       placeholder="••••••" autofocus required autocomplete="one-time-code">
                <small class="helper-text">Enter the 6-digit code shown above.</small>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Verify OTP & Login</button>
        </form>

        <div class="card-footer">
            <form method="POST" action="verify_otp.php" style="display:inline;">
                <input type="hidden" name="action" value="resend_otp">
                <button type="submit" class="btn-link">Did not receive or expired? Request New OTP</button>
            </form>
            <p style="margin-top: 10px;">
                <a href="logout.php" class="small-link">&larr; Cancel and return to Login</a>
            </p>
        </div>
    </div>
</body>
</html>
