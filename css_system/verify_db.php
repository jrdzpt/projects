<?php
require_once __DIR__ . '/includes/config.php';

echo "=== Database Verification ===\n\n";

// Check if cs_users table exists
$db = getDB();
$result = $db->query("SELECT id, username, full_name, is_active FROM cs_users");

if (!$result) {
    echo "ERROR: cs_users table does not exist!\n";
    echo "Error: " . $db->error . "\n";
    exit;
}

echo "Users in database:\n";
while ($row = $result->fetch_assoc()) {
    echo "- ID: {$row['id']}, Username: {$row['username']}, Name: {$row['full_name']}, Active: {$row['is_active']}\n";
}

if ($result->num_rows === 0) {
    echo "NO USERS FOUND!\n\n";
    echo "Inserting admin user...\n";
    // password_hash('admin123', PASSWORD_BCRYPT)
    $hashed = '$2y$10$N7JUzM2hVbDZWKrMlM3z7eWc/dYT4eEwHv9FKhxeH/pq5nfxwqPFW';
    $db->query("INSERT INTO cs_users (username, password_hash, full_name, role, is_active) 
               VALUES ('admin', '$hashed', 'System Administrator', 'admin', 1)");
    
    if ($db->affected_rows > 0) {
        echo "✓ Admin user inserted successfully!\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
    } else {
        echo "ERROR: Failed to insert admin user\n";
    }
}

echo "\nDone.\n";
?>
