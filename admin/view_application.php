<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$app_id = (int)($_GET['id'] ?? 0);

$app = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT a.*, s.first_name, s.last_name, s.email, s.phone, s.date_of_birth, s.gender, s.nationality, s.omang_passport, s.address, s.created_at as reg_date
    FROM applications a
    JOIN students s ON a.student_id = s.id
    WHERE a.id = $app_id
"));

if (!$app) { header('Location: applications.php'); exit; }

$results = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM bgcse_results WHERE application_id=$app_id ORDER BY subject"), MYSQLI_ASSOC);
$docs    = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM documents WHERE application_id=$app_id"), MYSQLI_ASSOC);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE applications SET status='$new_status' WHERE id=$app_id");
    $app['status'] = $new_status;
    $msg = 'Status updated to: ' . ucfirst($new_status);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Details | Admin</title>
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
    <div style="margin-bottom:20px;">
        <a href="applications.php" style="color:var(--mid-gray); text-decoration:none; font-size:0.88rem;">← Back to Applications</a>
    </div>

    <?php if (isset($msg)): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Header -->
    <div style="background:white; border-radius:var(--radius); padding:28px 32px; margin-bottom:24px; border:1px solid var(--light-gray); box-shadow:var(--shadow-sm); display:flex; justify-content:space-between; align-items:center; gap:20px; flex-wrap:wrap;">
        <div>
            <h1 style="font-family:'Playfair Display',serif; color:var(--ub-blue); font-size:1.7rem; margin-bottom:4px;">
                <?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?>
            </h1>
            <p style="color:var(--mid-gray); font-size:0.88rem;"><?= htmlspecialchars($app['email']) ?> &nbsp;·&nbsp; <?= htmlspecialchars($app['programme']) ?></p>
            <div style="margin-top:10px;">
                <span class="badge badge-<?= $app['status'] ?>" style="font-size:0.85rem; padding:5px 14px;"><?= ucfirst($app['status']) ?></span>
                <?php if ($app['submitted']): ?>
                <span style="color:var(--mid-gray); font-size:0.82rem; margin-left:10px;">Submitted <?= date('d M Y', strtotime($app['submitted_at'])) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div style="text-align:center;">
            <div style="font-size:0.78rem; color:var(--mid-gray); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">BGCSE Points</div>
            <div style="font-family:'Playfair Display',serif; font-size:3rem; color:var(--ub-blue); font-weight:700; line-height:1;"><?= $app['total_bgcse_points'] ?></div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">

        <!-- Personal Details -->
        <div class="card">
            <div class="card-header"><h2>👤 Personal Details</h2></div>
            <div class="card-body">
                <?php
                $fields = [
                    'Full Name' => $app['first_name'].' '.$app['last_name'],
                    'Email' => $app['email'],
                    'Phone' => $app['phone'] ?: 'N/A',
                    'Omang / Passport' => $app['omang_passport'] ?: 'N/A',
                    'Date of Birth' => $app['date_of_birth'] ? date('d M Y', strtotime($app['date_of_birth'])) : 'N/A',
                    'Gender' => $app['gender'] ?: 'N/A',
                    'Nationality' => $app['nationality'] ?: 'N/A',
                    'Address' => $app['address'] ?: 'N/A',
                    'Registered' => date('d M Y', strtotime($app['reg_date'])),
                ];
                foreach ($fields as $label => $val): ?>
                <div style="display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px solid var(--light-gray); font-size:0.88rem;">
                    <span style="color:var(--mid-gray);"><?= $label ?></span>
                    <span style="font-weight:500; text-align:right; max-width:200px;"><?= htmlspecialchars($val) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Update Status & Documents -->
        <div>
            <div class="card" style="margin-bottom:18px;">
                <div class="card-header"><h2>⚙️ Update Application Status</h2></div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Application Status</label>
                            <select name="status" class="form-control">
                                <?php foreach(['draft','submitted','accepted','rejected','waitlisted'] as $s): ?>
                                <option value="<?= $s ?>" <?= $app['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-blue" style="width:100%; justify-content:center;">Update Status</button>
                    </form>
                </div>
            </div>

            <!-- Documents -->
            <div class="card">
                <div class="card-header"><h2>📎 Uploaded Documents (<?= count($docs) ?>)</h2></div>
                <div class="card-body" style="padding:0;">
                    <?php if (empty($docs)): ?>
                    <div style="padding:20px; text-align:center; color:var(--mid-gray); font-size:0.88rem;">No documents uploaded yet</div>
                    <?php else: ?>
                    <?php
                    $type_labels = ['certificate'=>'📜 Certificate','national_id'=>'🪪 National ID','transcript'=>'📋 Transcript','other'=>'📄 Other'];
                    foreach ($docs as $d): ?>
                    <div style="padding:12px 18px; border-bottom:1px solid var(--light-gray); display:flex; justify-content:space-between; align-items:center; font-size:0.88rem;">
                        <div>
                            <div style="font-weight:600;"><?= $type_labels[$d['doc_type']] ?? $d['doc_type'] ?></div>
                            <div style="font-size:0.78rem; color:var(--mid-gray);"><?= htmlspecialchars($d['file_name']) ?></div>
                        </div>
                        <a href="../<?= htmlspecialchars($d['file_path']) ?>" target="_blank" class="btn btn-blue btn-sm">View</a>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- BGCSE Results -->
    <div class="card">
        <div class="card-header">
            <h2>📊 BGCSE Results</h2>
            <div style="font-family:'Playfair Display',serif; font-size:1.4rem; color:var(--ub-blue);">
                Total: <strong><?= $app['total_bgcse_points'] ?></strong> pts
            </div>
        </div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($results)): ?>
            <div style="padding:30px; text-align:center; color:var(--mid-gray);">No BGCSE results entered</div>
            <?php else: ?>
            <div class="table-wrapper" style="border:none; border-radius:0;">
                <table>
                    <thead>
                        <tr><th>#</th><th>Subject</th><th>Grade</th><th>Points</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($results as $i => $r): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($r['subject']) ?></td>
                        <td><strong><?= $r['grade'] ?></strong></td>
                        <td><?= $r['points'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="background:var(--off-white);">
                        <td colspan="3"><strong>Total Points</strong></td>
                        <td><strong style="color:var(--ub-blue); font-size:1.1rem;"><?= $app['total_bgcse_points'] ?></strong></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="footer">
    © <?= date('Y') ?> <span>University of Botswana</span> — Admissions Administration.
</footer>
</body>
</html>
