<?php
/**
 * User Registration System
 * 
 * Demonstrates:
 * 1. User Identification (Unique Username & Email)
 * 2. Role assignment (Administrator or Normal User)
 * 3. Secure Password Processing:
 *    - Cryptographically random unique per-user Salt
 *    - Server-side isolated Pepper
 *    - Native password_hash() using bcrypt
 * 4. Prepared Statements via PDO to prevent SQL Injection
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// If already logged in, redirect to index
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$errors = [];
$successMessage = '';

// Form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = trim($_POST['role'] ?? 'user');

    // 1. Validate Username
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $errors[] = "Username must be 3-30 characters long and contain only letters, numbers, and underscores.";
    }

    // 2. Validate Email
    if (empty($email)) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }

    // 3. Validate Password
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    // 4. Validate Role
    if (!in_array($role, ['admin', 'user'], true)) {
        $role = 'user';
    }

    // 5. Check if Username or Email is already registered
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE username = :username OR email = :email LIMIT 1");
            $stmt->execute([
                ':username' => $username,
                ':email'    => $email
            ]);
            $existing = $stmt->fetch();

            if ($existing) {
                if (strcasecmp($existing['username'], $username) === 0) {
                    $errors[] = "The username '{$username}' is already taken. Please choose another.";
                }
                if (strcasecmp($existing['email'], $email) === 0) {
                    $errors[] = "The email '{$email}' is already registered. Try logging in.";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Database error while verifying identity.";
        }
    }

    // 6. Secure Password Hashing with Salt + Pepper and Account Insertion
    if (empty($errors)) {
        try {
            // Step A: Generate unique 128-bit random salt
            $salt = generateSalt();

            // Step B: Hash password combining Password + Salt + Server Pepper
            $passwordHash = hashPasswordWithSaltAndPepper($password, $salt);

            // Step C: Insert into database using PDO Prepared Statements
            $insertStmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, salt, role) 
                VALUES (:username, :email, :password_hash, :salt, :role)
            ");
            
            $insertStmt->execute([
                ':username'      => $username,
                ':email'         => $email,
                ':password_hash' => $passwordHash,
                ':salt'          => $salt,
                ':role'          => $role
            ]);

            $successMessage = "Registration successful! You can now log in with your credentials.";
            // Reset fields
            $username = '';
            $email    = '';
        } catch (PDOException $e) {
            $errors[] = "Failed to register account: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Secure Authentication</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="center-layout">
    <div class="card">
        <div class="card-header">
            <span class="security-tag">Pillar 1: User Identification & Pillar 4: Salt + Pepper</span>
            <h2>Create an Account</h2>
            <p>Register as an Administrator or Normal User</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($successMessage) ?>
                <div style="margin-top: 10px;">
                    <a href="login.php" class="btn btn-primary" style="display:inline-block; padding: 6px 14px; font-size: 0.9rem;">Proceed to Login</a>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" 
                       value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" 
                       placeholder="e.g. alex_hunter" required autocomplete="username">
                <small class="helper-text">Unique username (3-30 letters, numbers, underscores).</small>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" 
                       placeholder="e.g. alex@example.com" required autocomplete="email">
                <small class="helper-text">Used for unique identification and recovery.</small>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Minimum 8 characters" required autocomplete="new-password">
                <small class="helper-text">Will be protected using Salt + Pepper + bcrypt.</small>
            </div>

            <div class="form-group">
                <label for="role">Select Role (RBAC Demo)</label>
                <select id="role" name="role" class="form-control">
                    <option value="user" selected>Normal User</option>
                    <option value="admin">Administrator</option>
                </select>
                <small class="helper-text">Select role for testing role-based authorization.</small>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Complete Registration</button>
        </form>

        <div class="card-footer">
            <p>Already have an account? <a href="login.php">Sign In here</a></p>
        </div>
    </div>
</body>
</html>
