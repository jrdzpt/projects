<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
include('db.php');
check_session_timeout();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: index.php"); exit(); }

$stmt = $conn->prepare("SELECT * FROM consumers WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { echo "<script>alert('Consumer not found.');window.location='index.php';</script>"; exit(); }

$errors  = [];
$success = false;

if (isset($_POST['update'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
    $first_name   = trim($_POST['first_name'] ?? '');
    $last_name    = trim($_POST['last_name'] ?? '');
    $middle_name  = trim($_POST['middle_name'] ?? '');
    $gender       = $_POST['gender'] ?? 'Male';
    $birthdate    = $_POST['birthdate'] ?? '';
    $address      = trim($_POST['address'] ?? '');
    $contact      = trim($_POST['contact_number'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $serial       = trim($_POST['SerialNumber'] ?? '');
    $brand        = trim($_POST['MeterBrand'] ?? '');
    $status       = (int)($_POST['status'] ?? 1);
    $prev_reading = (float)($_POST['previous_reading'] ?? 0);
    $pres_reading = (float)($_POST['present_reading'] ?? 0);

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
        $kwh = $pres_reading - $prev_reading;
        try {
            $sql = "UPDATE consumers SET
                    first_name=?, last_name=?, middle_name=?, gender=?,
                    birthdate=?, address=?, contact_number=?, email=?,
                    SerialNumber=?, MeterBrand=?, status=?,
                    previous_reading=?, present_reading=?, kilowatthour=?
                    WHERE id=?";
            $conn->prepare($sql)->execute([
                $first_name, $last_name, $middle_name, $gender,
                $birthdate, $address, $contact, $email,
                $serial, $brand, $status,
                $prev_reading, $pres_reading, $kwh, $id
            ]);
            logActivity($conn, $_SESSION['admin'], 'EDIT', "Updated consumer {$row['accountnumber']}");
            $success = true;
            // Refresh row
            $stmt->execute([$id]);
            $row = $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Edit DB Error: " . $e->getMessage());
            $errors[] = 'A database error occurred. Please try again or contact support.';
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
    <title>PELCO III – Edit Consumer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
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
    <span class="nav-acct"><i class="bi bi-pencil-square"></i> Editing: <?php echo sanitize($row['accountnumber']); ?></span>
    <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i> Dashboard</a>
</nav>

<div class="page">

    <?php if ($success): ?>
    <div class="alert-success">
        <i class="bi bi-check-circle-fill" style="color:var(--g); font-size:1.1rem;"></i>
        <span>Consumer record updated successfully!</span>
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="alert-errors">
        <h6><i class="bi bi-exclamation-triangle-fill"></i> Please fix the following errors:</h6>
        <ul><?php foreach ($errors as $e): ?><li><?php echo sanitize($e); ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <!-- Consumer banner -->
    <div class="card" style="margin-bottom:20px;">
        <div class="acct-banner">
            <img src="<?php echo sanitize($row['photo'] ?: 'https://ui-avatars.com/api/?name='.urlencode($row['first_name'].'+'.$row['last_name']).'&background=037339&color=fff'); ?>"
                 onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($row['first_name'].'+'.$row['last_name']); ?>&background=037339&color=fff'"
                 alt="Photo">
            <div>
                <div class="name"><?php echo sanitize($row['first_name'].' '.$row['middle_name'].' '.$row['last_name']); ?></div>
                <div class="num">Account #<?php echo sanitize($row['accountnumber']); ?></div>
            </div>
            <?php
                $bc = ['1'=>'active','4'=>'inactive','5'=>'pullout'];
                $bl = ['1'=>'Active','4'=>'Inactive','5'=>'Pull-Out'];
                $s  = (string)$row['status'];
            ?>
            <div class="acct-badge <?php echo $bc[$s]??''; ?>"><?php echo $bl[$s]??''; ?></div>
        </div>
    </div>

    <form method="POST" action="edit.php?id=<?php echo $id; ?>" id="editForm">
        <?php echo csrf_field(); ?>

        <!-- Section 1 -->
        <div class="card">
            <div class="card-section-head"><div class="num">1</div><h6>Account Identification</h6></div>
            <div class="card-body">
                <div class="form-grid cols-2">
                    <div class="field">
                        <label>Account Number (Locked)</label>
                        <input type="text" class="readonly-field" value="<?php echo sanitize($row['accountnumber']); ?>" readonly>
                    </div>
                    <div class="field">
                        <label>Account Status</label>
                        <select name="status">
                            <option value="1" <?php echo $row['status']==1?'selected':''; ?>>1 – Active</option>
                            <option value="4" <?php echo $row['status']==4?'selected':''; ?>>4 – Inactive</option>
                            <option value="5" <?php echo $row['status']==5?'selected':''; ?>>5 – Pull-Out</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2 -->
        <div class="card">
            <div class="card-section-head"><div class="num">2</div><h6>Personal Information</h6></div>
            <div class="card-body">
                <div class="form-grid cols-3" style="margin-bottom:18px;">
                    <div class="field">
                        <label>First Name *</label>
                        <input type="text" name="first_name" value="<?php echo sanitize($row['first_name']); ?>" required>
                    </div>
                    <div class="field">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" value="<?php echo sanitize($row['middle_name']); ?>">
                    </div>
                    <div class="field">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" value="<?php echo sanitize($row['last_name']); ?>" required>
                    </div>
                </div>
                <div class="form-grid cols-4" style="margin-bottom:18px;">
                    <div class="field">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="Male" <?php echo $row['gender']=='Male'?'selected':''; ?>>Male</option>
                            <option value="Female" <?php echo $row['gender']=='Female'?'selected':''; ?>>Female</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Birthdate</label>
                        <input type="date" name="birthdate" value="<?php echo $row['birthdate']; ?>">
                    </div>
                    <div class="field">
                        <label>Contact No.</label>
                        <input type="text" name="contact_number" value="<?php echo sanitize($row['contact_number']); ?>">
                    </div>
                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo sanitize($row['email']); ?>">
                    </div>
                </div>
                <div class="field">
                    <label>Address</label>
                    <textarea name="address"><?php echo sanitize($row['address']); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Section 3 -->
        <div class="card">
            <div class="card-section-head"><div class="num">3</div><h6>Meter & Reading Data</h6></div>
            <div class="card-body">
                <div class="form-grid cols-2" style="margin-bottom:18px;">
                    <div class="field">
                        <label>Meter Serial No.</label>
                        <input type="text" name="SerialNumber" value="<?php echo sanitize($row['SerialNumber']); ?>">
                    </div>
                    <div class="field">
                        <label>Meter Brand</label>
                        <input type="text" name="MeterBrand" value="<?php echo sanitize($row['MeterBrand']); ?>">
                    </div>
                </div>
                <div class="form-grid cols-2">
                    <div class="field">
                        <label>Previous Reading</label>
                        <input type="number" step="0.01" name="previous_reading" id="prevRead"
                               value="<?php echo $row['previous_reading']; ?>"
                               class="reading-input"
                               oninput="calcKwh()">
                    </div>
                    <div class="field">
                        <label>Present Reading</label>
                        <input type="number" step="0.01" name="present_reading" id="presRead"
                               value="<?php echo $row['present_reading']; ?>"
                               class="reading-input"
                               oninput="calcKwh()">
                    </div>
                </div>
                <div class="kwh-live" id="kwhBox" style="margin-top:14px;">
                    <div class="val" id="kwhDisplay"><?php echo number_format($row['kilowatthour'],2); ?></div>
                    <div class="lbl">Computed Consumption (kWh)</div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn-cancel">Cancel</a>
            <input type="hidden" name="update" value="1">
            <button type="submit" class="btn-update">Update Record <i class="bi bi-arrow-right"></i></button>
        </div>
    </form>
</div>

<script>
// ── Scroll to errors/success on page load ───────────────────────────────────
window.addEventListener('DOMContentLoaded', () => {
    const errBox = document.querySelector('.alert-errors');
    const okBox  = document.querySelector('.alert-success');
    if (errBox) errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    else if (okBox) okBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
});

function calcKwh() {
    const prev = parseFloat(document.getElementById('prevRead').value) || 0;
    const pres = parseFloat(document.getElementById('presRead').value) || 0;
    document.getElementById('kwhDisplay').textContent = (pres - prev).toFixed(2);
}
calcKwh();

// ── Keyboard shortcuts ──────────────────────────────────────────────────────
document.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.key === 'enter') {
        const form = document.getElementById('editForm');
        if (form) form.submit();
    }
});

// ── Form submission loading state ───────────────────────────────────────────
const editForm = document.getElementById('editForm');
if (editForm) {
    editForm.addEventListener('submit', (e) => {
        const btn = editForm.querySelector('button[type="submit"]');
        if (btn) {
            setTimeout(() => {
                btn.disabled = true;
                btn.style.opacity = '0.6';
                btn.style.cursor = 'not-allowed';
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
            }, 0);
        }
    });
}
</script>
</body>
</html>