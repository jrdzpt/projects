<?php
session_set_cookie_params([
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
require_once __DIR__ . '/includes/config.php';

if (!empty($_SESSION['css_user_id'])) {
    $db  = getDB();
    $uid = (int)$_SESSION['css_user_id'];
    $u   = $db->real_escape_string($_SESSION['css_user'] ?? '');
    $ip  = $db->real_escape_string($_SERVER['REMOTE_ADDR'] ?? '');
    $db->query("INSERT INTO cs_audit_log (user_id, username, action, details, ip_address)
                VALUES ($uid, '$u', 'logout', 'User logged out', '$ip')");
}

session_unset();
session_destroy();
header('Location: login.php');
exit;
