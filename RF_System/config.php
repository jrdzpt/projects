<?php
/**
 * ============================================================
 * CONFIGURATION FILE - config.php
 * ============================================================
 * Centralized database and application configuration
 * Keep database credentials secure - consider using .env files in production
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'request_form_db');
define('DB_USER', 'root');
define('DB_PASS', '');  // Change to your password if needed

// Application Settings
define('APP_NAME', 'PELCO 3 — IT Request Form System');
define('LOGO_URL', 'https://www.pelco3.org/images/logo.png');
define('DEFAULT_NOTED_BY', 'Engr. Dean Mark Hernandez');

// Security
define('CSRF_TOKEN_LENGTH', 32);
define('SESSION_TIMEOUT', 3600); // 1 hour

/**
 * Get PDO Connection
 * Returns a secure PDO connection instance
 */

function getPDOConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        die('<div style="font-family:sans-serif;color:red;padding:20px;"><strong>Database Error:</strong> Unable to connect to database.<br><a href="setup.php">Run Setup</a></div>');
    }
}

/**
 * Sanitize Input
 * Removes XSS vulnerabilities from user input
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate Required Fields
 */
function validateRequired($fields) {
    foreach ($fields as $field) {
        if (empty($field)) {
            return false;
        }
    }
    return true;
}

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * Generate Request Number
 */
function generateRequestNo($pdo) {
    $year = date('Y');
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM requests WHERE YEAR(created_at) = ?");
    $stmt->execute([$year]);
    $count = $stmt->fetch()['count'] + 1;
    return 'REQ-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

?>
