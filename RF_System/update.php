<?php
// ============================================================
// UPDATE.PHP — Add findings, remarks, update status
// ============================================================
session_start();
require_once 'config.php';
$pdo = getPDOConnection();

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$request = $pdo->prepare("SELECT * FROM requests WHERE id = ?");
$request->execute([$id]);
$r = $request->fetch(PDO::FETCH_ASSOC);
if (!$r) { header('Location: index.php'); exit; }

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status          = sanitizeInput($_POST['status'] ?? '');
    $finding         = sanitizeInput($_POST['finding'] ?? '');
    $remarks         = sanitizeInput($_POST['remarks'] ?? '');
    $accomplished_by = sanitizeInput($_POST['accomplished_by'] ?? '');
    $conforme        = sanitizeInput($_POST['conforme'] ?? '');
    $noted_by        = sanitizeInput($_POST['noted_by'] ?? DEFAULT_NOTED_BY);
    
    $pdo->prepare("UPDATE requests SET status=?, finding=?, remarks=?, accomplished_by=?, conforme=?, noted_by=? WHERE id=?")
        ->execute([$status, $finding, $remarks, $accomplished_by, $conforme, $noted_by, $id]);
    $message = 'Request updated successfully!';
    $request = $pdo->prepare("SELECT * FROM requests WHERE id = ?");
    $request->execute([$id]);
    $r = $request->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Request — <?= $r['request_no'] ?></title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', sans-serif;
    background: linear-gradient(135deg, #f5fef9 0%, #f0fef6 100%);
    color: #2c3e50;
    line-height: 1.6;
  }
  .navbar {
    background: linear-gradient(135deg, #049f4d 0%, #038a42 100%);
    color: white; padding: 0 28px;
    display: flex; align-items: center; justify-content: space-between; height: 72px;
    box-shadow: 0 8px 32px rgba(4, 159, 77, 0.25);
    border-bottom: 4px solid #fce704;
  }
  .navbar h1 { 
    font-size: 18px; display: flex; align-items: center; gap: 12px; font-weight: 700;
  }
  .navbar a {
    color: white; text-decoration: none; font-size: 13px; padding: 8px 18px;
    border: 1px solid rgba(255,255,255,.4); border-radius: 10px;
    display: inline-flex; align-items: center; gap: 6px; transition: all 0.3s ease;
    font-weight: 600;
  }
  .navbar a:hover { 
    background: rgba(255,255,255,.2); 
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
  }
  .container { max-width: 900px; margin: 40px auto; padding: 0 24px; }
  .card {
    background: white; border-radius: 16px; padding: 40px;
    box-shadow: 0 8px 32px rgba(0,0,0,.08);
    border: 1px solid rgba(40, 167, 69, 0.1);
    margin-bottom: 24px;
  }
  h3 { 
    color: #1a5c2a; margin-bottom: 20px; font-size: 16px; 
    font-weight: 700; letter-spacing: 0.5px;
  }
  .form-section {
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e8f0ed;
  }
  .form-section:last-child { border-bottom: none; }
  .form-section-title {
    font-size: 12px; font-weight: 700; 
    text-transform: uppercase; letter-spacing: 1px;
    color: #1a5c2a; margin-bottom: 16px;
    display: flex; align-items: center; gap: 8px;
  }
  .form-section-title::before {
    content: '';
    display: inline-block;
    width: 4px; height: 4px;
    background: #28a745;
    border-radius: 50%;
  }
  label {
    display: block; font-size: 12px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .6px; color: #2c3e50; margin-bottom: 8px;
  }
  input[type=text], select, textarea {
    width: 100%; padding: 12px 16px; border: 2px solid #d4edda;
    border-radius: 10px; font-size: 14px; font-family: inherit; 
    margin-bottom: 16px; background: #fafef9;
    transition: all 0.3s ease;
  }
  input::placeholder, textarea::placeholder {
    color: #999;
  }
  input:focus, select:focus, textarea:focus {
    outline: none; border-color: #28a745;
    box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.1);
    background: white;
  }
  textarea { resize: vertical; min-height: 100px; }
  .info-grid { 
    display: grid; grid-template-columns: 1fr 1fr; gap: 16px; 
    margin-bottom: 24px; margin-top: -8px;
  }
  .info-item { 
    background: linear-gradient(135deg, #f8fdf8 0%, #f0faf2 100%);
    border-radius: 10px; padding: 14px 18px; 
    border: 2px solid #d4edda;
  }
  .info-item .lbl { 
    font-size: 10px; font-weight: 700; text-transform: uppercase; 
    color: #666; letter-spacing: 0.5px;
  }
  .info-item .val { 
    font-size: 14px; font-weight: 600; color: #1a5c2a; 
    margin-top: 4px; word-break: break-word;
  }
  .btn {
    padding: 12px 28px; border: none; border-radius: 10px; cursor: pointer;
    font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;
    transition: all 0.3s ease;
  }
  .btn-primary { 
    background: linear-gradient(135deg, #28a745, #218838);
    color: white;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.35);
    margin-right: 12px;
  }
  .btn-primary:hover { 
    background: linear-gradient(135deg, #218838, #1a6b2f);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.45);
  }
  .btn-warning { 
    background: linear-gradient(135deg, #ffc107, #ffab00);
    color: #1a1a1a;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.35);
  }
  .btn-warning:hover { 
    background: linear-gradient(135deg, #ffb300, #ffa500);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 193, 7, 0.45);
  }
  .btn-secondary { 
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white; text-decoration: none; 
    display: inline-flex; align-items: center; gap: 8px;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.35);
  }
  .btn-secondary:hover { 
    background: linear-gradient(135deg, #5a6268, #495057);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.45);
  }
  .btn:active { transform: translateY(0); }
  .alert {
    padding: 14px 18px; border-radius: 12px; 
    background: linear-gradient(135deg, #d4edda 0%, #e8f5e9 100%);
    border: 2px solid #28a745;
    color: #1a5c2a; margin-bottom: 24px; font-size: 14px;
    display: flex; align-items: center; gap: 12px;
    border-left: 4px solid #28a745;
    animation: slideIn 0.3s ease;
  }
  .icon { display: inline-block; vertical-align: middle; flex-shrink: 0; }
  
  @keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  @media (max-width: 768px) {
    .navbar { padding: 0 16px; height: 64px; }
    .navbar h1 { font-size: 16px; }
    .container { padding: 0 16px; margin: 24px auto; max-width: 100%; }
    .card { padding: 24px; }
    .info-grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>
<div class="navbar">
  <h1>
    <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
    </svg>
    Update Request — <?= $r['request_no'] ?>
  </h1>
  <a href="index.php">
    <svg class="icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
      <polyline points="15 18 9 12 15 6"/>
    </svg>
    Back to Dashboard
  </a>
</div>
<div class="container">

<?php if ($message): ?>
<div class="alert">
  <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1a5c2a" stroke-width="2.5">
    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
  </svg>
  <?= $message ?> <a href="index.php" style="color:#1a5c2a;font-weight:700;margin-left:6px;">Back to list →</a>
</div>
<?php endif; ?>

<div class="card">
  <h3>
    <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1a5c2a" stroke-width="2.5" style="margin-right:6px;">
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    Request Details
  </h3>
  <div class="info-grid">
    <div class="info-item"><div class="lbl">Request No.</div><div class="val"><?= $r['request_no'] ?></div></div>
    <div class="info-item"><div class="lbl">Date</div><div class="val"><?= $r['date'] ?></div></div>
    <div class="info-item"><div class="lbl">Department</div><div class="val"><?= htmlspecialchars($r['department']) ?></div></div>
    <div class="info-item"><div class="lbl">Requested By</div><div class="val"><?= htmlspecialchars($r['requested_by']) ?></div></div>
    <div class="info-item" style="grid-column:span 2"><div class="lbl">Purpose</div><div class="val"><?= htmlspecialchars($r['purpose']) ?></div></div>
  </div>
</div>

<div class="card">
  <h3>
    <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1a5c2a" stroke-width="2.5" style="margin-right:6px;">
      <polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
    </svg>
    Update Status &amp; Findings
  </h3>
  <form method="POST">
    <!-- STATUS SECTION -->
    <div class="form-section">
      <h4 class="form-section-title">Update Status</h4>
      <label>Current Status</label>
      <select name="status">
        <option value="Pending"     <?= $r['status']==='Pending'?'selected':'' ?>>Pending</option>
        <option value="In Progress" <?= $r['status']==='In Progress'?'selected':'' ?>>In Progress</option>
        <option value="Done"        <?= $r['status']==='Done'?'selected':'' ?>>Done</option>
      </select>
    </div>

    <!-- FINDINGS SECTION -->
    <div class="form-section">
      <h4 class="form-section-title">Findings & Recommendations</h4>
      <label>Finding / Result</label>
      <textarea name="finding" rows="4" placeholder="Describe findings..."><?= htmlspecialchars($r['finding'] ?? '') ?></textarea>

      <label>Recommendations / Accomplishment Report</label>
      <textarea name="remarks" rows="4" placeholder="Enter recommendations or accomplishment report..."><?= htmlspecialchars($r['remarks'] ?? '') ?></textarea>
    </div>

    <!-- PERSONNEL SECTION -->
    <div class="form-section">
      <h4 class="form-section-title">Personnel & Approvals</h4>
      <label>Checked / Accomplished By (Corplan Personnel)</label>
      <input type="text" name="accomplished_by" value="<?= htmlspecialchars($r['accomplished_by'] ?? '') ?>" placeholder="Name">

      <label>Conforme (Name &amp; Signature)</label>
      <input type="text" name="conforme" value="<?= htmlspecialchars($r['conforme'] ?? '') ?>" placeholder="Name">

      <label>Noted By</label>
      <input type="text" name="noted_by" value="<?= htmlspecialchars($r['noted_by'] ?? 'Engr. Dean Mark Hernandez') ?>" placeholder="Name">
    </div>

    <div style="display: flex; gap: 14px; margin-top: 28px;">
      <button type="submit" class="btn btn-primary">
        <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
          <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
        </svg>
        Save Changes
      </button>
      <a href="index.php" class="btn btn-secondary">
        <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
        Back to List
      </a>
    </div>
  </form>
</div>
</div>
</body>
</html>