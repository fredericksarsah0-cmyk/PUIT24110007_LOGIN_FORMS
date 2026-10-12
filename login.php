<?php
/**
 * Login Authentication System (Factor 1: Password Authentication)
 * 
 * Demonstrates:
 * 1. User Identification via Username OR Email
 * 2. Password verification with Salt and Pepper
 * 3. Generation of random 6-digit OTP with 5-minute expiry
 * 4. Storing OTP in database and redirecting to 2FA verification step
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// If already fully authenticated, redirect to dashboards
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$errorMessage = '';
$logoutNotice = '';

// Check if redirected from logout
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $logoutNotice = "You have been successfully and securely logged out.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? ''); // Can be username or email
    $password   = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $errorMessage = "Please enter your username/email and password.";
    } else {
        try {
            // Find user by either username OR email (Prepared Statement)
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1");
            $stmt->execute([
                ':username' => $identifier,
                ':email'    => $identifier
            ]);
            $user = $stmt->fetch();

            // Verify password using Salt + Pepper
            if ($user && verifyPasswordWithSaltAndPepper($password, $user['salt'], $user['password_hash'])) {
                // Password factor passed! Now initialize Factor 2: OTP
                $otp = generateOTP();
                $expiryTime = date('Y-m-d H:i:s', time() + OTP_EXPIRATION_SECONDS);

                // Store OTP and Expiration in the database
                $updateStmt = $pdo->prepare("UPDATE users SET otp = :otp, otp_expiry = :expiry WHERE id = :id");
                $updateStmt->execute([
                    ':otp'    => $otp,
                    ':expiry' => $expiryTime,
                    ':id'     => $user['id']
                ]);

                // Store temporary 2FA state in session (User is NOT yet fully logged in)
                $_SESSION['2fa_pending'] = true;
                $_SESSION['2fa_user_id'] = $user['id'];
                $_SESSION['2fa_username'] = $user['username'];
                $_SESSION['2fa_role'] = $user['role'];
                
                // For educational/school demo: Store OTP to display it conveniently on the verification page
                $_SESSION['demo_otp_display'] = $otp;

                // Redirect to 2FA Verification page
                header('Location: verify_otp.php');
                exit;
            } else {
                // Security best practice: Generic error message to prevent user enumeration
                $errorMessage = "Invalid credentials. Please verify your username/email and password.";
            }
        } catch (PDOException $e) {
            $errorMessage = "An unexpected error occurred during authentication.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Secure Authentication</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="center-layout">
    <div class="card">
        <div class="card-header">
            <span class="security-tag">Pillar 1: Identification & Pillar 4: Password Verification</span>
            <h2>Sign In</h2>
            <p>Enter your credentials to begin authentication</p>
        </div>

        <?php if (!empty($logoutNotice)): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($logoutNotice) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="form">
            <div class="form-group">
                <label for="identifier">Username or Email Address</label>
                <input type="text" id="identifier" name="identifier" class="form-control" 
                       value="<?= isset($identifier) ? htmlspecialchars($identifier) : '' ?>" 
                       placeholder="Enter username or email" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Enter password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Authenticate & Continue to 2FA &rarr;</button>
        </form>

        <div class="card-footer">
            <p>Don't have an account yet? <a href="register.php">Create one here</a></p>
            <p style="margin-top: 8px;"><a href="setup_db.php" class="small-link">Need Demo Accounts? Run DB Setup</a></p>
        </div>
    </div>
</body>
</html>
