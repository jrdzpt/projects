<?php
date_default_timezone_set('Asia/Manila');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'consumer_db');

try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    die("Database connection failed. Contact system administrator.");
}

function getStatusLabel(int $s): string {
    return match($s) { 1=>'Active', 4=>'Inactive', 5=>'Pull-Out', default=>'Unknown' };
}
function getStatusBadgeClass(int $s): string {
    return match($s) { 1=>'badge-active', 4=>'badge-inactive', 5=>'badge-pullout', default=>'' };
}
function getDashboardStats(PDO $conn): array {
    return [
        'total'    => (int)$conn->query("SELECT COUNT(*) FROM consumers")->fetchColumn(),
        'active'   => (int)$conn->query("SELECT COUNT(*) FROM consumers WHERE status=1")->fetchColumn(),
        'inactive' => (int)$conn->query("SELECT COUNT(*) FROM consumers WHERE status=4")->fetchColumn(),
        'pullout'  => (int)$conn->query("SELECT COUNT(*) FROM consumers WHERE status=5")->fetchColumn(),
        'archived' => (int)$conn->query("SELECT COUNT(*) FROM archive")->fetchColumn(),
        'avg_kwh'  => (float)$conn->query("SELECT COALESCE(AVG(kilowatthour),0) FROM consumers WHERE kilowatthour>0")->fetchColumn(),
        'total_kwh'=> (float)$conn->query("SELECT COALESCE(SUM(kilowatthour),0) FROM consumers")->fetchColumn(),
    ];
}
function sanitize(string $v): string {
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}
function logActivity(PDO $conn, string $admin, string $action, string $detail=''): void {
    try {
        $conn->prepare("INSERT INTO activity_log (admin_user,action,detail,created_at) VALUES (?,?,?,NOW())")
             ->execute([$admin,$action,$detail]);
    } catch(PDOException $e){}
}

// ── CSRF Protection ──────────────────────────────────────────────────────────
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verify_csrf_token(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
}

// ── Session timeout protection ───────────────────────────────────────────────
define('SESSION_TIMEOUT', 1800);
function check_session_timeout(): void {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
            session_destroy();
            header("Location: login.php?session_expired=1");
            exit();
        } else {
            $_SESSION['login_time'] = time();
        }
    }
}
?>