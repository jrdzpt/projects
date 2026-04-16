<?php
// ============================================================
// SETUP.PHP — Run once to create the database and tables
// ============================================================
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create DB
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE " . DB_NAME);

    // Create requests table
    $pdo->exec("CREATE TABLE IF NOT EXISTS requests (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        request_no   VARCHAR(30)  NOT NULL UNIQUE,
        date         DATE         NOT NULL,
        department   VARCHAR(100) NOT NULL,
        issue_database  TEXT,
        issue_hardware  TEXT,
        issue_software  TEXT,
        issue_cctv      TEXT,
        issue_others    TEXT,
        purpose         TEXT NOT NULL,
        requested_by    VARCHAR(100) NOT NULL,
        request_date    DATE,
        status          ENUM('Pending','In Progress','Done') DEFAULT 'Pending',
        finding         TEXT,
        remarks         TEXT,
        accomplished_by VARCHAR(100),
        conforme        VARCHAR(100),
        noted_by        VARCHAR(100),
        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Sample data
    $pdo->exec("INSERT IGNORE INTO requests 
        (request_no, date, department, issue_hardware, purpose, requested_by, request_date, status)
        VALUES 
        ('REQ-2025-0001', CURDATE(), 'Finance', 'Printer not working',
         'Need printer repaired for monthly report printing.', 'Juan dela Cruz', CURDATE(), 'Pending')");

    $status = 'success';
    $msg = 'Database and tables created successfully! <a href="index.php" style="color:#1a5c2a;font-weight:700;">Go to Request Form →</a>';

} catch (PDOException $e) {
    $status = 'error';
    $msg = 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DB Setup</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Segoe UI', sans-serif; background: #f4f9f4;
    display: flex; align-items: center; justify-content: center;
    min-height: 100vh;
  }
  .box {
    background: white; padding: 40px 50px; border-radius: 16px;
    box-shadow: 0 8px 40px rgba(40,167,69,.15); max-width: 500px;
    width: 100%; text-align: center;
  }
  .icon-wrap {
    width: 70px; height: 70px; background: #d4edda; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px;
  }
  h2 { color: #1a5c2a; margin-bottom: 8px; font-size: 22px; }
  p.sub { color: #888; font-size: 13px; margin-bottom: 0; }
  code { background: #f0faf2; color: #1a5c2a; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
  .alert {
    padding: 16px 20px; border-radius: 10px; font-size: 15px; margin-top: 24px;
    display: flex; align-items: center; gap: 12px; text-align: left;
  }
  .success { background: #d4edda; border: 1px solid #28a745; color: #1a5c2a; }
  .error   { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
  .footer-note { margin-top: 20px; font-size: 12px; color: #aaa; }
</style>
</head>
<body>
<div class="box">
  <div class="icon-wrap">
    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2">
      <ellipse cx="12" cy="5" rx="9" ry="3"/>
      <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
      <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
    </svg>
  </div>
  <h2>Database Setup</h2>
  <p class="sub">Sets up <code>request_form_db</code> on MySQL</p>

  <div class="alert <?= $status ?>">
    <?php if ($status === 'success'): ?>
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1a5c2a" stroke-width="2.5" style="flex-shrink:0;">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
    <?php else: ?>
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#991b1b" stroke-width="2.5" style="flex-shrink:0;">
        <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
      </svg>
    <?php endif; ?>
    <span><?= $msg ?></span>
  </div>

  <p class="footer-note">Edit DB credentials in setup.php and index.php if needed.</p>
</div>
</body>
</html>