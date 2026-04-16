<?php
// ─── PHP backend: runs when form is submitted via fetch ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    header('Content-Type: application/json');

    $host       = trim($_POST['db_host']    ?? 'localhost');
    $port       = (int)($_POST['db_port']   ?? 3306);
    $user       = trim($_POST['db_user']    ?? 'root');
    $pass       = $_POST['db_pass']         ?? '';
    $dbname     = trim($_POST['db_name']    ?? 'consumer_db');
    $tz         = trim($_POST['tz']         ?? 'Asia/Manila');
    $adminUser  = trim($_POST['admin_user'] ?? 'admin');
    $adminPass  = trim($_POST['admin_pass'] ?? 'admin123');

    $steps = [];
    $ok    = true;

    function step(string $msg, string $type = 'ok'): array {
        return ['msg' => $msg, 'type' => $type, 'ts' => date('H:i:s')];
    }

    // 1. Connect (without DB selected)
    try {
        $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $steps[] = step("Connected to MySQL at $host:$port");
    } catch (PDOException $e) {
        $steps[] = step("Connection failed: " . $e->getMessage(), 'err');
        echo json_encode(['ok' => false, 'steps' => $steps]);
        exit();
    }

    // 2. Create database
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $steps[] = step("Database '$dbname' created (or already exists)");
        $pdo->exec("USE `$dbname`");
        $steps[] = step("Switched to database '$dbname'");
    } catch (PDOException $e) {
        $steps[] = step("Failed to create database: " . $e->getMessage(), 'err');
        echo json_encode(['ok' => false, 'steps' => $steps]);
        exit();
    }

    // 3. Create tables
    $tables = [
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            username   VARCHAR(50)  NOT NULL UNIQUE,
            password   VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'consumers' => "CREATE TABLE IF NOT EXISTS consumers (
            id               INT AUTO_INCREMENT PRIMARY KEY,
            accountnumber    VARCHAR(8)    NOT NULL UNIQUE,
            first_name       VARCHAR(100),
            last_name        VARCHAR(100),
            middle_name      VARCHAR(100),
            gender           VARCHAR(20),
            birthdate        DATE,
            address          TEXT,
            contact_number   VARCHAR(20),
            email            VARCHAR(100),
            photo            VARCHAR(255),
            SerialNumber     VARCHAR(100),
            MeterBrand       VARCHAR(100),
            status           TINYINT       DEFAULT 1,
            present_reading  DECIMAL(10,2) DEFAULT 0.00,
            previous_reading DECIMAL(10,2) DEFAULT 0.00,
            kilowatthour     DECIMAL(10,2) DEFAULT 0.00,
            created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_accountnumber (accountnumber),
            INDEX idx_status (status),
            INDEX idx_name (last_name, first_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'archive' => "CREATE TABLE IF NOT EXISTS archive (
            id               INT PRIMARY KEY,
            accountnumber    VARCHAR(8),
            first_name       VARCHAR(100),
            last_name        VARCHAR(100),
            middle_name      VARCHAR(100),
            gender           VARCHAR(20),
            birthdate        DATE,
            address          TEXT,
            contact_number   VARCHAR(20),
            email            VARCHAR(100),
            photo            VARCHAR(255),
            SerialNumber     VARCHAR(100),
            MeterBrand       VARCHAR(100),
            status           TINYINT,
            present_reading  DECIMAL(10,2),
            previous_reading DECIMAL(10,2),
            kilowatthour     DECIMAL(10,2),
            archived_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_archived_at  (archived_at),
            INDEX idx_accountnumber(accountnumber)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        'activity_log' => "CREATE TABLE IF NOT EXISTS activity_log (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            admin_user VARCHAR(100),
            action     VARCHAR(50),
            detail     TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_admin  (admin_user),
            INDEX idx_action (action),
            INDEX idx_created(created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    foreach ($tables as $name => $sql) {
        try {
            $pdo->exec($sql);
            $steps[] = step("Table '$name' created successfully");
        } catch (PDOException $e) {
            $steps[] = step("Failed to create '$name': " . $e->getMessage(), 'err');
            $ok = false;
        }
    }

    // 4. Uploads directory reminder
    $steps[] = step("Reminder: create an 'uploads/' folder with write permissions (chmod 755)", 'warn');

    // 5. Insert admin account
    if ($ok) {
        try {
            $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $check->execute([$adminUser]);
            if ($check->rowCount() > 0) {
                $steps[] = step("Admin '$adminUser' already exists — skipped", 'warn');
            } else {
                $hashed = password_hash($adminPass, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)")
                    ->execute([$adminUser, $hashed]);
                $steps[] = step("Admin account '$adminUser' created (password hashed with bcrypt)");
            }
        } catch (PDOException $e) {
            $steps[] = step("Failed to create admin: " . $e->getMessage(), 'err');
            $ok = false;
        }
    }

    // 6. Write db.php automatically
    if ($ok) {
        $dbContent = <<<PHP
<?php
date_default_timezone_set('$tz');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

define('DB_HOST', '$host');
define('DB_PORT', '$port');
define('DB_USER', '$user');
define('DB_PASS', '$pass');
define('DB_NAME', '$dbname');

try {
    \$conn = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException \$e) {
    error_log("DB Error: " . \$e->getMessage());
    die("Database connection failed. Contact system administrator.");
}

function getStatusLabel(int \$s): string {
    return match(\$s) { 1=>'Active', 4=>'Inactive', 5=>'Pull-Out', default=>'Unknown' };
}
function getStatusBadgeClass(int \$s): string {
    return match(\$s) { 1=>'badge-active', 4=>'badge-inactive', 5=>'badge-pullout', default=>'' };
}
function getDashboardStats(PDO \$conn): array {
    return [
        'total'    => (int)\$conn->query("SELECT COUNT(*) FROM consumers")->fetchColumn(),
        'active'   => (int)\$conn->query("SELECT COUNT(*) FROM consumers WHERE status=1")->fetchColumn(),
        'inactive' => (int)\$conn->query("SELECT COUNT(*) FROM consumers WHERE status=4")->fetchColumn(),
        'pullout'  => (int)\$conn->query("SELECT COUNT(*) FROM consumers WHERE status=5")->fetchColumn(),
        'archived' => (int)\$conn->query("SELECT COUNT(*) FROM archive")->fetchColumn(),
        'avg_kwh'  => (float)\$conn->query("SELECT COALESCE(AVG(kilowatthour),0) FROM consumers WHERE kilowatthour>0")->fetchColumn(),
        'total_kwh'=> (float)\$conn->query("SELECT COALESCE(SUM(kilowatthour),0) FROM consumers")->fetchColumn(),
    ];
}
function sanitize(string \$v): string {
    return htmlspecialchars(trim(\$v), ENT_QUOTES, 'UTF-8');
}
function logActivity(PDO \$conn, string \$admin, string \$action, string \$detail=''): void {
    try {
        \$conn->prepare("INSERT INTO activity_log (admin_user,action,detail,created_at) VALUES (?,?,?,NOW())")
             ->execute([\$admin,\$action,\$detail]);
    } catch(PDOException \$e){}
}

// ── CSRF Protection ──────────────────────────────────────────────────────────
function generate_csrf_token(): string {
    if (empty(\$_SESSION['csrf_token'])) {
        \$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return \$_SESSION['csrf_token'];
}
function verify_csrf_token(string \$token): bool {
    return isset(\$_SESSION['csrf_token']) && hash_equals(\$_SESSION['csrf_token'], \$token);
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
}

// ── Session timeout protection ───────────────────────────────────────────────
define('SESSION_TIMEOUT', 1800);
function check_session_timeout(): void {
    if (isset(\$_SESSION['login_time'])) {
        if (time() - \$_SESSION['login_time'] > SESSION_TIMEOUT) {
            session_destroy();
            header("Location: login.php?session_expired=1");
            exit();
        } else {
            \$_SESSION['login_time'] = time();
        }
    }
}
?>
PHP;
        if (file_put_contents(__DIR__ . '/db.php', $dbContent) !== false) {
            $steps[] = step("db.php updated with your connection settings");
        } else {
            $steps[] = step("Could not auto-write db.php — update it manually", 'warn');
        }
    }

    if ($ok) {
        $steps[] = step("Installation complete! Delete setup.php before going live.", 'info');
    }

    echo json_encode(['ok' => $ok, 'steps' => $steps]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PELCO III &mdash; Database Setup</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --g: #049f4d; --g-dark: #037339; --g-light: #e8f7ee;
            --y: #fce704; --b: #0102f3; --r: #fb0908;
            --border: #e2e8df; --muted: #6b7566; --text: #1a1f16;
        }

        body {
            font-family: 'Sora', sans-serif;
            background: var(--g-dark);
            background-image:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(4,159,77,0.5) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 90%, rgba(1,2,243,0.15) 0%, transparent 60%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .installer-wrap {
            width: 100%;
            max-width: 620px;
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Brand */
        .brand {
            display: flex; align-items: center; gap: 14px;
            margin-bottom: 28px;
        }
        .brand img { width: 56px; filter: drop-shadow(0 4px 16px rgba(0,0,0,0.4)); }
        .brand-text h1 { font-size: 1.4rem; font-weight: 800; color: #fff; letter-spacing: -0.3px; }
        .brand-text p  { font-size: 0.75rem; color: rgba(255,255,255,0.6); font-family: 'JetBrains Mono', monospace; letter-spacing: 1.5px; margin-top: 2px; }

        /* Card */
        .card {
            background: #fff; border-radius: 20px;
            border-top: 4px solid var(--y);
            box-shadow: 0 10px 50px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .card-header {
            padding: 22px 28px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 12px;
        }
        .card-header-icon {
            width: 40px; height: 40px; border-radius: 10px;
            background: var(--g-light); display: flex; align-items: center; justify-content: center;
            color: var(--g-dark); font-size: 1.2rem;
        }
        .card-header h2 { font-size: 1rem; font-weight: 800; color: var(--text); }
        .card-header p  { font-size: 0.75rem; color: var(--muted); margin-top: 2px; }

        .card-body { padding: 28px; }

        /* Config fields */
        .config-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; }
        .field label {
            display: block; font-size: 0.72rem; font-weight: 700;
            color: var(--muted); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px;
        }
        .field input {
            width: 100%; padding: 10px 14px;
            border: 2px solid var(--border); border-radius: 10px;
            font-family: 'JetBrains Mono', monospace; font-size: 0.88rem;
            color: var(--text); background: #fafbf9; outline: none; transition: all 0.2s;
        }
        .field input:focus { border-color: var(--g); background: #fff; box-shadow: 0 0 0 4px rgba(4,159,77,0.1); }
        .field.full { grid-column: 1 / -1; }

        /* Info box */
        .info-box {
            background: #fffbeb; border: 1px solid #fde68a;
            border-left: 4px solid #f59e0b;
            border-radius: 10px; padding: 12px 16px; margin-bottom: 20px;
            font-size: 0.8rem; color: #92400e;
            display: flex; gap: 10px; align-items: flex-start;
        }
        .info-box i { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }

        /* Install button */
        .btn-install {
            width: 100%; padding: 14px;
            background: var(--g); color: #fff;
            font-family: 'Sora', sans-serif; font-size: 0.92rem; font-weight: 800;
            letter-spacing: 1px; text-transform: uppercase;
            border: none; border-radius: 12px; cursor: pointer;
            transition: all 0.25s; display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-install:hover { background: var(--g-dark); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(4,159,77,0.35); }
        .btn-install:disabled { background: #9ca3af; cursor: not-allowed; transform: none; box-shadow: none; }

        /* Progress log */
        .log-wrap {
            display: none; margin-top: 22px;
            background: #0f1a0d; border-radius: 12px; overflow: hidden;
        }
        .log-header {
            padding: 10px 16px; background: #1a2e18;
            display: flex; align-items: center; gap: 8px;
            font-size: 0.72rem; font-weight: 700; color: rgba(255,255,255,0.5);
            font-family: 'JetBrains Mono', monospace; text-transform: uppercase; letter-spacing: 1px;
        }
        .log-header .dot { width: 8px; height: 8px; border-radius: 50%; }
        .dot-r { background: #fb0908; } .dot-y { background: #fce704; } .dot-g { background: #02f801; }
        .log-body {
            padding: 16px; max-height: 280px; overflow-y: auto;
            font-family: 'JetBrains Mono', monospace; font-size: 0.78rem; line-height: 1.8;
        }
        .log-line { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 2px; }
        .log-line .ts  { color: #4a7c59; white-space: nowrap; flex-shrink: 0; }
        .log-line.ok   .msg { color: #4ade80; }
        .log-line.err  .msg { color: #f87171; }
        .log-line.info .msg { color: #93c5fd; }
        .log-line.warn .msg { color: #fde68a; }

        /* Success panel */
        .success-panel {
            display: none;
            background: var(--g-light); border: 1px solid rgba(4,159,77,0.3);
            border-radius: 12px; padding: 22px 24px; margin-top: 22px; text-align: center;
        }
        .success-panel .success-icon {
            width: 52px; height: 52px; border-radius: 50%;
            background: var(--g); color: #fff; font-size: 1.5rem;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
        }
        .success-panel h3 { font-size: 1rem; font-weight: 800; color: var(--g-dark); }
        .success-panel p  { font-size: 0.82rem; color: var(--muted); margin-top: 6px; }
        .btn-goto-login {
            display: inline-flex; align-items: center; gap: 8px;
            margin-top: 16px; padding: 10px 28px;
            background: var(--g); color: #fff;
            font-family: 'Sora', sans-serif; font-weight: 700; font-size: 0.88rem;
            border-radius: 10px; text-decoration: none; transition: all 0.2s;
        }
        .btn-goto-login:hover { background: var(--g-dark); color: #fff; }

        /* Error panel */
        .error-panel {
            display: none;
            background: #fff5f5; border: 1px solid #fecaca;
            border-left: 4px solid var(--r);
            border-radius: 12px; padding: 16px 20px; margin-top: 20px;
        }
        .error-panel h4 { font-size: 0.88rem; font-weight: 800; color: #991b1b; margin-bottom: 6px; }
        .error-panel p  { font-size: 0.8rem; color: #7f1d1d; }

        .footer-note { text-align: center; margin-top: 20px; font-size: 0.72rem; color: rgba(255,255,255,0.4); font-family: 'JetBrains Mono', monospace; }
        .footer-note span { color: var(--y); }

        /* Spinner */
        .spinner {
            width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff; border-radius: 50%;
            animation: spin 0.7s linear infinite; display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div class="installer-wrap">

    <div class="brand">
        <img src="https://www.pelco3.org/images/logo.png" alt="PELCO III Logo">
        <div class="brand-text">
            <h1>PELCO III</h1>
            <p>DATABASE SETUP WIZARD &mdash; v2.0</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-header-icon"><i class="bi bi-database-fill-gear"></i></div>
            <div>
                <h2>One-Click Database Installer</h2>
                <p>Configure your MySQL connection and install all tables automatically.</p>
            </div>
        </div>

        <div class="card-body">

            <div class="info-box">
                <i class="bi bi-info-circle-fill"></i>
                <span>This wizard will create the <strong>consumer_db</strong> database, all required tables, indexes, and your first admin account. Run this once before launching the system.</span>
            </div>

            <form id="setupForm">
                <div class="config-grid">
                    <div class="field">
                        <label><i class="bi bi-hdd-network"></i> DB Host</label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required>
                    </div>
                    <div class="field">
                        <label><i class="bi bi-hash"></i> DB Port</label>
                        <input type="text" id="db_port" name="db_port" value="3306">
                    </div>
                    <div class="field">
                        <label><i class="bi bi-person-fill"></i> DB Username</label>
                        <input type="text" id="db_user" name="db_user" value="root" required>
                    </div>
                    <div class="field">
                        <label><i class="bi bi-key-fill"></i> DB Password</label>
                        <input type="password" id="db_pass" name="db_pass" placeholder="Leave blank if none">
                    </div>
                    <div class="field">
                        <label><i class="bi bi-database-fill"></i> Database Name</label>
                        <input type="text" id="db_name" name="db_name" value="consumer_db" required>
                    </div>
                    <div class="field">
                        <label><i class="bi bi-globe"></i> Timezone</label>
                        <input type="text" id="tz" name="tz" value="Asia/Manila" required>
                    </div>
                    <div class="field full" style="border-top:1px solid var(--border); padding-top:16px; margin-top:4px;">
                        <label style="color:var(--g-dark); font-size:0.75rem; margin-bottom:8px; display:block;"><i class="bi bi-shield-lock-fill"></i> &nbsp;First Admin Account</label>
                    </div>
                    <div class="field">
                        <label>Admin Username</label>
                        <input type="text" id="admin_user" name="admin_user" value="admin" required>
                    </div>
                    <div class="field">
                        <label>Admin Password</label>
                        <input type="password" id="admin_pass" name="admin_pass" value="admin123" required>
                    </div>
                </div>

                <button type="submit" class="btn-install" id="installBtn">
                    <span class="spinner" id="spinner"></span>
                    <i class="bi bi-play-fill" id="btnIcon"></i>
                    <span id="btnText">Install Database Now</span>
                </button>
            </form>

            <div class="log-wrap" id="logWrap">
                <div class="log-header">
                    <span class="dot dot-r"></span>
                    <span class="dot dot-y"></span>
                    <span class="dot dot-g"></span>
                    &nbsp;Installation Log
                </div>
                <div class="log-body" id="logBody"></div>
            </div>

            <div class="success-panel" id="successPanel">
                <div class="success-icon"><i class="bi bi-check-lg"></i></div>
                <h3>Database Installed Successfully!</h3>
                <p>All tables created. Your admin account is ready.<br>
                   <strong>Delete <code>setup.php</code> from the server</strong> before going live.</p>
                <a href="login.php" class="btn-goto-login">
                    <i class="bi bi-box-arrow-in-right"></i> Go to Login
                </a>
            </div>

            <div class="error-panel" id="errorPanel">
                <h4><i class="bi bi-x-circle-fill"></i> Installation Failed</h4>
                <p id="errorMsg">Check the log above for details.</p>
            </div>

        </div>
    </div>

    <p class="footer-note">&copy; 2026 <span>PELCO III</span> &mdash; Consumer Management System</p>
</div>


<script>
const form     = document.getElementById('setupForm');
const logWrap  = document.getElementById('logWrap');
const logBody  = document.getElementById('logBody');
const btnText  = document.getElementById('btnText');
const btnIcon  = document.getElementById('btnIcon');
const spinner  = document.getElementById('spinner');
const installBtn = document.getElementById('installBtn');

function appendLog(step) {
    const line = document.createElement('div');
    line.className = `log-line ${step.type}`;
    line.innerHTML = `<span class="ts">[${step.ts}]</span><span class="msg">${escHtml(step.msg)}</span>`;
    logBody.appendChild(line);
    logBody.scrollTop = logBody.scrollHeight;
}

function escHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Reset UI
    logBody.innerHTML = '';
    logWrap.style.display = 'block';
    document.getElementById('successPanel').style.display = 'none';
    document.getElementById('errorPanel').style.display = 'none';

    installBtn.disabled = true;
    btnText.textContent = 'Installing…';
    btnIcon.style.display = 'none';
    spinner.style.display = 'inline-block';

    const fd = new FormData(form);
    fd.append('action', 'install');

    try {
        const res  = await fetch(window.location.href, { method: 'POST', body: fd });
        const data = await res.json();

        data.steps.forEach(appendLog);

        if (data.ok) {
            document.getElementById('successPanel').style.display = 'block';
        } else {
            document.getElementById('errorPanel').style.display = 'block';
        }
    } catch(err) {
        appendLog({ msg: 'Request failed: ' + err.message, type: 'err', ts: new Date().toTimeString().slice(0,8) });
        document.getElementById('errorPanel').style.display = 'block';
    } finally {
        installBtn.disabled = false;
        btnText.textContent = 'Retry Installation';
        btnIcon.style.display = '';
        spinner.style.display = 'none';
    }
});
</script>

</body>
</html>