<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
include('db.php');check_session_timeout();
$searchTerm = trim($_GET['search'] ?? '');
$dateFrom   = $_GET['date_from'] ?? '';
$dateTo     = $_GET['date_to'] ?? '';

$conditions = [];
$params     = [];

if ($searchTerm !== '') {
    $conditions[] = "(accountnumber LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
    $like = "%$searchTerm%";
    $params = [$like, $like, $like];
}
if ($dateFrom !== '') { $conditions[] = "DATE(archived_at) >= ?"; $params[] = $dateFrom; }
if ($dateTo   !== '') { $conditions[] = "DATE(archived_at) <= ?"; $params[] = $dateTo;   }

$where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
$sql   = "SELECT * FROM archive $where ORDER BY archived_at DESC";
$query = $conn->prepare($sql);
$query->execute($params);
$archived = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PELCO III – Archive System</title>
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
    <span class="nav-badge"><i class="bi bi-archive-fill"></i> Archive System</span>
    <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i> Dashboard</a>
</nav>

<div class="page">

    <!-- Filter Bar -->
    <div class="filter-card">
        <form class="filter-form" method="GET" action="archive.php">
            <div class="field" style="grid-column:1">
                <label>Search</label>
                <input type="search" name="search" placeholder="Account #, name…" value="<?php echo sanitize($searchTerm); ?>">
            </div>
            <div class="field">
                <label>From Date</label>
                <input type="date" name="date_from" value="<?php echo sanitize($dateFrom); ?>">
            </div>
            <div class="field">
                <label>To Date</label>
                <input type="date" name="date_to" value="<?php echo sanitize($dateTo); ?>">
            </div>
            <div style="display:flex;gap:8px;align-items:flex-end;">
                <button type="submit" class="btn-search">Search</button>
                <?php if ($searchTerm || $dateFrom || $dateTo): ?>
                    <a href="archive.php" class="btn-clear">Clear</a>
                <?php endif; ?>
                <button type="button" class="btn-export" onclick="exportCSV()"><i class="bi bi-download"></i> CSV</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-toolbar">
            <div class="table-title">
                <?php echo $searchTerm ? "Results for &ldquo;".sanitize($searchTerm)."&rdquo;" : "Archived Consumer Records"; ?>
            </div>
            <span class="record-badge"><?php echo count($archived); ?> record<?php echo count($archived)!=1?'s':''; ?></span>
        </div>

        <div style="overflow-x:auto;">
            <table id="archiveTable">
                <thead>
                    <tr>
                        <th>Account #</th>
                        <th>Consumer Name</th>
                        <th>Former Status</th>
                        <th>Last kWh</th>
                        <th>Archived On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($archived) > 0): foreach ($archived as $row):
                        $bc = getStatusBadgeClass((int)$row['status']);
                        $bl = getStatusLabel((int)$row['status']);
                    ?>
                    <tr>
                        <td><span class="acct-num"><?php echo sanitize($row['accountnumber']); ?></span></td>
                        <td><span class="consumer-name"><?php echo sanitize($row['first_name'].' '.$row['last_name']); ?></span></td>
                        <td>
                            <span class="badge-status <?php echo $bc; ?>">
                                <span class="led"></span><?php echo $bl; ?>
                            </span>
                        </td>
                        <td style="font-family:'JetBrains Mono',monospace;font-weight:600;"><?php echo number_format($row['kilowatthour'],2); ?> kWh</td>
                        <td><span class="archived-badge"><?php echo date('M d, Y · g:i A', strtotime($row['archived_at'])); ?></span></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5">
                        <div class="empty-state">
                            <div class="icon"><i class="bi bi-inbox" style="font-size:2.2rem; color:#c8d4c5;"></i></div>
                            <p>No archived records found<?php echo $searchTerm ? " for &ldquo;".sanitize($searchTerm)."&rdquo;" : ''; ?>.</p>
                        </div>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="padding:12px 22px;background:#f8faf7;border-top:1px solid var(--border);font-size:.72rem;color:var(--muted);font-family:'JetBrains Mono',monospace;text-align:center;">
            © 2026 PELCO III Archive System · <?php echo count($archived); ?> historical records
        </div>
    </div>
</div>

<script>
function exportCSV() {
    const rows = [['Account #','Name','Former Status','kWh','Archived On']];
    document.querySelectorAll('#archiveTable tbody tr').forEach(tr => {
        const cells = tr.querySelectorAll('td');
        if (cells.length >= 5) {
            rows.push([
                cells[0].textContent.trim(),
                cells[1].textContent.trim(),
                cells[2].textContent.trim(),
                cells[3].textContent.trim(),
                cells[4].textContent.trim()
            ]);
        }
    });
    const csv = rows.map(r => r.map(c => `"${c.replace(/"/g,'""')}"`).join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = 'PELCO3_Archive_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
}
</script>
</body>
</html>
