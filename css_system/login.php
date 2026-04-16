<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PELCO III – Login</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#f1f5f2;min-height:100vh;display:flex;flex-direction:column}
.topbar{background:#037d3c;border-bottom:3px solid #fce704;height:60px;display:flex;align-items:center;padding:0 28px;gap:14px;box-shadow:0 2px 8px rgba(3,125,60,.2)}
.topbar img{width:34px;height:34px;background:#fff;border-radius:7px;padding:3px;object-fit:contain}
.topbar-name{font-size:14px;font-weight:800;color:#fce704;letter-spacing:.4px}
.topbar-sep{width:1px;height:20px;background:rgba(255,255,255,.2)}
.topbar-title{font-size:13px;font-weight:500;color:rgba(255,255,255,.7)}
.page{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 20px}
.card{background:#fff;border-radius:14px;border:1px solid #e3ebe5;box-shadow:0 6px 32px rgba(0,0,0,.1);width:100%;max-width:420px;overflow:hidden}
.card-head{background:#037d3c;border-bottom:3px solid #fce704;padding:24px 28px;text-align:center}
.card-head img{width:56px;height:56px;background:#fff;border-radius:12px;padding:5px;object-fit:contain;box-shadow:0 0 0 3px rgba(252,231,4,.3);margin-bottom:12px}
.card-head h1{font-size:18px;font-weight:800;color:#fff}
.card-head p{font-size:11px;color:rgba(255,255,255,.6);margin-top:4px}
.card-body{padding:28px}
.alert{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;font-size:12.5px;color:#dc2626;margin-bottom:18px;display:none}
.alert.show{display:block}
.fg{margin-bottom:16px}
.fg label{display:block;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#037d3c;margin-bottom:5px}
.fg input{width:100%;padding:10px 13px;border:1.5px solid #e3ebe5;border-radius:8px;font-size:14px;font-family:inherit;color:#111;background:#fafcfb;outline:none;transition:.18s}
.fg input:focus{border-color:#037d3c;background:#fff;box-shadow:0 0 0 3px rgba(3,125,60,.1)}
.btn-login{width:100%;padding:12px;background:#037d3c;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;transition:.18s;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:6px}
.btn-login:hover{background:#025a2b}
.btn-login:disabled{opacity:.6;cursor:default}
.spin{width:15px;height:15px;border:2.5px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:sp .7s linear infinite;display:inline-block}
@keyframes sp{to{transform:rotate(360deg)}}
.footer-note{text-align:center;font-size:11px;color:#94a3b8;margin-top:20px}
</style>
</head>
<body>

<?php
// Session configuration for localhost & IP address compatibility
session_set_cookie_params([
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
require_once __DIR__ . '/includes/config.php';

// Redirect if already logged in
if (!empty($_SESSION['css_user'])) {
    header('Location: index.php'); exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db = getDB();
        $u  = $db->real_escape_string($username);
        $row = $db->query("SELECT * FROM cs_users WHERE username='$u' AND is_active=1")->fetch_assoc();

        if ($row && password_verify($password, $row['password_hash'])) {
            $_SESSION['css_user']     = $row['username'];
            $_SESSION['css_user_id']  = $row['id'];
            $_SESSION['css_fullname'] = $row['full_name'];
            $_SESSION['css_role']     = $row['role'];

            // Log login
            $ip  = $db->real_escape_string($_SERVER['REMOTE_ADDR'] ?? '');
            $ua  = $db->real_escape_string(substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255));
            $db->query("INSERT INTO cs_audit_log (user_id, username, action, details, ip_address)
                        VALUES ({$row['id']}, '{$u}', 'login', 'User logged in', '$ip')");

            // Update last login
            $db->query("UPDATE cs_users SET last_login=NOW() WHERE id={$row['id']}");

            header('Location: index.php'); exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter your username and password.';
    }
}
?>

<header class="topbar">
  <img src="https://www.pelco3.org/images/logo.png" alt="PELCO 3" onerror="this.style.display='none'">
  <span class="topbar-name">PELCO III</span>
  <div class="topbar-sep"></div>
  <span class="topbar-title">Customer Service System</span>
</header>

<div class="page">
  <div class="card">
    <div class="card-head">
      <img src="https://www.pelco3.org/images/logo.png" alt="PELCO 3" onerror="this.style.display='none'">
      <h1>Sign In</h1>
      <p>Enter your credentials to access the system</p>
    </div>
    <div class="card-body">

      <div class="alert <?= $error ? 'show' : '' ?>" id="errMsg">
        <?= htmlspecialchars($error) ?>
      </div>

      <form method="POST" onsubmit="handleSubmit(event)">
        <div class="fg">
          <label>Username</label>
          <input type="text" name="username" id="username" autocomplete="username"
                 placeholder="Enter your username" required
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
        <div class="fg">
          <label>Password</label>
          <input type="password" name="password" id="password" autocomplete="current-password"
                 placeholder="Enter your password" required>
        </div>
        <button type="submit" class="btn-login" id="loginBtn">
          Sign In
        </button>
      </form>

      <div class="footer-note">PELCO III &mdash; For authorized personnel only</div>
    </div>
  </div>
</div>

<script>
function handleSubmit(e){
  const btn = document.getElementById('loginBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spin"></span> Signing in…';
}
</script>
</body>
</html>
