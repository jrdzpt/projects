<?php
session_start();
date_default_timezone_set('Asia/Manila');
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
include('db.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: index.php"); exit(); }

$stmt = $conn->prepare("SELECT * FROM consumers WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { echo "Consumer not found."; exit(); }

$statusLabel = getStatusLabel((int)$row['status']);
$generatedAt = date('F d, Y · g:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PELCO III – Report: <?php echo sanitize($row['accountnumber']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Toolbar (no-print) -->
<div class="toolbar no-print">
    <span class="title">Consumer Report: <?php echo sanitize($row['accountnumber']); ?></span>
    <a href="index.php" class="btn-t btn-back"><i class="bi bi-arrow-left"></i> Dashboard</a>
    <button onclick="window.print()" class="btn-t btn-print"><i class="bi bi-printer-fill"></i> Print</button>
    <button onclick="exportAs('pdf')" class="btn-t btn-pdf"><i class="bi bi-file-earmark-pdf-fill"></i> PDF</button>
    <button onclick="exportAs('png')" class="btn-t btn-png"><i class="bi bi-file-earmark-image-fill"></i> PNG</button>
</div>

<div class="report-wrap">
<div class="report" id="printableReport">

    <!-- Header -->
    <div class="report-header">
        <img src="https://www.pelco3.org/images/logo.png" alt="PELCO III" class="logo">
        <div class="org-info">
            <h1>PELCO III</h1>
            <p>Pampanga III Electric Cooperative, Inc. · Apalit, Pampanga</p>
        </div>
        <div class="doc-title">
            <div>Official Document</div>
            <h3>Consumer Information<br>Report</h3>
        </div>
    </div>

    <!-- Account strip -->
    <div class="acct-strip">
        <div>
            <div style="font-size:.65rem;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:1px;">Account Number</div>
            <div class="num"><?php echo sanitize($row['accountnumber']); ?></div>
        </div>
        <span class="status-badge status-<?php echo (int)$row['status']; ?>">
            <?php echo $statusLabel; ?>
        </span>
    </div>

    <!-- Content -->
    <div class="content-grid">
        <!-- Photo -->
        <div class="photo-col">
            <img src="<?php echo sanitize($row['photo']); ?>"
                 onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($row['first_name'].'+'.$row['last_name']); ?>&background=049f4d&color=fff&size=200'"
                 alt="Profile Photo">
            <div class="acct-label">Consumer<br>Profile Photo</div>
        </div>

        <!-- Data -->
        <div class="data-col">
            <div class="section-head">Personal Information</div>
            <div class="data-grid">
                <div class="data-item">
                    <div class="lbl">Full Name</div>
                    <div class="val" style="text-transform:uppercase;"><?php echo sanitize($row['first_name'].' '.$row['middle_name'].' '.$row['last_name']); ?></div>
                </div>
                <div class="data-item">
                    <div class="lbl">Gender</div>
                    <div class="val"><?php echo sanitize($row['gender']); ?></div>
                </div>
                <div class="data-item">
                    <div class="lbl">Date of Birth</div>
                    <div class="val"><?php echo $row['birthdate'] ? date('F d, Y', strtotime($row['birthdate'])) : '—'; ?></div>
                </div>
                <div class="data-item">
                    <div class="lbl">Contact No.</div>
                    <div class="val"><?php echo sanitize($row['contact_number'] ?: '—'); ?></div>
                </div>
                <div class="data-item" style="grid-column:1/-1;">
                    <div class="lbl">Email</div>
                    <div class="val"><?php echo sanitize($row['email'] ?: '—'); ?></div>
                </div>
                <div class="data-item" style="grid-column:1/-1;">
                    <div class="lbl">Permanent Address</div>
                    <div class="val"><?php echo sanitize($row['address'] ?: '—'); ?></div>
                </div>
            </div>

            <div class="section-head">Meter & Equipment</div>
            <div class="data-grid">
                <div class="data-item">
                    <div class="lbl">Serial Number</div>
                    <div class="val" style="font-family:'JetBrains Mono',monospace;"><?php echo sanitize($row['SerialNumber'] ?: '—'); ?></div>
                </div>
                <div class="data-item">
                    <div class="lbl">Brand</div>
                    <div class="val"><?php echo sanitize($row['MeterBrand'] ?: '—'); ?></div>
                </div>
            </div>

            <div class="section-head">Usage Summary</div>
            <div class="readings-box">
                <div class="reading-cell">
                    <div class="r-label">Previous Reading</div>
                    <div class="r-value" style="color:#b91c1c;"><?php echo number_format($row['previous_reading'],2); ?></div>
                    <div class="r-unit">kWh</div>
                </div>
                <div class="reading-cell">
                    <div class="r-label">Present Reading</div>
                    <div class="r-value" style="color:#1a1f16;"><?php echo number_format($row['present_reading'],2); ?></div>
                    <div class="r-unit">kWh</div>
                </div>
                <div class="reading-cell" style="background:#e8f7ee;">
                    <div class="r-label" style="color:var(--g-dark);">Total Consumption</div>
                    <div class="r-value" style="color:var(--g-dark);"><?php echo number_format($row['kilowatthour'],2); ?></div>
                    <div class="r-unit" style="color:var(--g-dark);font-weight:700;">kWh</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signature -->
    <div class="signature-section">
        <div class="sig-block">
            <div class="sig-line">
                <div class="sig-name"><?php echo strtoupper(sanitize($_SESSION['admin'])); ?></div>
                <div class="sig-role">Authorized Administrative Personnel (PELCO III)</div>
            </div>
        </div>
        <div class="generated-info">
            <div class="ts">Document generated on:</div>
            <div class="ts" style="font-weight:700;color:#1a1f16;"><?php echo $generatedAt; ?></div>
            <div class="ts" style="margin-top:4px;">PELCO III CMS v2.0</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="report-footer">
        <p>*** This is a computer-generated document from the PELCO III Consumer Management System. Any alteration renders this document invalid. ***</p>
    </div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
async function exportAs(fmt) {
    const el  = document.getElementById('printableReport');
    const canvas = await html2canvas(el, { scale:3, useCORS:true, backgroundColor:'#ffffff' });
    const imgData = canvas.toDataURL('image/png', 1.0);
    const fname = 'PELCO3_<?php echo $row['accountnumber']; ?>_Report';

    if (fmt === 'pdf') {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p','mm','a4');
        const w   = pdf.internal.pageSize.getWidth();
        const h   = (canvas.height * w) / canvas.width;
        pdf.addImage(imgData, 'PNG', 0, 0, w, h);
        pdf.save(fname + '.pdf');
    } else {
        const a = document.createElement('a');
        a.download = fname + '.' + fmt;
        a.href = fmt === 'jpeg' ? canvas.toDataURL('image/jpeg',0.95) : imgData;
        a.click();
    }
}
</script>
</body>
</html>
