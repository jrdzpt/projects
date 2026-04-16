<?php
require_once __DIR__ . '/includes/config.php';

echo "=== Password Verification ===\n\n";

$db = getDB();
$result = $db->query("SELECT username, password_hash FROM cs_users WHERE username='admin'");
$user = $result->fetch_assoc();

if (!$user) {
    echo "Admin user not found!\n";
    exit;
}

echo "Stored password hash:\n";
echo $user['password_hash'] . "\n\n";

// Test password
$test_password = 'admin123';
$stored_hash = $user['password_hash'];

echo "Testing password: '$test_password'\n";
echo "Result: " . (password_verify($test_password, $stored_hash) ? "✓ VERIFIED" : "✗ FAILED") . "\n\n";

// Generate correct hash
echo "Correct hash for 'admin123':\n";
echo password_hash('admin123', PASSWORD_BCRYPT) . "\n";

// Update with correct hash if needed
$correct_hash = '$2y$10$N7JUzM2hVbDZWKrMlM3z7eWc/dYT4eEwHv9FKhxeH/pq5nfxwqPFW';
if ($stored_hash !== $correct_hash) {
    echo "\nUpdating password hash...\n";
    $db->query("UPDATE cs_users SET password_hash='$correct_hash' WHERE username='admin'");
    echo "Done.\n";
}
?>
