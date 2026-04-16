<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
include('db.php');
check_session_timeout();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header("Location: index.php");
    exit();
}

// Fetch consumer info for confirmation page
$stmt = $conn->prepare("SELECT * FROM consumers WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$consumer = $stmt->fetch();

if (!$consumer) {
    header("Location: index.php?error=not_found");
    exit();
}

// Process archive on POST confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_archive'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $archiveError = 'Invalid request. Please try again.';
    } else {
        try {
        $conn->beginTransaction();

        $archiveSql = "INSERT INTO archive 
            (id, accountnumber, first_name, last_name, middle_name, gender, birthdate,
             address, contact_number, email, photo, SerialNumber, MeterBrand,
             status, present_reading, previous_reading, kilowatthour)
            SELECT id, accountnumber, first_name, last_name, middle_name, gender, birthdate,
                   address, contact_number, email, photo, SerialNumber, MeterBrand,
                   status, present_reading, previous_reading, kilowatthour
            FROM consumers WHERE id = ?";
        $conn->prepare($archiveSql)->execute([$id]);

        $conn->prepare("DELETE FROM consumers WHERE id = ?")->execute([$id]);

        $conn->commit();

        logActivity($conn, $_SESSION['admin'], 'ARCHIVE',
            "Archived consumer {$consumer['accountnumber']} – {$consumer['first_name']} {$consumer['last_name']}");

        header("Location: index.php?archived=1");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Archive DB Error: " . $e->getMessage());
        $archiveError = 'A database error occurred during archiving. Please try again or contact support.';
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PELCO III – Confirm Archive</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="confirm-card">
    <div class="card-header">
        <div class="warn-icon"><i class="bi bi-exclamation-triangle-fill" style="color:#dc2626;"></i></div>
        <div>
            <h2>Archive Consumer?</h2>
            <p>This action will move the record to the archive system.</p>
        </div>
    </div>
    <div class="card-body">

        <?php if (isset($archiveError)): ?>
        <div class="error-note"><i class="bi bi-x-circle-fill"></i> Archive failed: <?php echo sanitize($archiveError); ?></div>
        <?php endif; ?>

        <div class="consumer-info">
            <div class="info-row">
                <span class="info-label">Account Number</span>
                <span class="info-value"><?php echo sanitize($consumer['accountnumber']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Full Name</span>
                <span class="info-value"><?php echo sanitize($consumer['first_name'].' '.$consumer['last_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-value"><?php echo getStatusLabel((int)$consumer['status']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Last Reading</span>
                <span class="info-value"><?php echo number_format($consumer['present_reading'],2); ?> kWh</span>
            </div>
        </div>

        <div class="warning-note">
            <i class="bi bi-info-circle-fill"></i> The consumer record will be moved to the <strong>Archive System</strong> and removed from the active list. This can be viewed in Archive but not directly restored.
        </div>

        <form method="POST">
            <?php echo csrf_field(); ?>
            <div class="actions">
                <a href="index.php" class="btn-cancel"><i class="bi bi-arrow-left"></i> Cancel</a>
                <button type="submit" name="confirm_archive" class="btn-archive">Archive Record</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
