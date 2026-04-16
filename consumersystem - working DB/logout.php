<?php
session_start();

$admin = $_SESSION['admin'] ?? 'unknown';

// Log the logout if DB is available
try {
    include('db.php');
    logActivity($conn, $admin, 'LOGOUT', 'User logged out');
} catch (Exception $e) { /* silent */ }

session_unset();
session_destroy();

if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}

header("Location: login.php?logged_out=1");
exit();
?>
