<?php
// Student dashboard — shows application summary and quick links
require_once 'includes/db.php';
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$sid = (int)$_SESSION['student_id'];

// Fetch student details using their session ID
$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id=$sid"));

// Fetch all applications for this student, newest first
$apps = mysqli_query($conn, "SELECT * FROM applications WHERE student_id=$sid ORDER BY created_at DESC");
$app_count = mysqli_num_rows($apps);
$apps_arr = mysqli_fetch_all($apps, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | UB Admissions</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="navbar-brand">
        <div class="logo-circle">UB</div>
        <div class="brand-text">
            <div class="top">University of Botswana</div>
            <div class="sub">Online Admissions Portal</div>
        </div>
    </a>
    <div class="nav-links">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="profile.php">My Profile</a>
        <a href="application.php">Application</a>
        <a href="logout.php" style="color:rgba(255,255,255,0.6);">Logout</a>
    </div>
</nav>

<div class="page-body">

    <!-- Welcome Header -->
    <div style="background:white; border-radius:var(--radius); padding:28px 32px; margin-bottom:28px; border:1px solid var(--light-gray); box-shadow:var(--shadow-sm); display:flex; align-items:center; justify-content:space-between; gap:20px;">
        <div>
            <p style="color:var(--mid-gray); font-size:0.82rem; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px;">Welcome back,</p>
            <h1 style="font-family:'Playfair Display',serif; color:var(--ub-blue); font-size:1.8rem;"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h1>
            <p style="color:var(--mid-gray); font-size:0.88rem; margin-top:4px;">📧 <?= htmlspecialchars($student['email']) ?></p>
        </div>
        <a href="application.php" class="btn btn-primary">
            <?= $app_count > 0 ? 'View Application' : '+ New Application' ?>
        </a>
    </div>

    <!-- Stats -->
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card">
            <div class="stat-icon">📄</div>
            <div class="stat-value"><?= $app_count ?></div>
            <div class="stat-label">Application(s)</div>
        </div>
        <div class="stat-card">
            <?php
            $submitted = 0;
            foreach($apps_arr as $a) { if($a['submitted']) $submitted++; }
            ?>
            <div class="stat-icon">✅</div>
            <div class="stat-value"><?= $submitted ?></div>
            <div class="stat-label">Submitted</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📁</div>
            <?php
            $doc_count = 0;
            foreach($apps_arr as $a) {
                $dc = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM documents WHERE application_id=".(int)$a['id']));
                $doc_count += $dc['c'];
            }
            ?>
            <div class="stat-value"><?= $doc_count ?></div>
            <div class="stat-label">Documents Uploaded</div>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="card">
        <div class="card-header">
            <h2>My Applications</h2>
            <?php if ($app_count === 0): ?>
            <a href="application.php" class="btn btn-blue btn-sm">+ Start Application</a>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding:0;">
            <?php if (count($apps_arr) === 0): ?>
            <div style="padding:48px; text-align:center; color:var(--mid-gray);">
                <div style="font-size:3rem; margin-bottom:14px;">📋</div>
                <h3 style="color:var(--text-dark); margin-bottom:8px;">No Application Yet</h3>
                <p style="margin-bottom:20px;">You haven't started an application. Click below to begin.</p>
                <a href="application.php" class="btn btn-blue">Start Application</a>
            </div>
            <?php else: ?>
            <div class="table-wrapper" style="border:none; border-radius:0;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Programme</th>
                            <th>BGCSE Points</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($apps_arr as $i => $a): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($a['programme']) ?></td>
                            <td><?= $a['total_bgcse_points'] ?></td>
                            <td><span class="badge badge-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
                            <td><?= date('d M Y', strtotime($a['created_at'])) ?></td>
                            <td>
                                <a href="application.php?id=<?= $a['id'] ?>" class="btn btn-blue btn-sm">View</a>
                                <?php if (!$a['submitted']): ?>
                                <a href="documents.php?app_id=<?= $a['id'] ?>" class="btn btn-sm" style="background:var(--off-white); border:1px solid var(--light-gray); color:var(--text-body);">Docs</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Links -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-top:24px;">
        <a href="profile.php" style="text-decoration:none;">
            <div style="background:white; border-radius:var(--radius); padding:22px; border:1px solid var(--light-gray); display:flex; gap:14px; align-items:center; transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.boxShadow='none'">
                <span style="font-size:2rem;">👤</span>
                <div>
                    <div style="font-weight:600; color:var(--text-dark); margin-bottom:2px;">Update Profile</div>
                    <div style="font-size:0.82rem; color:var(--mid-gray);">Change personal info, phone, address</div>
                </div>
            </div>
        </a>
        <a href="application.php" style="text-decoration:none;">
            <div style="background:white; border-radius:var(--radius); padding:22px; border:1px solid var(--light-gray); display:flex; gap:14px; align-items:center; transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.boxShadow='none'">
                <span style="font-size:2rem;">📋</span>
                <div>
                    <div style="font-weight:600; color:var(--text-dark); margin-bottom:2px;">My Application</div>
                    <div style="font-size:0.82rem; color:var(--mid-gray);">Fill BGCSE results and programme</div>
                </div>
            </div>
        </a>
    </div>
</div>

<footer class="footer">
    © <?= date('Y') ?> <span>University of Botswana</span> — Online Admissions Portal.
</footer>
</body>
</html>
