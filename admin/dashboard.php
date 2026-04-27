<?php
// Admin dashboard — shows summary stats and recent applications
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

// Stats
$total_apps    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM applications"))['c'];
$submitted     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM applications WHERE submitted=1"))['c'];
$total_students= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM students"))['c'];
$accepted      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM applications WHERE status='accepted'"))['c'];

// Recent applications
$recent = mysqli_fetch_all(mysqli_query($conn, "
    SELECT a.*, s.first_name, s.last_name, s.email 
    FROM applications a 
    JOIN students s ON a.student_id = s.id 
    ORDER BY a.created_at DESC LIMIT 10
"), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | UB Admissions</title>
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
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="applications.php">Applications</a>
        <a href="rankings.php">Rankings</a>
        <a href="logout.php" style="color:rgba(255,255,255,0.6);">Logout</a>
    </div>
</nav>

<div style="background:var(--ub-blue); padding:28px 1.5rem 44px;">
    <div style="max-width:1100px; margin:0 auto; color:white;">
        <p style="opacity:0.65; font-size:0.82rem; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px;">Logged in as</p>
        <h1 style="font-family:'Playfair Display',serif; font-size:1.7rem;"><?= htmlspecialchars($_SESSION['admin_name']) ?></h1>
    </div>
</div>

<div style="max-width:1100px; margin:-24px auto 0; padding:0 1.5rem;">

    <!-- Stats -->
    <div class="stats-grid" style="margin-bottom:32px;">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-value"><?= $total_students ?></div>
            <div class="stat-label">Registered Students</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-value"><?= $total_apps ?></div>
            <div class="stat-label">Total Applications</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-value"><?= $submitted ?></div>
            <div class="stat-label">Submitted</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎓</div>
            <div class="stat-value"><?= $accepted ?></div>
            <div class="stat-label">Accepted</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:28px;">
        <a href="applications.php" style="text-decoration:none;">
            <div style="background:white; border-radius:var(--radius); padding:20px; border:1px solid var(--light-gray); display:flex; gap:14px; align-items:center; transition:box-shadow 0.2s; cursor:pointer;" onmouseover="this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.boxShadow='none'">
                <span style="font-size:2rem;">📋</span>
                <div>
                    <div style="font-weight:600; color:var(--text-dark);">Manage Applications</div>
                    <div style="font-size:0.78rem; color:var(--mid-gray);">View, approve, reject</div>
                </div>
            </div>
        </a>
        <a href="rankings.php" style="text-decoration:none;">
            <div style="background:white; border-radius:var(--radius); padding:20px; border:1px solid var(--light-gray); display:flex; gap:14px; align-items:center; transition:box-shadow 0.2s; cursor:pointer;" onmouseover="this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.boxShadow='none'">
                <span style="font-size:2rem;">🏆</span>
                <div>
                    <div style="font-weight:600; color:var(--text-dark);">Student Rankings</div>
                    <div style="font-size:0.78rem; color:var(--mid-gray);">BGCSE points ranking</div>
                </div>
            </div>
        </a>
        <a href="applications.php?status=submitted" style="text-decoration:none;">
            <div style="background:white; border-radius:var(--radius); padding:20px; border:1px solid var(--light-gray); display:flex; gap:14px; align-items:center; transition:box-shadow 0.2s; cursor:pointer;" onmouseover="this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.boxShadow='none'">
                <span style="font-size:2rem;">⏳</span>
                <div>
                    <div style="font-weight:600; color:var(--text-dark);">Pending Review</div>
                    <div style="font-size:0.78rem; color:var(--mid-gray);"><?= $submitted - $accepted ?> awaiting decision</div>
                </div>
            </div>
        </a>
    </div>

    <!-- Recent Applications -->
    <div class="card" style="margin-bottom:40px;">
        <div class="card-header">
            <h2>Recent Applications</h2>
            <a href="applications.php" class="btn btn-blue btn-sm">View All</a>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrapper" style="border:none; border-radius:0;">
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Programme</th>
                            <th>BGCSE Points</th>
                            <th>Status</th>
                            <th>Applied</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recent as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></td>
                        <td style="font-size:0.82rem;"><?= htmlspecialchars($a['email']) ?></td>
                        <td><?= htmlspecialchars($a['programme']) ?></td>
                        <td><strong><?= $a['total_bgcse_points'] ?></strong></td>
                        <td><span class="badge badge-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
                        <td><?= date('d M Y', strtotime($a['created_at'])) ?></td>
                        <td><a href="view_application.php?id=<?= $a['id'] ?>" class="btn btn-blue btn-sm">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recent)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:30px; color:var(--mid-gray);">No applications yet</td></tr>
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
