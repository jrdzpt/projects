<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PELCO III – Daily Summary Report</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Helvetica Neue',Arial,sans-serif;font-size:10px;color:#1a1a1a;background:#fff;padding:10mm}

.header{display:flex;align-items:center;gap:14px;padding-bottom:12px;border-bottom:2.5px solid #037d3c;margin-bottom:16px}
.header img{width:48px;height:48px;object-fit:contain;flex-shrink:0}
.header-text{flex:1}
.header-org{font-size:18px;font-weight:700;color:#037d3c;line-height:1.1}
.header-sub{font-size:9px;color:#888;margin-top:3px}
.header-meta{text-align:right;font-size:9px;color:#999;line-height:1.7}
.header-meta strong{color:#1a1a1a;font-size:13px;display:block}

.report-title{font-size:13px;font-weight:700;color:#037d3c;margin-bottom:14px;text-align:center;letter-spacing:.3px}
.report-date{font-size:11px;color:#555;text-align:center;margin-bottom:18px}

.summary-box{display:flex;gap:14px;margin-bottom:22px;justify-content:center}
.stat-card{background:#f7faf8;border:1px solid #d1e9d8;border-radius:8px;padding:12px 22px;text-align:center;min-width:120px}
.stat-num{font-size:26px;font-weight:800;color:#037d3c;line-height:1}
.stat-lbl{font-size:9px;color:#888;text-transform:uppercase;letter-spacing:.5px;margin-top:4px}

.grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.section-title{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#666;margin-bottom:8px;padding-bottom:5px;border-bottom:1px solid #e5e5e5}

table{width:100%;border-collapse:collapse;font-size:9.5px}
thead th{background:#037d3c;color:#fff;padding:5px 8px;text-align:left;font-size:8px;font-weight:600;text-transform:uppercase;letter-spacing:.4px;-webkit-print-color-adjust:exact;print-color-adjust:exact}
thead th.right{text-align:right}
tbody td{padding:5px 8px;border-bottom:1px solid #f0f0f0;vertical-align:middle}
tbody td.right{text-align:right;font-weight:700;color:#037d3c}
tbody tr:nth-child(even) td{background:#f9fbf9;-webkit-print-color-adjust:exact;print-color-adjust:exact}
tbody tr:last-child td{border-bottom:none;font-weight:700;background:#f0faf4!important;color:#037d3c}

.bar-cell{display:flex;align-items:center;gap:6px}
.bar-bg{flex:1;height:5px;background:#e5e5e5;border-radius:3px}
.bar-fill{height:5px;background:#037d3c;border-radius:3px;-webkit-print-color-adjust:exact;print-color-adjust:exact}

.footer{margin-top:18px;padding-top:8px;border-top:1px solid #e8e8e8;display:flex;justify-content:space-between;font-size:8px;color:#bbb}
.no-data{text-align:center;padding:24px;color:#999;font-size:11px}

/* Print button — hidden when printing */
.print-btn{position:fixed;bottom:24px;right:24px;padding:11px 24px;background:#037d3c;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;box-shadow:0 4px 16px rgba(3,125,60,.3);display:flex;align-items:center;gap:8px;transition:.15s}
.print-btn:hover{background:#025a2b}
@media print{
  @page{size:A4;margin:8mm}
  body{padding:0}
  .print-btn{display:none!important}
}
</style>
</head>
<body>
<?php
session_set_cookie_params([
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
require_once __DIR__ . '/includes/config.php';
if (empty($_SESSION['css_user'])) { header('Location: login.php'); exit; }

$db   = getDB();
$date = $_GET['date'] ?? date('Y-m-d');
$safeDate = $db->real_escape_string($date);

// ── Data ────────────────────────────────────────────────────────────
$total = (int)$db->query("SELECT COUNT(*) AS c FROM cs_records WHERE date_forwarded='$safeDate'")->fetch_assoc()['c'];

$byConcern = [];
$res = $db->query("SELECT concern, COUNT(*) AS cnt FROM cs_records WHERE date_forwarded='$safeDate' GROUP BY concern ORDER BY cnt DESC");
while ($r = $res->fetch_assoc()) $byConcern[] = $r;

$byArea = [];
$res = $db->query("SELECT area_dept, COUNT(*) AS cnt FROM cs_records WHERE date_forwarded='$safeDate' GROUP BY area_dept ORDER BY cnt DESC");
while ($r = $res->fetch_assoc()) $byArea[] = $r;

// Full records for the day
$records = [];
$res = $db->query("SELECT * FROM cs_records WHERE date_forwarded='$safeDate' ORDER BY created_at ASC");
while ($r = $res->fetch_assoc()) $records[] = $r;

$maxC = $byConcern ? max(array_column($byConcern, 'cnt')) : 1;
$maxA = $byArea    ? max(array_column($byArea,    'cnt')) : 1;

$dateFormatted = date('F d, Y', strtotime($date));
$generatedBy   = htmlspecialchars($_SESSION['css_fullname'] ?? $_SESSION['css_user']);
$generatedAt   = date('F d, Y  H:i');
$logoUrl = 'https://www.pelco3.org/images/logo.png';

function h($s){ return htmlspecialchars($s ?? ''); }
function fmtD($s){ return $s ? date('M d, Y', strtotime($s)) : '—'; }
?>

<div class="header">
  <img src="<?= $logoUrl ?>" alt="PELCO III" onerror="this.style.display='none'">
  <div class="header-text">
    <div class="header-org">PELCO III</div>
    <div class="header-sub">Pampanga Electric Cooperative III &nbsp;&middot;&nbsp; Customer Service System</div>
  </div>
  <div class="header-meta">
    <strong><?= $total ?> record(s)</strong>
    Generated: <?= $generatedAt ?><br>
    By: <?= $generatedBy ?>
  </div>
</div>

<div class="report-title">Daily Summary Report</div>
<div class="report-date">Date: <?= $dateFormatted ?></div>

<!-- Quick stats -->
<div class="summary-box">
  <div class="stat-card">
    <div class="stat-num"><?= $total ?></div>
    <div class="stat-lbl">Total Records</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= count($byConcern) ?></div>
    <div class="stat-lbl">Concern Types</div>
  </div>
  <div class="stat-card">
    <div class="stat-num"><?= count($byArea) ?></div>
    <div class="stat-lbl">Areas / Depts</div>
  </div>
</div>

<?php if ($total > 0): ?>

<!-- Breakdown grid -->
<div class="grid">

  <!-- By Concern -->
  <div>
    <div class="section-title">By Concern</div>
    <?php if ($byConcern): ?>
    <table>
      <thead><tr><th>Concern</th><th>Volume</th><th class="right">Count</th></tr></thead>
      <tbody>
        <?php foreach ($byConcern as $row):
          $pct = round($row['cnt'] / $maxC * 100); ?>
        <tr>
          <td><?= h($row['concern']) ?: '<em style="color:#bbb">—</em>' ?></td>
          <td><div class="bar-cell"><div class="bar-bg"><div class="bar-fill" style="width:<?= $pct ?>%"></div></div></div></td>
          <td class="right"><?= $row['cnt'] ?></td>
        </tr>
        <?php endforeach; ?>
        <tr><td><strong>Total</strong></td><td></td><td class="right"><?= $total ?></td></tr>
      </tbody>
    </table>
    <?php else: ?>
    <div class="no-data">No data.</div>
    <?php endif; ?>
  </div>

  <!-- By Area / Dept -->
  <div>
    <div class="section-title">By Area / Department</div>
    <?php if ($byArea): ?>
    <table>
      <thead><tr><th>Area / Dept</th><th>Volume</th><th class="right">Count</th></tr></thead>
      <tbody>
        <?php foreach ($byArea as $row):
          $pct = round($row['cnt'] / $maxA * 100); ?>
        <tr>
          <td><?= h($row['area_dept']) ?: '<em style="color:#bbb">—</em>' ?></td>
          <td><div class="bar-cell"><div class="bar-bg"><div class="bar-fill" style="width:<?= $pct ?>%"></div></div></div></td>
          <td class="right"><?= $row['cnt'] ?></td>
        </tr>
        <?php endforeach; ?>
        <tr><td><strong>Total</strong></td><td></td><td class="right"><?= $total ?></td></tr>
      </tbody>
    </table>
    <?php else: ?>
    <div class="no-data">No data.</div>
    <?php endif; ?>
  </div>

</div>

<!-- Full Records Table -->
<div style="margin-top:22px">
  <div class="section-title">All Records for <?= $dateFormatted ?></div>
  <table style="font-size:8.5px">
    <thead>
      <tr>
        <th>#</th>
        <th>Ref No</th>
        <th>Account No</th>
        <th>Account Name</th>
        <th>Contact No</th>
        <th>Messenger / Caller</th>
        <th>Concern</th>
        <th>Area / Dept</th>
        <th>Notes</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($records as $i => $r): ?>
      <tr>
        <td style="color:#bbb"><?= $i+1 ?></td>
        <td style="font-family:monospace;font-weight:600;color:#037d3c"><?= h($r['reference_no']) ?></td>
        <td><?= h($r['account_number']) ?></td>
        <td><?= h($r['account_name']) ?></td>
        <td><?= h($r['contact_no']) ?></td>
        <td><?= h($r['messenger_caller']) ?></td>
        <td><?= h($r['concern']) ?></td>
        <td><?= h($r['area_dept']) ?></td>
        <td><?= h($r['notes']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php else: ?>
<div class="no-data" style="padding:40px;font-size:13px">No records found for <?= $dateFormatted ?>.</div>
<?php endif; ?>

<div class="footer">
  <span>PELCO III &mdash; Customer Service System</span>
  <span>Confidential &middot; For Internal Use Only</span>
</div>

<button class="print-btn" onclick="window.print()">
  <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:#fff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
  Print / Save as PDF
</button>

</body>
</html>
