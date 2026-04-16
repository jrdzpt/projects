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

$format = $_GET['format'] ?? 'excel';
$db     = getDB();


$where = ['1=1'];
if (!empty($_GET['search']))   { $s = $db->real_escape_string($_GET['search']);     $where[] = "(reference_no LIKE '%$s%' OR account_name LIKE '%$s%' OR account_number LIKE '%$s%')"; }
if (!empty($_GET['concern'])) {
    if ($_GET['concern'] === '__OTHER__') {
        $standardConcerns = getDropdownOptions('concern');
        if (!empty($standardConcerns)) {
            $esc = array_map(fn($v) => "'" . $db->real_escape_string($v) . "'", $standardConcerns);
            $where[] = "concern NOT IN (" . implode(',', $esc) . ") AND concern != ''";
        }
    } else {
        $v = $db->real_escape_string($_GET['concern']);
        $where[] = "concern='$v'";
    }
}
if (!empty($_GET['area'])) {
    if ($_GET['area'] === '__OTHER__') {
        $standardAreas = getDropdownOptions('area_dept');
        if (!empty($standardAreas)) {
            $esc = array_map(fn($v) => "'" . $db->real_escape_string($v) . "'", $standardAreas);
            $where[] = "area_dept NOT IN (" . implode(',', $esc) . ") AND area_dept != ''";
        }
    } else {
        $v = $db->real_escape_string($_GET['area']);
        $where[] = "area_dept='$v'";
    }
}
if (!empty($_GET['date_from'])){ $v = $db->real_escape_string($_GET['date_from']); $where[] = "date_forwarded>='$v'"; }
if (!empty($_GET['date_to']))  { $v = $db->real_escape_string($_GET['date_to']);   $where[] = "date_forwarded<='$v'"; }

$wSql = implode(' AND ', $where);
$rows = [];
$res  = $db->query("SELECT * FROM cs_records WHERE $wSql ORDER BY created_at DESC");
while ($r = $res->fetch_assoc()) $rows[] = $r;

$headers = ['#', 'Ref No', 'Account No', 'Account Name', 'Address', 'Landmark',
            'Contact No', 'Messenger / Caller', 'Concern', 'Area / Dept',
            'Date Forwarded', 'Notes', 'Created At'];
$cols    = ['_num', 'reference_no', 'account_number', 'account_name', 'address', 'landmark',
            'contact_no', 'messenger_caller', 'concern', 'area_dept',
            'date_forwarded', 'notes', 'created_at'];

$logoUrl = 'https://www.pelco3.org/images/logo.png';


$imgData = @file_get_contents('https://www.pelco3.org/images/logo.png');
$logoForPng = $imgData
    ? 'data:image/png;base64,' . base64_encode($imgData)
    : 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50"><circle cx="25" cy="25" r="25" fill="#037d3c"/><text x="25" y="31" text-anchor="middle" font-family="Arial" font-size="14" font-weight="bold" fill="#fce704">P3</text></svg>');


if ($format === 'excel') {
    $name = 'PELCO3_CS_Records_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"$name\"");
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, ['PELCO III — Pampanga Electric Cooperative III']);
    fputcsv($out, ['Customer Service Records']);
    fputcsv($out, ['Generated: ' . date('F d, Y  H:i') . '   |   Total Records: ' . count($rows)]);
    fputcsv($out, []);
   
    $csvHeaders = array_filter($headers, fn($h) => $h !== '#');
    fputcsv($out, array_values($csvHeaders));
    foreach ($rows as $i => $r) {
        $line = [];
        foreach ($cols as $c) {
            if ($c === '_num') continue;
            $line[] = $r[$c] ?? '';
        }
        fputcsv($out, $line);
    }
    fclose($out);
    exit;
}


if ($format === 'pdf') {
    header('Content-Type: text/html; charset=utf-8');
    $date  = date('F d, Y · H:i');
    $total = count($rows);
    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>PELCO III — Customer Service Records</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body {
    font-family: 'Helvetica Neue', Arial, sans-serif;
    font-size: 9px; color: #1a1a1a;
    background: #fff; padding: 10mm;
  }


  .header {
    display: flex; align-items: center; gap: 14px;
    padding-bottom: 10px;
    border-bottom: 2px solid #037d3c;
    margin-bottom: 10px;
  }
  .header img {
    width: 42px; height: 42px; object-fit: contain; flex-shrink: 0;
  }
  .header-text { flex: 1; }
  .header-org {
    font-size: 16px; font-weight: 700; color: #037d3c;
    letter-spacing: .2px; line-height: 1.1;
  }
  .header-sub {
    font-size: 8.5px; color: #777; margin-top: 3px; letter-spacing: .2px;
  }
  .header-meta {
    text-align: right; font-size: 8px; color: #999; line-height: 1.7;
  }
  .header-meta strong { color: #1a1a1a; font-size: 9px; display: block; }

 
  table { width: 100%; border-collapse: collapse; font-size: 7.8px; }
  thead th {
    background: #037d3c; color: #fff;
    padding: 5px 5px; text-align: left;
    font-size: 7px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .4px; white-space: nowrap;
    -webkit-print-color-adjust: exact; print-color-adjust: exact;
  }
  tbody td {
    padding: 4px 5px; border-bottom: 1px solid #ebebeb;
    vertical-align: top; word-break: break-word; line-height: 1.4;
  }
  tbody tr:nth-child(even) td {
    background: #f7faf8;
    -webkit-print-color-adjust: exact; print-color-adjust: exact;
  }
  tbody tr:last-child td { border-bottom: none; }
  .num { color: #ccc; font-size: 7px; text-align: center; }
  .ref { font-weight: 600; color: #037d3c; }


  .footer {
    margin-top: 10px; padding-top: 6px;
    border-top: 1px solid #e8e8e8;
    display: flex; justify-content: space-between;
    font-size: 7.5px; color: #bbb;
  }

  @media print {
    @page { size: landscape; margin: 8mm; }
    body { padding: 0; }
  }
</style>
</head>
<body>

<div class="header">
  <img src="$logoUrl" alt="PELCO III" onerror="this.style.display='none'">
  <div class="header-text">
    <div class="header-org">PELCO III</div>
    <div class="header-sub">Pampanga Electric Cooperative III &nbsp;&middot;&nbsp; Customer Service Records</div>
  </div>
  <div class="header-meta">
    <strong>$total record(s)</strong>
    Generated: $date
  </div>
</div>

<table>
<thead><tr>
HTML;
    foreach ($headers as $h) echo "<th>" . htmlspecialchars($h) . "</th>";
    echo "</tr></thead><tbody>";
    $i = 1;
    foreach ($rows as $r) {
        echo "<tr>";
        foreach ($cols as $c) {
            if ($c === '_num') {
                echo "<td class='num'>$i</td>";
            } else {
                $v   = htmlspecialchars($r[$c] ?? '');
                $cls = ($c === 'reference_no') ? " class='ref'" : '';
                echo "<td$cls>$v</td>";
            }
        }
        echo "</tr>";
        $i++;
    }
    echo <<<HTML
</tbody>
</table>

<div class="footer">
  <span>PELCO III &mdash; Customer Service System</span>
  <span>Confidential &middot; For Internal Use Only</span>
</div>

<script>window.onload = function(){ window.print(); }</script>
</body>
</html>
HTML;
    exit;
}


if ($format === 'png') {
    header('Content-Type: text/html; charset=utf-8');
    $date  = date('F d, Y');
    $total = count($rows);

    $bodyHtml = '';
    $i = 1;
    foreach ($rows as $r) {
        $bodyHtml .= '<tr>';
        foreach ($cols as $c) {
            if ($c === '_num') {
                $bodyHtml .= "<td class='num'>$i</td>";
            } else {
                $v   = htmlspecialchars($r[$c] ?? '', ENT_QUOTES);
                $cls = ($c === 'reference_no') ? " class='ref'" : '';
                $bodyHtml .= "<td$cls>$v</td>";
            }
        }
        $bodyHtml .= '</tr>';
        $i++;
    }
    $bodyHtml = addslashes($bodyHtml);
    $headHtml = implode('', array_map(fn($h) => "<th>" . htmlspecialchars($h) . "</th>", $headers));

    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>PELCO III — Export PNG</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body {
    font-family: 'Helvetica Neue', Arial, sans-serif;
    background: #f0f2f1;
    display: flex; flex-direction: column; align-items: center;
    padding: 32px 20px; min-height: 100vh;
  }

  #capture {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 20px rgba(0,0,0,.07);
    max-width: 1440px; width: 100%;
    overflow: hidden;
  }

  /* Header */
  .hd {
    display: flex; align-items: center; gap: 16px;
    padding: 20px 24px;
    border-bottom: 2px solid #037d3c;
  }
  .hd img { width: 50px; height: 50px; object-fit: contain; flex-shrink: 0; }
  .hd-text { flex: 1; }
  .hd-org  { font-size: 20px; font-weight: 700; color: #037d3c; line-height: 1.1; }
  .hd-sub  { font-size: 11px; color: #888; margin-top: 4px; }
  .hd-meta { text-align: right; font-size: 11px; color: #aaa; line-height: 1.7; }
  .hd-meta strong { color: #1a1a1a; font-size: 13px; display: block; }

  /* Table */
  table { width: 100%; border-collapse: collapse; font-size: 9.5px; }
  thead th {
    background: #037d3c; color: #fff;
    padding: 8px 7px; text-align: left;
    font-size: 8px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .5px; white-space: nowrap;
  }
  tbody td {
    padding: 6px 7px; border-bottom: 1px solid #f0f0f0;
    vertical-align: top; line-height: 1.4;
  }
  tbody tr:nth-child(even) td { background: #f7faf8; }
  .num { color: #ccc; font-size: 8.5px; text-align: center; }
  .ref { font-weight: 600; color: #037d3c; }

  /* Footer */
  .ft {
    padding: 11px 24px; border-top: 1px solid #f0f0f0;
    display: flex; justify-content: space-between;
    font-size: 10px; color: #ccc;
  }

  /* Save button */
  .save-btn {
    margin-top: 22px;
    padding: 11px 30px;
    background: #037d3c; color: #fff;
    border: none; border-radius: 7px;
    cursor: pointer; font-size: 14px; font-weight: 600; font-family: inherit;
    display: flex; align-items: center; gap: 8px;
    transition: background .15s;
  }
  .save-btn:hover { background: #025a2b; }
</style>
</head>
<body>

<div id="capture">
  <div class="hd">
    <img src="$logoForPng" alt="PELCO III">
    <div class="hd-text">
      <div class="hd-org">PELCO III</div>
      <div class="hd-sub">Pampanga Electric Cooperative III &nbsp;&middot;&nbsp; Customer Service Records</div>
    </div>
    <div class="hd-meta">
      <strong>$total record(s)</strong>
      $date
    </div>
  </div>

  <table>
    <thead><tr>$headHtml</tr></thead>
    <tbody id="tbody"></tbody>
  </table>

  <div class="ft">
    <span>PELCO III — Customer Service System</span>
    <span>Confidential &middot; For Internal Use Only</span>
  </div>
</div>

<button class="save-btn" onclick="saveImg()">
  <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#fff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
  Save as PNG
</button>

<script>
document.getElementById('tbody').innerHTML = '$bodyHtml';
function saveImg(){
  html2canvas(document.getElementById('capture'),{scale:2,useCORS:true,allowTaint:true}).then(c=>{
    var a = document.createElement('a');
    a.download = 'PELCO3_CS_Records_$date.png';
    a.href = c.toDataURL('image/png');
    a.click();
  });
}
</script>
</body>
</html>
HTML;
    exit;
}

echo "Unknown format.";