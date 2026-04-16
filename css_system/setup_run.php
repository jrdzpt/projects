<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

$host = $input['host'] ?? 'localhost';
$user = $input['user'] ?? 'root';
$pass = $input['pass'] ?? 'Pelco123';
$name = $input['name'] ?? 'customer_service_db';

$steps   = [];
$success = true;

// ── Test connection (no DB yet) ─────────────────────
$conn = @new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    $steps[] = ['ok'=>false,'msg'=>'Connection failed: '.$conn->connect_error];
    echo json_encode(['success'=>false,'steps'=>$steps]); exit;
}
$steps[] = ['ok'=>true,'msg'=>"Connected to MySQL at $host"];

// ── Create database ─────────────────────────────────
if ($conn->query("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    $steps[] = ['ok'=>true,'msg'=>"Database `$name` ready"];
} else {
    $steps[] = ['ok'=>false,'msg'=>'Failed to create database: '.$conn->error];
    $success = false;
}

$conn->select_db($name);
$conn->set_charset('utf8mb4');

// ── Run SQL file ────────────────────────────────────
$sqlFile = __DIR__ . '/database.sql';
if (!file_exists($sqlFile)) {
    $steps[] = ['ok'=>false,'msg'=>'database.sql not found'];
    echo json_encode(['success'=>false,'steps'=>$steps]); exit;
}

$sql = file_get_contents($sqlFile);
// Remove the CREATE DATABASE and USE lines (already handled above)
$sql = preg_replace('/^(CREATE DATABASE|USE)[^;]+;/mi', '', $sql);

// Split by ; and execute each statement
$statements = array_filter(array_map('trim', explode(';', $sql)));
$errors = 0;
foreach ($statements as $stmt) {
    if (!$stmt) continue;
    if (!$conn->query($stmt)) {
        if (stripos($conn->error, 'Duplicate') === false) {
            $steps[] = ['ok'=>false,'msg'=>'SQL error: '.$conn->error];
            $errors++;
        }
    }
}
if ($errors === 0) {
    $steps[] = ['ok'=>true,'msg'=>'All tables created & seeded'];
} else {
    $steps[] = ['ok'=>false,'msg'=>"$errors SQL statement(s) failed"];
    $success = false;
}

// ── Update config.php ───────────────────────────────
$config = __DIR__ . '/includes/config.php';
$content = file_get_contents($config);
$content = preg_replace("/define\('DB_HOST',\s*'[^']*'\)/",   "define('DB_HOST',   '$host')", $content);
$content = preg_replace("/define\('DB_USER',\s*'[^']*'\)/",   "define('DB_USER',   '$user')", $content);
$content = preg_replace("/define\('DB_PASS',\s*'[^']*'\)/",   "define('DB_PASS',   '$pass')", $content);
$content = preg_replace("/define\('DB_NAME',\s*'[^']*'\)/",   "define('DB_NAME',   '$name')", $content);
file_put_contents($config, $content);
$steps[] = ['ok'=>true,'msg'=>'config.php updated with your credentials'];

echo json_encode(['success'=>$success,'steps'=>$steps]);
