<?php
// ============================================================
//  DATABASE CONFIGURATION — edit these to match your server
// ============================================================
define('DB_HOST',   'localhost');
define('DB_USER',   'root');          // ← your phpMyAdmin user
define('DB_PASS',   '');              // ← your phpMyAdmin password
define('DB_NAME',   'customer_service_db');
define('DB_CHARSET','utf8mb4');

// ============================================================
//  APPLICATION SETTINGS
// ============================================================
define('APP_NAME',    'Customer Service System');
define('APP_VERSION', '1.0.0');
define('RECORDS_PER_PAGE', 15);

// ============================================================
//  CREATE CONNECTION (MySQLi)
// ============================================================
function getDB(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
        }
        $conn->set_charset(DB_CHARSET);
    }
    return $conn;
}

// ============================================================
//  HELPERS
// ============================================================
function e(mixed $val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function generateRefNo(): string {
    $db   = getDB();
    $year = date('Y');
    $res  = $db->query("SELECT COUNT(*) AS cnt FROM cs_records WHERE YEAR(created_at)=$year");
    $cnt  = (int)($res->fetch_assoc()['cnt'] ?? 0);
    return 'CS-' . $year . '-' . str_pad($cnt + 1, 5, '0', STR_PAD_LEFT);
}

function getDropdownOptions(string $category): array {
    $db   = getDB();
    $cat  = $db->real_escape_string($category);
    $res  = $db->query("SELECT value FROM cs_dropdown_options WHERE category='$cat' AND is_active=1 ORDER BY sort_order,value");
    $opts = [];
    while ($row = $res->fetch_assoc()) {
        $opts[] = $row['value'];
    }
    return $opts;
}
