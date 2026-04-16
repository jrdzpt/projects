<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
include('db.php');

// 1. Capture the search term from the URL
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// 2. Adjust SQL query based on search
if ($searchTerm != '') {
    // Searches for partial matches in Account #, First Name, or Last Name
    $query = $conn->prepare("SELECT * FROM consumers 
                            WHERE accountnumber LIKE ? 
                            OR first_name LIKE ? 
                            OR last_name LIKE ? 
                            ORDER BY id DESC");
    $likeTerm = "%$searchTerm%";
    $query->execute([$likeTerm, $likeTerm, $likeTerm]);
} else {
    // Default view: Show all records
    $query = $conn->query("SELECT * FROM consumers ORDER BY id DESC");
}

$consumers = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PELCO III - Consumer Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        :root {
            --pelco-green: #049f4d;
            --pelco-yellow: #fce704;
            --pelco-blue: #0102f3;
            --pelco-red: #fb0908;
        }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .navbar-pelco { background-color: var(--pelco-green); border-bottom: 4px solid var(--pelco-yellow); }
        .card-header-pelco { background-color: var(--pelco-green); color: white; font-weight: bold; }
        .status-led { height: 12px; width: 12px; border-radius: 50%; display: inline-block; margin-right: 8px; }
        .led-active { background-color: #02f801; box-shadow: 0 0 8px #02f801; }
        .led-inactive { background-color: var(--pelco-red); box-shadow: 0 0 8px var(--pelco-red); }
        .led-pullout { background-color: var(--pelco-blue); box-shadow: 0 0 8px var(--pelco-blue); }
        .btn-action { background-color: var(--pelco-green); color: white; border: none; font-weight: 600; }
        .btn-action:hover { background-color: #037d3c; color: var(--pelco-yellow); }
        
        /* Search bar styling */
        .search-input {
            border-radius: 20px 0 0 20px !important;
            border: none;
        }
        .search-btn {
            border-radius: 0 20px 20px 0 !important;
            background-color: var(--pelco-yellow);
            color: #333;
            font-weight: bold;
            border: none;
        }
        .search-btn:hover {
            background-color: #aca9a9;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-pelco navbar-dark p-2 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center fw-bold" href="index.php">
            <img src="https://www.pelco3.org/images/logo.png" alt="PELCO III" width="50" class="me-2">
            PELCO III Consumer Management System 
        </a>

        <form class="d-flex mx-auto" style="width: 40%;" action="index.php" method="GET">
            <input class="form-control search-input ps-4" type="search" name="search" 
                   placeholder="Search Account # or Name..." 
                   value="<?php echo htmlspecialchars($searchTerm); ?>" required>
            <button class="btn search-btn px-4" type="submit">SEARCH</button>
            <?php if($searchTerm != ''): ?>
                <a href="index.php" class="btn btn-sm btn-link text-white ms-2 mt-1">Clear</a>
            <?php endif; ?>
        </form>

        <div class="d-flex align-items-center">
            <span class="text-white me-3 small">User: <?php echo $_SESSION['admin']; ?></span>
            <a href="logout.php" class="btn btn-sm btn-outline-light">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold" style="color: var(--pelco-green);">
                <?php echo $searchTerm != '' ? "Search Results for '$searchTerm'" : "Consumer Records"; ?>
            </h4>
        </div>
        <div class="col-md-6 text-end">
            <a href="archive.php" class="btn btn-outline-secondary me-2">Archive List</a>
            <a href="register.php" class="btn btn-action px-4">+ New Consumer</a>
        </div>
    </div>

  <div class="card shadow-sm border-0">
    <div class="card-header card-header-pelco py-3">
        <h6 class="mb-0 text-uppercase tracking-wider">Active Masterlist</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-uppercase">
                        <th class="ps-4">Account #</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Previous</th>
                        <th>Present</th>
                        <th>Usage (kWh)</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($consumers) > 0): ?>
                        <?php foreach ($consumers as $row): 
                            $statusClass = ($row['status'] == 1) ? 'led-active' : (($row['status'] == 4) ? 'led-inactive' : 'led-pullout'); 
                            $statusText = ($row['status'] == 1) ? 'Active' : (($row['status'] == 4) ? 'Inactive' : 'Pull-Out');
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?php echo htmlspecialchars($row['accountnumber']); ?></td>
                            <td class="text-dark fw-bold"><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></td>
                            <td>
                                <span class="status-led <?php echo $statusClass; ?>"></span>
                                <small class="fw-bold text-muted"><?php echo $statusText; ?></small>
                            </td>
                            
                            <td class="text-muted"><?php echo number_format($row['previous_reading'], 2); ?></td>
                            <td class="text-dark fw-bold"><?php echo number_format($row['present_reading'], 2); ?></td>
                            
                            <td class="fw-bold text-success"><?php echo number_format($row['kilowatthour'], 2); ?></td>
                            <td class="text-center">
                                <div class="btn-group shadow-sm">
                                    <a href="print.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-dark">Print</a>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-dark">Edit</a>
                                    <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Move to Archive?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted italic">
                                No consumer records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>