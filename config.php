<?php
/**
 * Configuration & Security Helper Functions
 * 
 * Demonstrates:
 * 1. Database connection settings
 * 2. Server-side Pepper storage (never saved in the database)
 * 3. Secure session cookie policies
 * 4. Password hashing with Salt + Pepper using PHP's native password_hash()
 */

// Set Timezone
date_default_timezone_set('UTC');

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure session cookie parameters for enhanced security
    ini_set('session.cookie_httponly', 1); // Mitigate XSS attacks by disallowing JS access to session cookies
    ini_set('session.use_only_cookies', 1); // Prevent session fixation via URLs
    ini_set('session.cookie_samesite', 'Lax'); // Protect against CSRF
    session_start();
}

// Database Credentials (Default for XAMPP)
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');
define('DB_NAME', 'authentication_system');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * SERVER-SIDE PEPPER:
 * A secret cryptographic key kept strictly in code/environment, NOT in the database.
 * If the database is compromised via SQL injection or dump, the attacker CANNOT crack passwords
 * without also stealing this server-side pepper key.
 */
define('PEPPER', 'K9#F2!xP9$qL4@vB8*eT1^zW5&uM3#yR_UnivSecDemoKey_2024');

// OTP Life Span (in seconds, e.g., 300 seconds = 5 minutes)
define('OTP_EXPIRATION_SECONDS', 300);

/**
 * Generate a cryptographically secure random Salt for each user
 * 
 * @return string 32-character hexadecimal salt
 */
function generateSalt(): string {
    return bin2hex(random_bytes(16)); // 16 bytes = 128-bit entropy
}

/**
 * Generate a 6-digit numeric OTP code
 * 
 * @return string 6-digit OTP padded with leading zeroes if necessary
 */
function generateOTP(): string {
    return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Hash password with User Salt and Server Pepper
 * 
 * Processing Workflow:
 * 1. Plain password is concatenated with user-specific random salt.
 * 2. Result is keyed through HMAC-SHA256 with the server-side PEPPER.
 *    (This also guarantees a fixed 64-character hex length, preventing bcrypt truncation issues).
 * 3. The output is hashed using PHP's native password_hash() with PASSWORD_DEFAULT (bcrypt).
 * 
 * @param string $plainPassword
 * @param string $salt
 * @return string Hashed password
 */
function hashPasswordWithSaltAndPepper(string $plainPassword, string $salt): string {
    // Step 1 & 2: Combine Password + Salt with Server Pepper via HMAC-SHA256
    $peppered = hash_hmac('sha256', $plainPassword . $salt, PEPPER);
    
    // Step 3: Hash with PHP's standard secure algorithm
    return password_hash($peppered, PASSWORD_DEFAULT);
}

/**
 * Verify a plain password against the stored hash and salt
 * 
 * @param string $plainPassword
 * @param string $salt
 * @param string $storedHash
 * @return bool True if matched, false otherwise
 */
function verifyPasswordWithSaltAndPepper(string $plainPassword, string $salt, string $storedHash): bool {
    // Reproduce the peppered digest using user's salt and server's pepper
    $peppered = hash_hmac('sha256', $plainPassword . $salt, PEPPER);
    
    // Verify against stored bcrypt/argon hash in constant time
    return password_verify($peppered, $storedHash);
}

/**
 * Sanitize output to prevent Cross-Site Scripting (XSS)
 * 
 * @param mixed $data
 * @return string
 */
function sanitize($data): string {
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}
