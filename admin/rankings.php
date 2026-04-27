<?php
require_once '../includes/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$programmes_available = mysqli_fetch_all(mysqli_query($conn, "SELECT DISTINCT programme FROM applications WHERE submitted=1 ORDER BY programme"), MYSQLI_ASSOC);

$selected_programme = isset($_GET['programme']) ? $_GET['programme'] : 'BSc General';

$prog_escaped = mysqli_real_escape_string($conn, $selected_programme);

// Fetch ranked students for selected programme
$ranked = mysqli_fetch_all(mysqli_query($conn, "
    SELECT a.id as app_id, a.total_bgcse_points, a.status, a.submitted_at,
           s.first_name, s.last_name, s.email, s.phone, s.omang_passport,
           a.programme
    FROM applications a
    JOIN students s ON a.student_id = s.id
    WHERE a.programme = '$prog_escaped'
      AND a.submitted = 1
    ORDER BY a.total_bgcse_points DESC
"), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Rankings | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        @media print {
            .navbar, .no-print { display: none !important; }
            body { background: white; }
            .card { box-shadow: none; border: 1px solid #ddd; }
        }
    </style>
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
        <a href="applications.php">Applications</a>
        <a href="rankings.php" class="active">Rankings</a>
        <a href="logout.php" style="color:rgba(255,255,255,0.6);">Logout</a>
    </div>
</nav>

<div class="page-body">
    <div class="page-header">
        <h1>🏆 Student Rankings</h1>
        <p>Automatic ranking of prospective students by BGCSE points (descending). Use this to assist in student selection.</p>
    </div>

    <!-- Programme Selector -->
    <div class="card no-print" style="margin-bottom:24px;">
        <div class="card-body" style="padding:18px 24px;">
            <form method="GET" style="display:flex; gap:14px; align-items:flex-end; flex-wrap:wrap;">
                <div class="form-group" style="margin:0; min-width:260px; flex:1;">
                    <label style="font-size:0.85rem;">Select Programme to Rank</label>
                    <select name="programme" class="form-control">
                        <option value="BSc General" <?= $selected_programme==='BSc General'?'selected':'' ?>>BSc General</option>
                        <?php foreach($programmes_available as $p): ?>
                            <?php if ($p['programme'] !== 'BSc General'): ?>
                            <option value="<?= htmlspecialchars($p['programme']) ?>" <?= $selected_programme===$p['programme']?'selected':'' ?>><?= htmlspecialchars($p['programme']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-blue">Generate Rankings</button>
                <button type="button" onclick="window.print()" class="btn btn-outline" style="color:var(--ub-blue); border-color:var(--ub-blue);">🖨️ Print</button>
            </form>
        </div>
    </div>

    <!-- Rankings Table -->
    <div class="card">
        <div class="card-header">
            <div>
                <h2>📊 <?= htmlspecialchars($selected_programme) ?> — Rankings</h2>
                <p style="font-size:0.82rem; color:var(--mid-gray); margin-top:3px;"><?= count($ranked) ?> submitted applicant(s) · Ranked by BGCSE total points (highest first)</p>
            </div>
            <?php if (count($ranked) > 0): ?>
            <div style="text-align:right;">
                <div style="font-size:0.78rem; color:var(--mid-gray);">Highest Score</div>
                <div style="font-family:'Playfair Display',serif; font-size:1.8rem; color:var(--ub-blue); font-weight:700;"><?= $ranked[0]['total_bgcse_points'] ?><span style="font-size:0.9rem; font-family:'DM Sans',sans-serif; color:var(--mid-gray);">pts</span></div>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($ranked)): ?>
            <div style="padding:60px; text-align:center; color:var(--mid-gray);">
                <div style="font-size:3rem; margin-bottom:16px;">📋</div>
                <h3 style="color:var(--text-dark); margin-bottom:8px;">No Submitted Applications</h3>
                <p>There are no submitted applications for <strong><?= htmlspecialchars($selected_programme) ?></strong> yet.</p>
            </div>
            <?php else: ?>
            <div class="table-wrapper" style="border:none; border-radius:0;">
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Omang / Passport</th>
                            <th>BGCSE Points</th>
                            <th>App. Status</th>
                            <th>Submitted</th>
                            <th class="no-print">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ranked as $i => $r): ?>
                    <tr class="<?= $i < 3 ? 'rank-1' : '' ?>">
                        <td>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <?php if ($i === 0): ?>
                                    <span class="rank-medal">🥇</span>
                                <?php elseif ($i === 1): ?>
                                    <span class="rank-medal">🥈</span>
                                <?php elseif ($i === 2): ?>
                                    <span class="rank-medal">🥉</span>
                                <?php else: ?>
                                    <span style="color:var(--mid-gray); font-weight:600; font-size:0.9rem;">#<?= $i+1 ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></strong>
                        </td>
                        <td style="font-size:0.82rem; color:var(--mid-gray);"><?= htmlspecialchars($r['email']) ?></td>
                        <td style="font-size:0.85rem;"><?= htmlspecialchars($r['omang_passport'] ?: 'N/A') ?></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="height:8px; background:var(--light-gray); border-radius:100px; width:80px; overflow:hidden;">
                                    <div style="height:100%; background:var(--ub-blue); width:<?= min(100, ($r['total_bgcse_points']/90)*100) ?>%; border-radius:100px;"></div>
                                </div>
                                <strong style="color:var(--ub-blue); font-size:1rem;"><?= $r['total_bgcse_points'] ?></strong>
                            </div>
                        </td>
                        <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                        <td style="font-size:0.82rem;"><?= date('d M Y', strtotime($r['submitted_at'])) ?></td>
                        <td class="no-print">
                            <a href="view_application.php?id=<?= $r['app_id'] ?>" class="btn btn-blue btn-sm">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary Stats -->
            <?php
            $pts = array_column($ranked, 'total_bgcse_points');
            $avg = round(array_sum($pts) / count($pts), 1);
            $max = max($pts);
            $min = min($pts);
            ?>
            <div style="padding:18px 24px; background:var(--off-white); display:flex; gap:32px; border-top:1px solid var(--light-gray);">
                <div><div style="font-size:0.75rem; color:var(--mid-gray); text-transform:uppercase; letter-spacing:0.5px;">Total Applicants</div><div style="font-weight:700; color:var(--ub-blue); font-size:1.1rem;"><?= count($ranked) ?></div></div>
                <div><div style="font-size:0.75rem; color:var(--mid-gray); text-transform:uppercase; letter-spacing:0.5px;">Highest Score</div><div style="font-weight:700; color:var(--ub-blue); font-size:1.1rem;"><?= $max ?> pts</div></div>
                <div><div style="font-size:0.75rem; color:var(--mid-gray); text-transform:uppercase; letter-spacing:0.5px;">Lowest Score</div><div style="font-weight:700; color:var(--ub-blue); font-size:1.1rem;"><?= $min ?> pts</div></div>
                <div><div style="font-size:0.75rem; color:var(--mid-gray); text-transform:uppercase; letter-spacing:0.5px;">Average Score</div><div style="font-weight:700; color:var(--ub-blue); font-size:1.1rem;"><?= $avg ?> pts</div></div>
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
