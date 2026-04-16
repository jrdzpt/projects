<?php
session_start();

if (isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

include('db.php');

$error   = '';
$success = '';

// Check for session expiration
if (isset($_GET['session_expired'])) {
    $error = 'Your session has expired. Please log in again.';
}

// ── Brute-force throttle (simple session-based) ──────────────────────────────
if (!isset($_SESSION['login_attempts']))  $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['lockout_until']))   $_SESSION['lockout_until']  = 0;

$locked = time() < $_SESSION['lockout_until'];

if (isset($_POST['login']) && !$locked) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $user = trim($_POST['username'] ?? '');
        $pass = $_POST['password'] ?? '';

        if (empty($user) || empty($pass)) {
            $error = 'Please enter both username and password.';
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$user]);
            $account = $stmt->fetch();

            if ($account && ($pass === $account['password'] || password_verify($pass, $account['password']))) {
                // Successful login
                session_regenerate_id(true);
                $_SESSION['admin']          = $account['username'];
                $_SESSION['login_time']     = time();
                $_SESSION['login_attempts'] = 0;
                $_SESSION['lockout_until']  = 0;

                logActivity($conn, $account['username'], 'LOGIN', 'Successful login from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['login_attempts']++;
                if ($_SESSION['login_attempts'] >= 5) {
                    $_SESSION['lockout_until'] = time() + 300; // 5-minute lockout
                    $error = 'Too many failed attempts. Account locked for 5 minutes.';
                } else {
                    $remaining = 5 - $_SESSION['login_attempts'];
                    $error = "Invalid credentials. {$remaining} attempt(s) remaining.";
                }
            }
        }
    }
} elseif ($locked) {
    $wait = ceil(($_SESSION['lockout_until'] - time()) / 60);
    $error = "Account temporarily locked. Please wait {$wait} minute(s).";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PELCO III – System Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="login-wrap">
    <div class="brand-bar">
        <img src="https://www.pelco3.org/images/logo.png" alt="PELCO III Logo">
        <h1>PELCO III</h1>
        <p>Consumer Management System</p>
    </div>

    <div class="card">
        <div class="card-title">Authorized Access Only</div>

        <?php if ($error): ?>
        <div class="alert-error">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?php echo sanitize($error); ?></span>
        </div>
        <?php endif; ?>

        <?php if ($_SESSION['login_attempts'] > 0): ?>
        <div class="attempts-bar">
            <?php for ($i = 0; $i < 5; $i++): ?>
                <div class="attempt-dot <?php echo $i < $_SESSION['login_attempts'] ? 'used' : ''; ?>"></div>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <?php echo csrf_field(); ?>
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       placeholder="Enter your username"
                       value="<?php echo sanitize($_POST['username'] ?? ''); ?>"
                       <?php echo $locked ? 'disabled' : ''; ?>
                       autofocus required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <div class="show-pass-wrap">
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           <?php echo $locked ? 'disabled' : ''; ?>
                           required>
                    <button type="button" class="toggle-pw" onclick="togglePw()" title="Show/hide password"><i class="bi bi-eye" id="eyeIcon"></i></button>
                </div>
            </div>
            <button type="submit" name="login" class="btn-login" <?php echo $locked ? 'disabled' : ''; ?>>
                Sign In →
            </button>
        </form>
    </div>

    <p class="footer-note">© 2026 <span>PELCO III</span> · Apalit, Pampanga · v2.0</p>
</div>

<script>
function togglePw() {
    const pw = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    const isHidden = pw.type === 'password';
    pw.type = isHidden ? 'text' : 'password';
    icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
}
<?php if ($locked): ?>
// Countdown to unlock
(function() {
    const lockUntil = <?php echo $_SESSION['lockout_until']; ?> * 1000;
    const btn = document.querySelector('.btn-login');
    const interval = setInterval(() => {
        const remaining = Math.ceil((lockUntil - Date.now()) / 1000);
        if (remaining <= 0) {
            clearInterval(interval);
            location.reload();
        } else {
            btn.textContent = `Locked – wait ${remaining}s`;
        }
    }, 1000);
})();
<?php endif; ?>
</script>
</body>
</html>
