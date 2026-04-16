<?php
// ============================================================
// REQUEST FORM SYSTEM - index.php
// ============================================================
session_start();
require_once 'config.php';

$message = '';
$error = '';
$pdo = getPDOConnection();

// Convert logo to base64 so html2canvas can render it without CORS issues
$logoBase64 = '';
try {
    $logoData = @file_get_contents(LOGO_URL);
    if ($logoData !== false) {
        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }
} catch (Exception $e) { /* silently fail — logo just won't show */ }

// Generate CSRF token
$csrfToken = generateCSRFToken();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security token validation failed. Please try again.';
    } else {
        // Sanitize and validate inputs
        $date       = sanitizeInput($_POST['date'] ?? '');
        $department = sanitizeInput($_POST['department'] ?? '');
        $database   = sanitizeInput($_POST['database'] ?? '');
        $hardware   = sanitizeInput($_POST['hardware'] ?? '');
        $software   = sanitizeInput($_POST['software'] ?? '');
        $cctv       = sanitizeInput($_POST['cctv'] ?? '');
        $others     = sanitizeInput($_POST['others'] ?? '');
        $purpose    = sanitizeInput($_POST['purpose'] ?? '');
        $requested_by    = sanitizeInput($_POST['requested_by'] ?? '');
        $request_date    = sanitizeInput($_POST['request_date'] ?? '');
        $finding         = sanitizeInput($_POST['finding'] ?? '');
        $remarks         = sanitizeInput($_POST['remarks'] ?? '');
        $accomplished_by = sanitizeInput($_POST['accomplished_by'] ?? '');
        $conforme        = sanitizeInput($_POST['conforme'] ?? '');
        $noted_by        = sanitizeInput($_POST['noted_by'] ?? DEFAULT_NOTED_BY);

        if (!validateRequired([$department, $purpose, $requested_by])) {
            $error = 'Please fill in all required fields (Department, Purpose, Requested By).';
        } else {
            $req_no = generateRequestNo($pdo);
            $stmt = $pdo->prepare("INSERT INTO requests 
                (request_no, date, department, issue_database, issue_hardware, issue_software, issue_cctv, issue_others, purpose, requested_by, request_date, status, finding, remarks, accomplished_by, conforme, noted_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?, ?, ?, ?)");
            $stmt->execute([$req_no, $date, $department, $database, $hardware, $software, $cctv, $others, $purpose, $requested_by, $request_date, $finding, $remarks, $accomplished_by, $conforme, $noted_by]);
            $message = "Request submitted successfully! Request No: <strong>$req_no</strong>";
        }
    }
}

// Fetch all requests
$requests = $pdo->query("SELECT * FROM requests ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PELCO 3 — IT Request Form System</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', sans-serif;
    background: linear-gradient(135deg, #f5fef9 0%, #f0fef6 100%);
    color: #2c3e50;
    line-height: 1.6;
  }

  /* NAV */
  .navbar {
    background: linear-gradient(135deg, #049f4d 0%, #038a42 100%);
    color: white; padding: 0 28px;
    display: flex; align-items: center; justify-content: space-between;
    height: 72px;
    border-bottom: 4px solid #fce704;
    box-shadow: 0 8px 32px rgba(4, 159, 77, 0.25);
    position: sticky; top: 0; z-index: 100;
    backdrop-filter: blur(10px);
  }
  .navbar-brand { display: flex; align-items: center; gap: 16px; }
  .navbar-brand img { height: 40px; width: auto; filter: drop-shadow(0 2px 6px rgba(0,0,0,.2)); transition: transform 0.3s ease; }
  .navbar-brand img:hover { transform: scale(1.05); }
  .navbar-brand .brand-text h1 { font-size: 18px; font-weight: 700; letter-spacing: .5px; line-height: 1.2; color: white; }
  .navbar-brand .brand-text span { font-size: 11px; opacity: .8; letter-spacing: .3px; font-weight: 500; }

  /* RIGHT SIDE NAV */
  .nav-right { display: flex; align-items: center; gap: 14px; }

  /* DATE/TIME BLOCK */
  .nav-datetime {
    display: flex; align-items: center; gap: 12px;
    background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.25);
    border-radius: 12px;
    padding: 8px 18px;
    backdrop-filter: blur(10px);
  }
  .nav-tb-divider {
    width: 1px; height: 32px;
    background: rgba(255,255,255,.2);
  }
  .nav-time-block { display: flex; flex-direction: column; align-items: center; gap: 2px; }
  .nav-date-block { display: flex; flex-direction: column; align-items: center; gap: 2px; }
  .nav-time-val {
    font-family: 'JetBrains Mono', 'Courier New', monospace;
    font-size: 15px; font-weight: 700; color: #fff;
    letter-spacing: 1.2px; line-height: 1;
  }
  .nav-time-lbl {
    font-size: 8px; font-weight: 700; color: rgba(255,255,255,.6);
    text-transform: uppercase; letter-spacing: .8px;
  }
  .nav-date-day {
    font-size: 16px; font-weight: 700; color: #fff;
    line-height: 1; letter-spacing: .2px;
  }
  .nav-date-full {
    font-size: 8px; font-weight: 600; color: rgba(255,255,255,.65);
    text-transform: uppercase; letter-spacing: .6px; white-space: nowrap;
  }

  .nav-new-btn {
    background: linear-gradient(135deg, #fce704, #fdd835);
    color: #1a1a1a;
    border: none; border-radius: 10px; cursor: pointer;
    padding: 10px 20px; height: 40px;
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 700;
    transition: all 0.3s ease; text-decoration: none; white-space: nowrap;
    box-shadow: 0 4px 15px rgba(252, 231, 4, 0.35);
  }
  .nav-new-btn:hover { 
    background: linear-gradient(135deg, #fdd835, #fbc02d);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(252, 231, 4, 0.45);
  }
  .nav-new-btn:active { transform: translateY(0); }

  /* LAYOUT */
  .container { max-width: 1200px; margin: 40px auto; padding: 0 24px; }
  .tabs { display: flex; gap: 8px; margin-bottom: 0px; flex-wrap: wrap; }
  .tab-btn {
    padding: 12px 28px; border: none; border-radius: 10px 10px 0 0;
    cursor: pointer; font-size: 14px; font-weight: 600;
    background: rgba(212, 237, 218, 0.5); color: #555; transition: all 0.3s ease;
    display: inline-flex; align-items: center; gap: 8px;
    border-bottom: 3px solid transparent;
  }
  .tab-btn:hover { background: rgba(212, 237, 218, 0.8); transform: translateY(-2px); }
  .tab-btn.active { 
    background: white; color: #28a745; 
    box-shadow: 0 -4px 12px rgba(40, 167, 69, 0.15);
    border-bottom: 3px solid #28a745;
  }
  .tab-panel { display: none; opacity: 0; transition: opacity 0.3s ease; }
  .tab-panel.active { display: block; opacity: 1; }

  /* CARD */
  .card {
    background: white; border-radius: 16px;
    padding: 40px; box-shadow: 0 8px 32px rgba(0,0,0,.08);
    border: 1px solid rgba(40, 167, 69, 0.1);
  }

  .form-header {
    text-align: center; border-bottom: 3px solid #28a745;
    padding-bottom: 20px; margin-bottom: 32px;
    display: flex; align-items: center; justify-content: center; gap: 24px;
  }
  .form-header img { height: 72px; width: auto; }
  .form-header-text h2 {
    font-size: 24px; letter-spacing: 2px; color: #1a5c2a;
    text-transform: uppercase; margin-bottom: 6px; font-weight: 700;
  }
  .form-header-text p { font-size: 13px; color: #666; font-weight: 500; }

  .form-section {
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 1px solid #e8f0ed;
  }
  .form-section:last-child { border-bottom: none; }
  .form-section-title {
    font-size: 13px; font-weight: 700; 
    text-transform: uppercase; letter-spacing: 1px;
    color: #1a5c2a; margin-bottom: 18px;
    display: flex; align-items: center; gap: 10px;
  }
  .form-section-title::before {
    content: '';
    display: inline-block;
    width: 4px; height: 4px;
    background: #28a745;
    border-radius: 50%;
  }

  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 20px; }
  .form-row.full { grid-template-columns: 1fr; }
  .form-group { margin-bottom: 0px; }
  label { 
    display: block; font-size: 12px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .6px; color: #2c3e50; margin-bottom: 8px; 
  }
  label .req { color: #dc2626; font-weight: 700; }
  input[type=text], input[type=date], textarea, select {
    width: 100%; padding: 12px 16px; border: 2px solid #d4edda;
    border-radius: 10px; font-size: 14px; transition: all 0.3s ease;
    font-family: inherit; background: #fafef9;
  }
  input::placeholder, textarea::placeholder {
    color: #999;
  }
  input:focus, textarea:focus, select:focus {
    outline: none; border-color: #28a745;
    box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.1);
    background: white;
  }
  textarea { resize: vertical; min-height: 100px; }

  .issue-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
    background: linear-gradient(135deg, #f8fdf8 0%, #f0faf2 100%); 
    border: 2px solid #d4edda;
    border-radius: 12px; padding: 20px; margin-bottom: 0px;
  }
  .issue-label {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .6px; color: #1a5c2a; margin-bottom: 8px;
    display: flex; align-items: center; gap: 6px;
  }
  .issue-label::before {
    content: '';
    display: inline-block;
    width: 3px; height: 3px;
    background: #28a745;
    border-radius: 50%;
  }

  .sig-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin: 32px 0 0; padding-top: 24px; border-top: 1px solid #e8f0ed; }
  .sig-box { text-align: center; }
  .sig-line { border-top: 2px solid #333; margin: 48px 0 8px; }
  .sig-name { font-weight: 700; font-size: 13px; text-decoration: underline; }
  .sig-title { font-size: 11px; color: #666; margin-top: 4px; }

  /* ALERTS */
  .alert { 
    padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; 
    font-size: 14px; border-left: 4px solid;
    display: flex; align-items: flex-start; gap: 12px;
    animation: slideIn 0.3s ease;
  }
  .alert-success { 
    background: linear-gradient(135deg, #d4edda 0%, #e8f5e9 100%); 
    border: 2px solid #28a745; 
    color: #1a5c2a;
    border-left: 4px solid #28a745;
  }
  .alert-error { 
    background: linear-gradient(135deg, #fef2f2 0%, #ffebee 100%);
    border: 2px solid #dc2626; 
    color: #991b1b;
    border-left: 4px solid #dc2626;
  }

  /* BUTTONS */
  .btn {
    padding: 12px 28px; border: none; border-radius: 10px;
    cursor: pointer; font-size: 14px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease;
  }
  .btn-primary { 
    background: linear-gradient(135deg, #28a745, #218838);
    color: white;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.35);
  }
  .btn-primary:hover { 
    background: linear-gradient(135deg, #218838, #1a6b2f);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.45);
  }
  .btn-primary:active { transform: translateY(0); }
  .btn-warning { 
    background: linear-gradient(135deg, #ffc107, #ffb300);
    color: #1a1a1a;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.35);
  }
  .btn-warning:hover { 
    background: linear-gradient(135deg, #ffb300, #ffa500);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 193, 7, 0.45);
  }
  .btn-warning:active { transform: translateY(0); }
  .btn-success { 
    background: linear-gradient(135deg, #28a745, #218838);
    color: white;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.35);
  }
  .btn-success:hover { 
    background: linear-gradient(135deg, #218838, #1a6b2f);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.45);
  }
  .btn-danger  { 
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    box-shadow: 0 4px 15px rgba(220, 38, 38, 0.35);
  }
  .btn-danger:hover  { 
    background: linear-gradient(135deg, #b91c1c, #991b1b);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.45);
  }
  .btn-secondary { 
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.35);
  }
  .btn-secondary:hover { 
    background: linear-gradient(135deg, #5a6268, #495057);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.45);
  }
  .btn-sm { padding: 8px 16px; font-size: 12px; }

  .btn-group { display: flex; gap: 14px; margin-top: 28px; flex-wrap: wrap; }

  /* TABLE */
  .table-wrap { overflow-x: auto; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.08); }
  table { width: 100%; border-collapse: collapse; font-size: 14px; }
  th { 
    background: linear-gradient(135deg, #28a745, #218838);
    color: white; padding: 14px 16px; text-align: left; font-size: 12px; 
    font-weight: 700; letter-spacing: 0.5px;
  }
  td { padding: 12px 16px; border-bottom: 1px solid #e8f0ed; vertical-align: middle; }
  tr:hover td { background: #f8fdf8; }
  tr:last-child td { border-bottom: none; }
  .badge {
    display: inline-block; padding: 4px 12px; border-radius: 20px;
    font-size: 12px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.5px;
  }
  .badge-pending  { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
  .badge-done     { background: #d4edda; color: #1a5c2a; border: 1px solid #28a745; }
  .badge-progress { background: #d1ecf1; color: #0c5460; border: 1px solid #17a2b8; }

  /* SECTION HEADING */
  h3 { color: #1a5c2a; font-size: 16px; font-weight: 700; letter-spacing: 0.5px; }

  @keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* ===== PRINT FORM ===== */
  #printForm {
    background: white;
    width: 794px;
    margin: 20px auto;
    padding: 30px 42px 42px;
    color: #000;
    box-shadow: 0 0 20px rgba(0,0,0,.15);
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10pt;
  }

  .lh { display: flex; align-items: center; gap: 14px; padding-bottom: 8px;
        border-bottom: 2.5px solid #28a745; margin-bottom: 0; }
  .lh img { width: 76px; height: 76px; object-fit: contain; flex-shrink: 0; }
  .lh-text { flex: 1; text-align: center; margin-right: 76px; }
  .lh-org { font-size: 13pt; font-weight: 700; line-height: 1.2; color: #000; margin-bottom: 3px; }
  .lh-addr { font-size: 8pt; color: #000; line-height: 1.6; }

  .pf-top-right { display: flex; flex-direction: column; align-items: flex-end; margin: 7px 0 4px; gap: 4px; }
  .pf-top-right .pf-row { display: flex; align-items: center; gap: 4px; font-size: 9pt; }
  .pf-top-right .pf-row .pf-uval {
    border-bottom: 1px solid #000; min-width: 110px; display: inline-block;
    min-height: 14px; padding: 0 3px; font-size: 9pt;
    text-align: center; vertical-align: middle;
  }

  .pf-title { text-align: center; font-size: 13pt; font-weight: 700; margin: 8px 0 10px; }

  .pf-dept-row { display: flex; align-items: center; gap: 4px; font-size: 10pt; margin-bottom: 10px; }
  .pf-dept-row .pf-dept-lbl { min-width: 88px; }
  .pf-dept-row .pf-colon { margin: 0 4px; }
  .pf-dept-row .pf-dline { border-bottom: 1px solid #000; min-width: 160px; min-height: 14px; padding: 0 3px; display: inline-block; text-align: center; vertical-align: middle; }

  .pf-issue-block { display: flex; gap: 0; font-size: 10pt; margin-bottom: 10px; }
  .pf-issue-lbl-col { min-width: 115px; padding-top: 1px; white-space: nowrap; flex-shrink: 0; }
  .pf-issue-rows { flex: 1; display: flex; flex-direction: column; gap: 5px; }
  .pf-irow { display: flex; align-items: center; gap: 0; }
  .pf-cb {
    width: 13px; height: 13px; border: 1px solid #000; display: inline-flex;
    align-items: center; justify-content: center; flex-shrink: 0; font-size: 10pt;
    line-height: 1; margin-right: 6px; background: white;
  }
  .pf-iname { width: 100px; white-space: nowrap; flex-shrink: 0; }
  .pf-icolon { flex-shrink: 0; margin-right: 4px; }
  .pf-ival { flex: 1; border-bottom: 1px solid #000; min-height: 14px; padding: 0 3px; text-align: center; vertical-align: middle; }

  .pf-purpose-row { display: flex; align-items: flex-start; gap: 0; font-size: 10pt; margin-bottom: 12px; }
  .pf-purpose-row .pf-plbl { width: 76px; white-space: nowrap; padding-top: 1px; flex-shrink: 0; }
  .pf-purpose-row .pf-pcolon { flex-shrink: 0; margin-right: 4px; }
  .pf-purpose-row .pf-pval { flex: 1; border-bottom: 1px solid #000; min-height: 14px; padding: 0 3px; text-align: center; vertical-align: middle; }

  .pf-midsig { display: flex; align-items: flex-start; margin-bottom: 4px; font-size: 9pt; }
  .pf-midsig .mc1 { flex: 1; }
  .pf-midsig .mc2 { flex: 1.1; text-align: center; }
  .pf-midsig .mc3 { flex: 1; text-align: right; }
  .pf-midsig .mc-head { margin-bottom: 20px; }
  .pf-midsig .mc-sigline { border-bottom: 1px solid #000; display: inline-block; min-width: 140px; min-height: 14px; text-align: center; vertical-align: middle; }
  .pf-midsig .mc-named { font-weight: 700; text-decoration: underline; display: block; font-size: 9pt; }
  .pf-midsig .mc-role { font-size: 8pt; color: #333; margin-top: 2px; }
  .pf-midsig .mc-date { font-size: 9pt; margin-top: 6px; }
  .pf-midsig .mc-date .pf-uval { border-bottom: 1px solid #000; min-width: 100px; display: inline-block; text-align: center; vertical-align: middle; }

  .pf-divider { border: none; border-top: 3px solid #000; margin: 8px 0 7px; }

  .pf-date2 { display: flex; justify-content: flex-end; font-size: 9pt; margin-bottom: 6px; }
  .pf-date2 .pf-uval { border-bottom: 1px solid #000; min-width: 120px; display: inline-block; margin-left: 4px; text-align: center; vertical-align: middle; }

  .pf-sec-title { text-align: center; font-size: 11pt; font-weight: 700; margin: 4px 0 4px; letter-spacing: .3px; }

  .pf-box { border: 1px solid #555; min-height: 90px; padding: 6px 8px; margin-bottom: 12px;
            font-size: 10pt; white-space: pre-wrap; }

  .pf-bot-sigs { display: flex; font-size: 8.5pt; margin-top: 6px; }
  .pf-bot-sigs .bs { flex: 1; }
  .pf-bot-sigs .bs-head { margin-bottom: 18px; }
  .pf-bot-sigs .bs-line { border-bottom: 1px solid #000; min-width: 120px; display: inline-block; min-height: 14px; text-align: center; vertical-align: middle; }
  .pf-bot-sigs .bs-named { font-weight: 700; text-decoration: underline; display: block; font-size: 9pt; }
  .pf-bot-sigs .bs-role { font-size: 7.5pt; color: #333; margin-top: 2px; }

  /* FILTER TOOLBAR */
  .filter-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: nowrap; gap: 16px; margin-bottom: 24px;
    padding: 14px 20px; background: linear-gradient(135deg, #f8fdf8, #f0faf2);
    border: 2px solid #d4edda; border-radius: 12px;
    height: 52px;
  }
  .filter-left { 
    display: flex; align-items: center; gap: 14px; 
    flex-shrink: 0;
  }
  .filter-count {
    background: linear-gradient(135deg, #d4edda, #c8e6c9);
    color: #1a5c2a; border-radius: 20px;
    padding: 6px 14px; font-size: 12px; font-weight: 700;
    border: 1px solid #28a745;
    white-space: nowrap;
  }
  .filter-right { 
    display: flex; align-items: center; gap: 10px; 
    flex: 1; justify-content: flex-end;
    flex-wrap: nowrap;
  }

  .search-wrap {
    display: flex; align-items: center; gap: 10px;
    background: white; border: 2px solid #d4edda;
    border-radius: 10px; padding: 10px 14px;
    transition: all 0.3s ease; height: 40px;
    min-width: 220px; max-width: 380px;
    flex-shrink: 1;
  }
  .search-wrap:focus-within { 
    border-color: #28a745; 
    box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.1);
  }
  .search-wrap input {
    border: none; background: transparent; outline: none;
    font-size: 14px; width: 100%; color: #333;
    padding: 0; margin: 0; height: 100%;
  }
  .search-wrap input::placeholder { color: #aaa; }

  .filter-select-wrap {
    display: flex; align-items: center; gap: 8px;
    background: white; border: 2px solid #d4edda;
    border-radius: 10px; padding: 8px 12px;
    transition: all 0.3s ease; height: 40px;
    flex-shrink: 0;
  }
  .filter-select-wrap:focus-within { 
    border-color: #28a745; 
    box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.1);
  }
  .filter-select-wrap select,
  .filter-select-wrap input[type=date] {
    border: none; background: transparent; outline: none;
    font-size: 14px; color: #333; cursor: pointer;
    padding: 0; margin: 0; width: auto; height: 100%;
  }

  .filter-clear-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 10px; border: 2px solid #ddd;
    background: white; color: #666; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all 0.3s ease; height: 40px;
    flex-shrink: 0;
  }
  .filter-clear-btn:hover { 
    border-color: #dc2626; color: #dc2626; 
    background: #fef2f2;
    transform: translateY(-1px);
  }

  /* MODERN ACTION BUTTONS */
  .action-btns { display: flex; flex-direction: column; gap: 6px; }
  .action-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600;
    cursor: pointer; border: none; text-decoration: none;
    transition: all 0.2s ease; white-space: nowrap; letter-spacing: .3px;
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
  }
  .action-export {
    background: linear-gradient(135deg, #ffc107, #ffab00);
    color: #1a1a1a;
  }
  .action-export:hover {
    background: linear-gradient(135deg, #ffca28, #ffc107);
    box-shadow: 0 4px 12px rgba(255,193,7,.45);
    transform: translateY(-2px);
  }
  .action-update {
    background: linear-gradient(135deg, #5a6473, #444e5c);
    color: #fff;
  }
  .action-update:hover {
    background: linear-gradient(135deg, #6b7685, #556070);
    box-shadow: 0 4px 12px rgba(80,90,105,.45);
    transform: translateY(-2px);
  }
  .action-btn:active { transform: translateY(0); box-shadow: 0 1px 3px rgba(0,0,0,.15); }

  /* SVG icon helpers */
  .icon { display: inline-block; vertical-align: middle; }

  /* ===== PRINT MEDIA — only #printForm is printed ===== */
  @media print {
    /* Hide everything on the page */
    body * { visibility: hidden; }

    /* Show only the print form and its children */
    #printForm, #printForm * { visibility: visible; }

    /* Position the form at top-left, full width */
    #printForm {
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      width: 100% !important;
      margin: 0 !important;
      padding: 20px 36px 36px !important;
      box-shadow: none !important;
      border: none !important;
      display: block !important;
      font-size: 10pt !important;
      background: white !important;
    }

    /* Remove screen-only elements */
    .navbar, .tabs, .tab-panel:not(#tab-export),
    #exportActions, h3, p, .form-group > label:first-child,
    select#selectRequest { display: none !important; }

    @page {
      size: A4 portrait;
      margin: 10mm;
    }
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .navbar { padding: 0 16px; height: 64px; }
    .navbar-brand { gap: 10px; }
    .navbar-brand img { height: 32px; }
    .navbar-brand .brand-text h1 { font-size: 14px; }
    .nav-right { gap: 8px; }
    .container { padding: 0 16px; margin: 24px auto; }
    .card { padding: 24px; }
    .form-row { grid-template-columns: 1fr; gap: 16px; }
    .sig-grid { grid-template-columns: 1fr; gap: 24px; }
    .issue-grid { grid-template-columns: 1fr; }
    .filter-toolbar { flex-direction: column; align-items: stretch; }
    .filter-right { justify-content: flex-start; }
    .search-wrap, .filter-select-wrap { width: 100%; }
    table { font-size: 12px; }
    td, th { padding: 10px 12px; }
  }
</style>
</head>
<body>

<div class="navbar">
  <div class="navbar-brand">
    <img src="<?= $logoBase64 ?: 'https://www.pelco3.org/images/logo.png' ?>" alt="PELCO 3 Logo">
    <div class="brand-text">
      <h1>CORPLAN - Request System</h1>
      <span>PAMPANGA III ELECTRIC COOPERATIVE, INC.</span>
    </div>
  </div>

  <div class="nav-right">
    <!-- Date/Time -->
    <div class="nav-datetime">
      <div class="nav-date-block">
        <div class="nav-date-day" id="nav-date-day">—</div>
        <div class="nav-date-full" id="nav-date-full">— —</div>
      </div>
      <div class="nav-tb-divider"></div>
      <div class="nav-time-block">
        <div class="nav-time-val" id="nav-time">00:00:00</div>
        <div class="nav-time-lbl">Local Time</div>
      </div>
    </div>


    <!-- New Record button -->
    <button class="nav-new-btn" onclick="showTab('tab-form', document.querySelectorAll('.tab-btn')[0])">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
      </svg>
      New Record
    </button>
  </div>
</div>

<div class="container">

<?php if ($message): ?>
<div class="alert alert-success">
  <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1a5c2a" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
  <?= $message ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error">
  <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#991b1b" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <?= $error ?>
</div>
<?php endif; ?>

<div class="tabs">
  <button class="tab-btn active" onclick="showTab('tab-form', this)">
    <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
    New Request
  </button>
  <button class="tab-btn" onclick="showTab('tab-list', this)">
    <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
    All Requests
  </button>
  <button class="tab-btn" onclick="showTab('tab-export', this)">
    <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
    Export / Print
  </button>
</div>

<!-- ============ TAB 1: NEW REQUEST FORM ============ -->
<div id="tab-form" class="tab-panel active">
  <div class="card">
    <div class="form-header">
      <img src="<?= $logoBase64 ?: 'https://www.pelco3.org/images/logo.png' ?>" alt="PELCO 3 Logo">
      <div class="brand-text">
        <h2>REQUEST FORM</h2>
        <p>PAMPANGA III ELECTRIC COOPERATIVE, INC. — Corplan Department</p>
      </div>
    </div>

  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    
    <!-- REQUEST DETAILS SECTION -->
    <div class="form-section">
      <h4 class="form-section-title">Request Details</h4>
      <div class="form-row">
        <div class="form-group">
          <label>Date <span class="req">*</span></label>
          <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
          <label>Department <span class="req">*</span></label>
          <input type="text" name="department" placeholder="Enter department name" required>
        </div>
      </div>
    </div>

    <!-- ISSUES/CONCERNS SECTION -->
    <div class="form-section">
      <h4 class="form-section-title">Issues / Concerns</h4>
      <div class="issue-grid">
        <div>
          <div class="issue-label">DATABASE</div>
          <input type="text" name="database" placeholder="Describe database issue">
        </div>
        <div>
          <div class="issue-label">HARDWARE</div>
          <input type="text" name="hardware" placeholder="Describe hardware issue">
        </div>
        <div>
          <div class="issue-label">SOFTWARE</div>
          <input type="text" name="software" placeholder="Describe software issue">
        </div>
        <div>
          <div class="issue-label">CCTV FOOTAGE</div>
          <input type="text" name="cctv" placeholder="Describe CCTV concern">
        </div>
        <div style="grid-column:span 2">
          <div class="issue-label">OTHERS</div>
          <input type="text" name="others" placeholder="Other concerns">
        </div>
      </div>
    </div>

    <!-- REQUEST PURPOSE SECTION -->
    <div class="form-section">
      <h4 class="form-section-title">Request Purpose</h4>
      <div class="form-group">
        <label>Purpose <span class="req">*</span></label>
        <textarea name="purpose" rows="4" placeholder="Explain the purpose of this request..." required></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Requested By <span class="req">*</span></label>
          <input type="text" name="requested_by" placeholder="Full name of requester" required>
        </div>
        <div class="form-group">
          <label>Request Date</label>
          <input type="date" name="request_date" value="<?= date('Y-m-d') ?>">
        </div>
      </div>
    </div>

    <!-- FINDINGS & RECOMMENDATIONS SECTION -->
    <div class="form-section">
      <h4 class="form-section-title">Findings & Recommendations</h4>
      <div class="form-group">
        <label>Finding / Result</label>
        <textarea name="finding" rows="4" placeholder="Describe findings..."></textarea>
      </div>
      <div class="form-group">
        <label>Recommendations / Accomplishment Report</label>
        <textarea name="remarks" rows="4" placeholder="Enter recommendations or accomplishment report..."></textarea>
      </div>
    </div>

    <!-- PERSONNEL & APPROVALS SECTION -->
    <div class="form-section">
      <h4 class="form-section-title">Personnel & Approvals</h4>
      <div class="form-row">
        <div class="form-group">
          <label>Checked / Accomplished By (Corplan Personnel)</label>
          <input type="text" name="accomplished_by" placeholder="Name">
        </div>
        <div class="form-group">
          <label>Conforme (Name &amp; Signature)</label>
          <input type="text" name="conforme" placeholder="Name">
        </div>
      </div>
      <div class="form-group">
        <label>Noted By</label>
        <input type="text" name="noted_by" value="Engr. Dean Mark Hernandez" placeholder="Name">
      </div>
    </div>

    <!-- SIGNATURES SECTION -->
    <div class="sig-grid">
      <div class="sig-box">
        <div class="sig-line"></div>
        <input type="text" name="sig1_name" value="ABEGAIL C. NUNAG"
          style="font-weight:700;font-size:13px;text-decoration:underline;text-align:center;
                 border:none;border-bottom:1px dashed #a0d9b8;width:100%;background:transparent;
                 outline:none;text-transform:uppercase;display:block;">
        <input type="text" name="sig1_title" value="Trading Supervisor"
          style="font-size:11px;color:#666;text-align:center;border:none;border-bottom:1px dashed #a0d9b8;
                 width:100%;background:transparent;outline:none;display:block;margin-top:4px;">
      </div>
      <div class="sig-box">
        <div class="sig-line"></div>
        <input type="text" name="sig2_name" value="ALLAN PAUL V. GARCIA"
          style="font-weight:700;font-size:13px;text-decoration:underline;text-align:center;
                 border:none;border-bottom:1px dashed #a0d9b8;width:100%;background:transparent;
                 outline:none;text-transform:uppercase;display:block;">
        <input type="text" name="sig2_title" value="Corplan &amp; IT Supervisor"
          style="font-size:11px;color:#666;text-align:center;border:none;border-bottom:1px dashed #a0d9b8;
                 width:100%;background:transparent;outline:none;display:block;margin-top:4px;">
      </div>
    </div>

    <div class="btn-group">
      <button type="submit" name="submit_request" class="btn btn-primary">
        <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        Submit Request
      </button>
      <button type="reset" class="btn btn-warning">
        <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.87"/></svg>
        Clear Form
      </button>
    </div>
  </form>
</div>
</div>

<!-- ============ TAB 2: ALL REQUESTS ============ -->
<div id="tab-list" class="tab-panel">
<div class="card">

  <!-- SEARCH & FILTER TOOLBAR -->
  <div class="filter-toolbar">
    <div class="filter-left">
      <h3>All Requests</h3>
      <span class="filter-count" id="filterCount"></span>
    </div>
    <div class="filter-right">
      <!-- Search bar -->
      <div class="search-wrap">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="filterSearch" placeholder="Search request, name, purpose…" oninput="applyFilters()">
      </div>
      <!-- Department filter -->
      <div class="filter-select-wrap">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <select id="filterDept" onchange="applyFilters()">
          <option value="">All Departments</option>
          <?php
            $depts = array_unique(array_column($requests, 'department'));
            sort($depts);
            foreach ($depts as $d): ?>
            <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <!-- Date filter -->
      <div class="filter-select-wrap">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <input type="date" id="filterDate" onchange="applyFilters()">
      </div>
      <!-- Status filter -->
      <div class="filter-select-wrap">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <select id="filterStatus" onchange="applyFilters()">
          <option value="">All Status</option>
          <option value="Pending">Pending</option>
          <option value="In Progress">In Progress</option>
          <option value="Done">Done</option>
        </select>
      </div>
      <!-- Clear button -->
      <button class="filter-clear-btn" onclick="clearFilters()" title="Clear filters">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Clear
      </button>
    </div>
  </div>

  <div class="table-wrap">
    <table id="requestsTable">
      <thead>
        <tr>
          <th>Request No.</th>
          <th>Date</th>
          <th>Department</th>
          <th>Issues</th>
          <th>Purpose</th>
          <th>Requested By</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="requestsTbody">
      <?php foreach ($requests as $r): ?>
        <tr class="req-row"
            data-search="<?= strtolower(htmlspecialchars($r['request_no'] . ' ' . $r['department'] . ' ' . $r['requested_by'] . ' ' . $r['purpose'])) ?>"
            data-dept="<?= htmlspecialchars($r['department']) ?>"
            data-date="<?= $r['date'] ?>"
            data-status="<?= $r['status'] ?>">
          <td><strong><?= htmlspecialchars($r['request_no']) ?></strong></td>
          <td><?= htmlspecialchars($r['date']) ?></td>
          <td><?= htmlspecialchars($r['department']) ?></td>
          <td>
            <?php
              $issues = [];
              if ($r['issue_database']) $issues[] = 'DB';
              if ($r['issue_hardware']) $issues[] = 'HW';
              if ($r['issue_software']) $issues[] = 'SW';
              if ($r['issue_cctv'])     $issues[] = 'CCTV';
              if ($r['issue_others'])   $issues[] = 'Other';
              echo $issues ? implode(', ', $issues) : '—';
            ?>
          </td>
          <td><?= nl2br(htmlspecialchars(substr($r['purpose'], 0, 60))) ?>...</td>
          <td><?= htmlspecialchars($r['requested_by']) ?></td>
          <td>
            <span class="badge <?= 
              $r['status'] === 'Pending' ? 'badge-pending' : 
              ($r['status'] === 'Done' ? 'badge-done' : 'badge-progress')
            ?>"><?= $r['status'] ?></span>
          </td>
          <td>
            <div class="action-btns">
              <button class="action-btn action-export" onclick="viewRequest(<?= $r['id'] ?>)" title="Export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Export
              </button>
              <a href="update.php?id=<?= $r['id'] ?>" class="action-btn action-update" title="Update">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Update
              </a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($requests)): ?>
        <tr id="noRecordsRow"><td colspan="8" style="text-align:center;color:#aaa;padding:30px">No requests found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    <div id="noFilterResults" style="display:none;text-align:center;padding:36px 0;color:#aaa;font-size:14px;">
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" style="display:block;margin:0 auto 10px"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      No records match your filters. <a href="#" onclick="clearFilters();return false;" style="color:#28a745;">Clear filters</a>
    </div>
  </div>
</div>
</div>

<!-- ============ TAB 3: EXPORT / PRINT ============ -->
<div id="tab-export" class="tab-panel">
<div class="card">
  <h3 style="margin-bottom:6px;">Export Request Form</h3>
  <p style="margin-bottom:20px;color:#888;font-size:13px">Select a request from the list below to preview and export as PDF or PNG.</p>

  <div class="form-group">
    <label>Select Request</label>
    <select id="selectRequest" onchange="loadPreview(this.value)">
      <option value="">-- Choose a request --</option>
      <?php foreach ($requests as $r): ?>
        <option value="<?= $r['id'] ?>"><?= $r['request_no'] ?> — <?= htmlspecialchars($r['department']) ?> (<?= $r['date'] ?>)</option>
      <?php endforeach; ?>
    </select>
  </div>

  <div id="exportActions" style="display:none;margin-bottom:20px">
    <div class="btn-group">
      <button class="btn btn-primary" onclick="exportPDF()">
        <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        Export as PDF
      </button>
      <button class="btn btn-warning" onclick="exportPNG()">
        <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        Export as PNG
      </button>
      <button class="btn btn-secondary" onclick="printForm()">
        <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Print
      </button>
    </div>
  </div>

  <!-- PRINT PREVIEW -->
  <div id="printForm" style="display:none">

   <div class="lh" style="display: flex; align-items: center; gap: 30px;">
  <img src="<?= $logoBase64 ?: 'https://www.pelco3.org/images/logo.png' ?>" 
       alt="PELCO III Logo" 
       style="width: 150px; height: auto; margin-left: 20px;">
  
  <div class="lh-text">
    <div class="lh-org" style="font-weight: bold; font-size: 1.2rem;">
      PAMPANGA III ELECTRIC COOPERATIVE, INC.
    </div>
    <div class="lh-addr" style="font-size: 0.9rem; line-height: 1.5;">
      Sampaloc, Apalit, Pampanga<br>
      Tel. No.: (045) 302 5114; Fax. No.: (045) 302 5785<br>
      Cell phone nos: 0998 533 1084; 0917 557 3526<br>
      email address: pelco3coop@gmail.com &nbsp;&nbsp; website: www.pelco3.org
    </div>
  </div>
</div>

    <div class="pf-top-right">
      <div class="pf-row">Date &nbsp; : <span class="pf-uval" id="pf-date"></span></div>
      <div class="pf-row">Request No.: <span class="pf-uval" id="pf-reqno"></span></div>
    </div>

    <div class="pf-title">REQUEST FORM</div>

    <div class="pf-dept-row">
      <span class="pf-dept-lbl">DEPARTMENT</span>
      <span class="pf-colon">&nbsp;:&nbsp;</span>
      <span class="pf-dline" id="pf-dept"></span>
    </div>

    <div class="pf-issue-block">
     <div class="pf-issue-lbl-col" style="margin-right: 20px;">ISSUE/CONCERN : </div>
      <div class="pf-issue-rows">
        <div class="pf-irow">
          <div class="pf-cb" id="cb-db"></div>
          <span class="pf-iname">DATABASE</span><span class="pf-icolon">&nbsp;:&nbsp;</span>
          <span class="pf-ival" id="pf-db"></span>
        </div>
        <div class="pf-irow">
          <div class="pf-cb" id="cb-hw"></div>
          <span class="pf-iname">HARD WARE</span><span class="pf-icolon">&nbsp;:&nbsp;</span>
          <span class="pf-ival" id="pf-hw"></span>
        </div>
        <div class="pf-irow">
          <div class="pf-cb" id="cb-sw"></div>
          <span class="pf-iname">SOFTWARE</span><span class="pf-icolon">&nbsp;:&nbsp;</span>
          <span class="pf-ival" id="pf-sw"></span>
        </div>
        <div class="pf-irow">
          <div class="pf-cb" id="cb-cctv"></div>
          <span class="pf-iname">CCTV FOOTAGE</span><span class="pf-icolon">&nbsp;:&nbsp;</span>
          <span class="pf-ival" id="pf-cctv"></span>
        </div>
        <div class="pf-irow">
          <div class="pf-cb" id="cb-others"></div>
          <span class="pf-iname">OTHERS</span><span class="pf-icolon">&nbsp;:&nbsp;</span>
          <span class="pf-ival" id="pf-others"></span>
        </div>
      </div>
    </div>

    <div class="pf-purpose-row">
      <span class="pf-plbl">PURPOSE</span><span class="pf-pcolon">&nbsp;:&nbsp;</span><span class="pf-pval" id="pf-purpose"></span>
    </div>

    <div class="pf-midsig">
      <div class="mc1">
        <div class="mc-head">Requested by:</div>
        <div class="mc-sigline" id="pf-reqby">&nbsp;</div>
        <div class="mc-role">Name &amp; Signature</div>
        <div class="mc-date">Date &nbsp; : <span class="pf-uval" id="pf-reqdate">&nbsp;</span></div>
      </div>
      <div class="mc2">
        <div class="mc-head">Received by:</div>
        <span class="mc-named">ALLAN PAUL V. GARCIA</span>
        <div class="mc-role">Corplan &amp; IT Supervisor</div>
      </div>
      <div class="mc3">
        <div class="mc-head">&nbsp;</div>
        <span class="mc-named">ABEGAIL C. NUNAG</span>
        <div class="mc-role">Trading Supervisor</div>
      </div>
    </div>

    <hr class="pf-divider">

    <div class="pf-date2">Date &nbsp; : <span class="pf-uval" id="pf-date2">&nbsp;</span></div>

    <div class="pf-sec-title">FINDING/ RESULT</div>
    <div class="pf-box" id="pf-finding"></div>

    <div class="pf-sec-title">RECOMMENDATIONS/ACCOMPLISHMENT REPORT</div>
    <div class="pf-box" id="pf-remarks"></div>

    <div class="pf-bot-sigs">
      <div class="bs">
        <div class="bs-head">Checked/ Accomplished by:</div>
        <div class="bs-line" id="pf-accomplished">&nbsp;</div>
        <div class="bs-role">Corplan Personnel Signature</div>
      </div>
      <div class="bs">
        <div class="bs-head">Conforme:</div>
        <div class="bs-line" id="pf-conforme">&nbsp;</div>
        <div class="bs-role">Name &amp; Signature</div>
      </div>
      <div class="bs">
        <div class="bs-head">Noted by:</div>
        <span class="bs-named">Engr. Dean Mark Hernandez</span>
        <div class="bs-role">Corplan, IT and Trading Manager</div>
      </div>
    </div>

  </div>

</div>
</div>

</div><!-- /container -->

<script>
const ALL_REQUESTS = <?= json_encode(array_column($requests, null, 'id')) ?>;

function showTab(id, btn) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  btn.classList.add('active');
}

function loadPreview(id) {
  if (!id) {
    document.getElementById('printForm').style.display = 'none';
    document.getElementById('exportActions').style.display = 'none';
    return;
  }
  const r = ALL_REQUESTS[id];
  if (!r) return;

  document.getElementById('pf-reqno').textContent = r.request_no || '';
  document.getElementById('pf-date').textContent  = r.date || '';
  document.getElementById('pf-date2').textContent = r.date || '';
  document.getElementById('pf-dept').textContent  = r.department || '';

  const issueMap = {
    'cb-db':     { val: 'pf-db',     data: r.issue_database },
    'cb-hw':     { val: 'pf-hw',     data: r.issue_hardware },
    'cb-sw':     { val: 'pf-sw',     data: r.issue_software },
    'cb-cctv':   { val: 'pf-cctv',   data: r.issue_cctv },
    'cb-others': { val: 'pf-others', data: r.issue_others }
  };
  for (const [cbId, info] of Object.entries(issueMap)) {
    document.getElementById(info.val).textContent = info.data || '';
    const cb = document.getElementById(cbId);
    cb.textContent = (info.data && info.data.trim()) ? '✓' : '';
    cb.style.fontWeight = '900';
  }

  document.getElementById('pf-purpose').textContent = r.purpose || '';
  document.getElementById('pf-reqby').textContent   = r.requested_by || '\u00A0';
  document.getElementById('pf-reqdate').textContent = r.request_date || r.date || '';
  document.getElementById('pf-finding').textContent = r.finding  || '';
  document.getElementById('pf-remarks').textContent = r.remarks  || '';
  document.getElementById('pf-accomplished').textContent = r.accomplished_by || '\u00A0';
  document.getElementById('pf-conforme').textContent     = r.conforme        || '\u00A0';

  document.getElementById('printForm').style.display = 'block';
  document.getElementById('exportActions').style.display = 'block';
}

function viewRequest(id) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-export').classList.add('active');
  document.querySelectorAll('.tab-btn')[2].classList.add('active');
  document.getElementById('selectRequest').value = id;
  loadPreview(id);
  document.getElementById('printForm').scrollIntoView({ behavior: 'smooth' });
}

async function exportPDF() {
  const { jsPDF } = window.jspdf;
  const el = document.getElementById('printForm');
  const canvas = await html2canvas(el, { scale: 2, useCORS: true, allowTaint: true, backgroundColor: '#ffffff' });
  const imgData = canvas.toDataURL('image/png');
  const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
  const pdfW = pdf.internal.pageSize.getWidth();
  const pdfH = pdf.internal.pageSize.getHeight();
  const ratio = canvas.height / canvas.width;
  const imgH = pdfW * ratio;
  if (imgH <= pdfH) {
    pdf.addImage(imgData, 'PNG', 0, 0, pdfW, imgH);
  } else {
    let yPos = 0; let remaining = imgH; let page = 0;
    while (remaining > 0) {
      if (page > 0) pdf.addPage();
      pdf.addImage(imgData, 'PNG', 0, -page * pdfH, pdfW, imgH);
      remaining -= pdfH; page++;
    }
  }
  const reqno = document.getElementById('pf-reqno').textContent || 'form';
  pdf.save(`${reqno}.pdf`);
}

async function exportPNG() {
  const el = document.getElementById('printForm');
  const canvas = await html2canvas(el, { scale: 2, useCORS: true, allowTaint: true, backgroundColor: '#ffffff' });
  const link = document.createElement('a');
  const reqno = document.getElementById('pf-reqno').textContent || 'form';
  link.download = `${reqno}.png`;
  link.href = canvas.toDataURL('image/png');
  link.click();
}

// ── Search & Filter ──
function applyFilters() {
  const search = document.getElementById('filterSearch').value.toLowerCase().trim();
  const dept   = document.getElementById('filterDept').value;
  const date   = document.getElementById('filterDate').value;
  const status = document.getElementById('filterStatus').value;

  const rows = document.querySelectorAll('.req-row');
  let visible = 0;

  rows.forEach(row => {
    const matchSearch = !search || row.dataset.search.includes(search);
    const matchDept   = !dept   || row.dataset.dept === dept;
    const matchDate   = !date   || row.dataset.date === date;
    const matchStatus = !status || row.dataset.status === status;

    if (matchSearch && matchDept && matchDate && matchStatus) {
      row.style.display = '';
      visible++;
    } else {
      row.style.display = 'none';
    }
  });

  const countEl = document.getElementById('filterCount');
  if (countEl) countEl.textContent = visible + ' record' + (visible !== 1 ? 's' : '');

  const noResults = document.getElementById('noFilterResults');
  if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
}

function clearFilters() {
  document.getElementById('filterSearch').value = '';
  document.getElementById('filterDept').value   = '';
  document.getElementById('filterDate').value   = '';
  document.getElementById('filterStatus').value = '';
  applyFilters();
}

// Init count on load
window.addEventListener('DOMContentLoaded', () => applyFilters());

function printForm() {
  const pf = document.getElementById('printForm');
  if (!pf || pf.style.display === 'none') {
    alert('Please select a request first.');
    return;
  }
  window.print();
}


;(function tick(){
  const n = new Date();
  const timeEl = document.getElementById('nav-time');
  const dayEl  = document.getElementById('nav-date-day');
  const fullEl = document.getElementById('nav-date-full');
  if(timeEl) timeEl.textContent = n.toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:true});
  if(dayEl)  dayEl.textContent  = n.toLocaleDateString('en-PH',{weekday:'long',day:'numeric'});
  if(fullEl) fullEl.textContent = n.toLocaleDateString('en-PH',{month:'long',year:'numeric'});
  setTimeout(tick, 1000);
})();

</script>
</body>
</html>