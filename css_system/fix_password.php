<?php
require_once __DIR__ . '/includes/config.php';

$db = getDB();

// Correct hash for 'admin123'
$correct_hash = '$2y$10$lMXGfvGNV9FYZP0c6EOueueU8MBcl1NX1DCAFXuF4lfW.odCThpJO';

$db->query("UPDATE cs_users SET password_hash='$correct_hash' WHERE username='admin'");

if ($db->affected_rows > 0) {
    echo "✓ Password hash updated!\n";
    echo "You can now login with:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";
} else {
    echo "Failed to update password hash\n";
}
?>
