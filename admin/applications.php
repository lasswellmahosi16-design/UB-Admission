<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $app_id    = (int)$_POST['app_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
    mysqli_query($conn, "UPDATE applications SET status='$new_status' WHERE id=$app_id");
    $success = 'Status updated!';
}

// Filters
$filter_status  = isset($_GET['status'])    ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$filter_prog    = isset($_GET['programme']) ? mysqli_real_escape_string($conn, $_GET['programme']) : '';
$search         = isset($_GET['search'])    ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$where = [];
if ($filter_status)  $where[] = "a.status='$filter_status'";
if ($filter_prog)    $where[] = "a.programme='$filter_prog'";
if ($search)         $where[] = "(s.first_name LIKE '%$search%' OR s.last_name LIKE '%$search%' OR s.email LIKE '%$search%')";

$where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$apps = mysqli_fetch_all(mysqli_query($conn, "
    SELECT a.*, s.first_name, s.last_name, s.email, s.phone
    FROM applications a 
    JOIN students s ON a.student_id = s.id 
    $where_sql
    ORDER BY a.created_at DESC
"), MYSQLI_ASSOC);

// Get programmes for filter
$progs = mysqli_fetch_all(mysqli_query($conn, "SELECT DISTINCT programme FROM applications ORDER BY programme"), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Applications | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="navbar-brand">
        <div class="logo-circle">UB</div>
        <div class="brand-text">
            <div class="top">University of Botswana</div>
            <div class="sub">Admin — Admissions Portal</div>
        </div>
    </a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="applications.php" class="active">Applications</a>
        <a href="rankings.php">Rankings</a>
        <a href="logout.php" style="color:rgba(255,255,255,0.6);">Logout</a>
    </div>
</nav>

<div class="page-body">
    <div class="page-header">
        <h1>📋 All Applications</h1>
        <p>Manage and review student applications. Update statuses and view details.</p>
    </div>

    <?php if (isset($success)): ?>
    <div class="alert alert-success">✅ <?= $success ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card" style="margin-bottom:24px;">
        <div class="card-body" style="padding:18px 24px;">
            <form method="GET" action="">
                <div style="display:flex; gap:14px; flex-wrap:wrap; align-items:flex-end;">
                    <div class="form-group" style="margin:0; flex:1; min-width:200px;">
                        <label style="font-size:0.8rem;">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Name or email..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    <div class="form-group" style="margin:0; min-width:160px;">
                        <label style="font-size:0.8rem;">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <?php foreach(['draft','submitted','accepted','rejected','waitlisted'] as $s): ?>
                            <option value="<?= $s ?>" <?= $filter_status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0; min-width:200px;">
                        <label style="font-size:0.8rem;">Programme</label>
                        <select name="programme" class="form-control">
                            <option value="">All Programmes</option>
                            <?php foreach($progs as $p): ?>
                            <option value="<?= htmlspecialchars($p['programme']) ?>" <?= $filter_prog===$p['programme']?'selected':'' ?>><?= htmlspecialchars($p['programme']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-blue">Filter</button>
                    <a href="applications.php" class="btn btn-outline" style="color:var(--ub-blue); border-color:var(--ub-blue);">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Applications (<?= count($apps) ?>)</h2>
            <a href="rankings.php" class="btn btn-primary btn-sm">🏆 View Rankings</a>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrapper" style="border:none; border-radius:0;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Programme</th>
                            <th>BGCSE</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Change Status</th>
                            <th>View</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($apps as $i => $a): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td>
                            <div style="font-weight:600; font-size:0.88rem;"><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></div>
                            <div style="font-size:0.78rem; color:var(--mid-gray);"><?= htmlspecialchars($a['email']) ?></div>
                        </td>
                        <td style="font-size:0.85rem;"><?= htmlspecialchars($a['programme']) ?></td>
                        <td><strong><?= $a['total_bgcse_points'] ?></strong></td>
                        <td><span class="badge badge-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
                        <td style="font-size:0.82rem;">
                            <?= $a['submitted'] ? date('d M Y', strtotime($a['submitted_at'])) : '<span style="color:var(--mid-gray)">Not yet</span>' ?>
                        </td>
                        <td>
                            <form method="POST" action="" style="display:flex; gap:6px;">
                                <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
                                <select name="new_status" class="form-control" style="padding:5px 8px; font-size:0.78rem; height:auto; width:110px;">
                                    <?php foreach(['draft','submitted','accepted','rejected','waitlisted'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $a['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-blue btn-sm" style="white-space:nowrap;">Set</button>
                            </form>
                        </td>
                        <td>
                            <a href="view_application.php?id=<?= $a['id'] ?>" class="btn btn-blue btn-sm">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($apps)): ?>
                    <tr><td colspan="8" style="text-align:center; padding:40px; color:var(--mid-gray);">No applications found</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    © <?= date('Y') ?> <span>University of Botswana</span> — Admissions Administration.
</footer>
</body>
</html>
