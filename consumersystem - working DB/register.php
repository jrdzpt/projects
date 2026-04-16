<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
include('db.php');
check_session_timeout();

$errors  = [];
$success = false;

if (isset($_POST['register'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Sanitize & collect
    $account_number = trim($_POST['account_number'] ?? '');
    $first_name     = trim($_POST['first_name'] ?? '');
    $last_name      = trim($_POST['last_name'] ?? '');
    $middle_name    = trim($_POST['middle_name'] ?? '');
    $gender         = $_POST['gender'] ?? 'Male';
    $birthdate      = $_POST['birthdate'] ?? '';
    $address        = trim($_POST['address'] ?? '');
    $contact        = trim($_POST['contact_number'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $serial         = trim($_POST['SerialNumber'] ?? '');
    $brand          = trim($_POST['MeterBrand'] ?? '');
    $status         = (int)($_POST['status'] ?? 1);
    $prev_reading   = (float)($_POST['previous_reading'] ?? 0);
    $pres_reading   = (float)($_POST['present_reading'] ?? 0);

    // Validate
    if (!preg_match('/^[0-9]{8}$/', $account_number))
        $errors[] = 'Account Number must be exactly 8 digits.';
    if (empty($first_name)) $errors[] = 'First name is required.';
    if (empty($last_name))  $errors[] = 'Last name is required.';
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Invalid email address format.';
    if (!empty($contact) && !preg_match('/^(\+63|0)[0-9]{10}$/', $contact))
        $errors[] = 'Contact number must be a valid Philippine mobile number (e.g., 09123456789 or +639123456789).';
    if (!empty($birthdate) && !strtotime($birthdate))
        $errors[] = 'Invalid birthdate.';
    if ($prev_reading < 0) $errors[] = 'Previous reading cannot be negative.';
    if ($pres_reading < $prev_reading) $errors[] = 'Present reading cannot be less than previous reading.';
    if (!in_array($status, [1,4,5])) $errors[] = 'Invalid status selected.';
    if (!in_array($gender, ['Male','Female','Other'])) $errors[] = 'Invalid gender selected.';

    if (empty($errors)) {
        // Check duplicate
        $check = $conn->prepare("SELECT id FROM consumers WHERE accountnumber = ?");
        $check->execute([$account_number]);
        if ($check->rowCount() > 0) {
            $errors[] = "Account Number {$account_number} is already registered.";
        }
    }

    if (empty($errors)) {
        $kwh = $pres_reading - $prev_reading;
        $photo_path = 'uploads/default_avatar.png'; // fallback

        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === 0) {
            $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
            $ftype   = mime_content_type($_FILES['photo']['tmp_name']);
            if (!in_array($ftype, $allowed)) {
                $errors[] = 'Invalid image format. Use JPG, PNG, or WebP.';
            } elseif ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Photo must be under 5MB.';
            } else {
                $ext        = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $photo_name = 'PELCO_' . $account_number . '_' . time() . '.' . $ext;
                $target_dir = 'uploads/';
                if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_dir . $photo_name)) {
                    $photo_path = $target_dir . $photo_name;
                }
            }
        }

        if (empty($errors)) {
            try {
                $sql  = "INSERT INTO consumers 
                         (accountnumber, first_name, last_name, middle_name, gender, birthdate,
                          address, contact_number, email, photo, SerialNumber, MeterBrand,
                          status, previous_reading, present_reading, kilowatthour)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $conn->prepare($sql)->execute([
                    $account_number, $first_name, $last_name, $middle_name,
                    $gender, $birthdate, $address, $contact, $email, $photo_path,
                    $serial, $brand, $status, $prev_reading, $pres_reading, $kwh
                ]);
                logActivity($conn, $_SESSION['admin'], 'REGISTER', "Added consumer $account_number – $first_name $last_name");
                header("Location: index.php?registered=1");
                exit();
            } catch (PDOException $e) {
                error_log("Registration DB Error: " . $e->getMessage());
                $errors[] = 'A database error occurred. Please try again or contact support.';
            }
        }
    }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PELCO III – New Consumer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="topnav">
    <a class="nav-brand" href="index.php">
        <img src="https://www.pelco3.org/images/logo.png" alt="PELCO III">
        <div class="nav-brand-text">
            <span class="nav-brand-title">PELCO III</span>
            <span class="nav-brand-sub">Consumer Management System</span>
        </div>
    </a>
    <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i> Dashboard</a>
</nav>

<div class="page">
    <div class="page-header">
        <h1>New Consumer Registration</h1>
        <p>Complete all required fields marked with <span style="color:var(--r);">*</span> to enroll a new consumer.</p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert-errors">
        <h6><i class="bi bi-exclamation-triangle-fill"></i> Please fix the following errors:</h6>
        <ul><?php foreach ($errors as $e): ?><li><?php echo sanitize($e); ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="register.php" enctype="multipart/form-data" id="regForm">
        <?php echo csrf_field(); ?>

        <!-- Section 1: Account -->
        <div class="card">
            <div class="card-section-head">
                <div class="num">1</div>
                <h6>Account & Profile</h6>
            </div>
            <div class="card-body">
                <div class="form-grid cols-3">
                    <div class="field">
                        <label>Account Number <span class="req">*</span></label>
                        <input type="text" name="account_number" id="acctNum" pattern="\d{8}" maxlength="8"
                               placeholder="00000000"
                               value="<?php echo sanitize($_POST['account_number'] ?? ''); ?>"
                               style="font-family:'JetBrains Mono',monospace; font-size:1.1rem; letter-spacing:3px;"
                               required>
                        <div class="hint">Exactly 8 numeric digits</div>
                    </div>
                    <div class="field">
                        <label>Account Status <span class="req">*</span></label>
                        <select name="status">
                            <option value="1" <?php echo ($_POST['status']??'1')=='1'?'selected':''; ?>>1 – Active</option>
                            <option value="4" <?php echo ($_POST['status']??'')=='4'?'selected':''; ?>>4 – Inactive</option>
                            <option value="5" <?php echo ($_POST['status']??'')=='5'?'selected':''; ?>>5 – Pull-Out</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Profile Photo</label>
                        <div class="photo-uploader" id="photoUploader">
                            <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/webp">
                            <div class="up-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                            <div class="up-label">Upload Photo</div>
                            <div class="up-hint">JPG · PNG · WebP · Max 5MB</div>
                            <img class="preview-img" id="photoPreview" src="" alt="Preview">
                            <div class="upload-overlay">
                                <i class="bi bi-arrow-repeat"></i>
                                Change Photo
                            </div>
                        </div>
                        <div class="photo-filename" id="photoFilename"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Personal -->
        <div class="card">
            <div class="card-section-head">
                <div class="num">2</div>
                <h6>Personal Information</h6>
            </div>
            <div class="card-body">
                <div class="form-grid cols-3" style="margin-bottom:18px;">
                    <div class="field">
                        <label>First Name <span class="req">*</span></label>
                        <input type="text" name="first_name" placeholder="Juan" value="<?php echo sanitize($_POST['first_name']??''); ?>" required>
                    </div>
                    <div class="field">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" placeholder="Dela" value="<?php echo sanitize($_POST['middle_name']??''); ?>">
                    </div>
                    <div class="field">
                        <label>Last Name <span class="req">*</span></label>
                        <input type="text" name="last_name" placeholder="Cruz" value="<?php echo sanitize($_POST['last_name']??''); ?>" required>
                    </div>
                </div>
                <div class="form-grid cols-4" style="margin-bottom:18px;">
                    <div class="field">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="Male" <?php echo ($_POST['gender']??'Male')=='Male'?'selected':''; ?>>Male</option>
                            <option value="Female" <?php echo ($_POST['gender']??'')=='Female'?'selected':''; ?>>Female</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Birthdate</label>
                        <input type="date" name="birthdate" value="<?php echo sanitize($_POST['birthdate']??''); ?>">
                    </div>
                    <div class="field">
                        <label>Contact No.</label>
                        <input type="text" name="contact_number" placeholder="09XXXXXXXXX" value="<?php echo sanitize($_POST['contact_number']??''); ?>">
                    </div>
                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="email@example.com" value="<?php echo sanitize($_POST['email']??''); ?>">
                    </div>
                </div>
                <div class="form-grid cols-1">
                    <div class="field">
                        <label>Permanent Address <span class="req">*</span></label>
                        <textarea name="address" placeholder="Street, Barangay, Municipality, Province" required><?php echo sanitize($_POST['address']??''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Meter -->
        <div class="card">
            <div class="card-section-head">
                <div class="num">3</div>
                <h6>Meter & Reading Data</h6>
            </div>
            <div class="card-body">
                <div class="form-grid cols-2" style="margin-bottom:18px;">
                    <div class="field">
                        <label>Meter Serial No.</label>
                        <input type="text" name="SerialNumber" placeholder="e.g. MSN-123456" value="<?php echo sanitize($_POST['SerialNumber']??''); ?>">
                    </div>
                    <div class="field">
                        <label>Meter Brand</label>
                        <input type="text" name="MeterBrand" placeholder="e.g. Landis+Gyr" value="<?php echo sanitize($_POST['MeterBrand']??''); ?>">
                    </div>
                </div>
                <div class="form-grid cols-2">
                    <div class="field">
                        <label>Previous Reading</label>
                        <input type="number" step="0.01" name="previous_reading" id="prevRead"
                               value="<?php echo ($_POST['previous_reading']??'0.00'); ?>"
                               class="reading-input"
                               oninput="calcKwh()">
                    </div>
                    <div class="field">
                        <label>Present Reading</label>
                        <input type="number" step="0.01" name="present_reading" id="presRead"
                               value="<?php echo ($_POST['present_reading']??'0.00'); ?>"
                               class="reading-input"
                               oninput="calcKwh()">
                    </div>
                </div>
                <div class="kwh-live" id="kwhBox" style="margin-top:14px;">
                    <div class="val" id="kwhDisplay">0.00</div>
                    <div class="lbl">Computed Consumption (kWh)</div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn-cancel">Cancel</a>
            <input type="hidden" name="register" value="1">
            <button type="submit" class="btn-save">Save Consumer <i class="bi bi-arrow-right"></i></button>
        </div>
    </form>
</div>

<script>
// ── Scroll to errors on page load ───────────────────────────────────────────
window.addEventListener('DOMContentLoaded', () => {
    const errBox = document.querySelector('.alert-errors');
    if (errBox) {
        errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

function calcKwh() {
    const prev = parseFloat(document.getElementById('prevRead').value) || 0;
    const pres = parseFloat(document.getElementById('presRead').value) || 0;
    document.getElementById('kwhDisplay').textContent = (pres - prev).toFixed(2);
}
calcKwh();

// Photo preview
document.getElementById('photoInput').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('photoPreview').src = e.target.result;
        document.getElementById('photoUploader').classList.add('has-file');
        const fn = document.getElementById('photoFilename');
        fn.textContent = file.name;
        fn.classList.add('visible');
    };
    reader.readAsDataURL(file);
});

// Account number: digits only
document.getElementById('acctNum').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0,8);
});

// ── Keyboard shortcuts ──────────────────────────────────────────────────────
document.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.key === 'enter') {
        const form = document.getElementById('regForm');
        if (form) form.submit();
    }
});

// ── Form submission loading state ───────────────────────────────────────────
const regForm = document.getElementById('regForm');
if (regForm) {
    regForm.addEventListener('submit', (e) => {
        const btn = regForm.querySelector('button[type="submit"]');
        if (btn) {
            // Small delay so browser collects form data first
            setTimeout(() => {
                btn.disabled = true;
                btn.style.opacity = '0.6';
                btn.style.cursor = 'not-allowed';
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Registering...';
            }, 0);
        }
    });
}
</script>
</body>
</html>