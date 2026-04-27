<?php
require_once 'includes/db.php';
if (!isset($_SESSION['student_id'])) { header('Location: login.php'); exit; }

$sid = (int)$_SESSION['student_id'];

// Grade to points mapping
$grade_points = ['A*'=>9,'A'=>8,'B'=>7,'C'=>6,'D'=>5,'E'=>4,'U'=>0];

$subjects_list = [
    'English Language','Setswana','Mathematics','Physical Science',
    'Biology','Chemistry','Physics','History','Geography',
    'Agriculture','Business Studies','Computer Studies',
    'Design & Technology','Religious Education','Art','French','Home Economics'
];

$programmes = [
    'BSc General','BSc Computer Science','BSc Mathematics',
    'BSc Biology','BSc Chemistry','BSc Physics','Bachelor of Education (Primary)',
    'Bachelor of Education (Secondary)','BA Social Sciences','BA English',
    'BEng Civil Engineering','BEng Electrical Engineering',
    'BCom Accounting','BCom Management','Bachelor of Nursing',
    'BBA Management','BBA Marketing'
];

// Get or create application
$app = null;
if (isset($_GET['id'])) {
    $aid = (int)$_GET['id'];
    $app = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM applications WHERE id=$aid AND student_id=$sid"));
}

if (!$app) {
    $ex = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM applications WHERE student_id=$sid LIMIT 1"));
    if ($ex) { $app = $ex; } 
}

$error = ''; $success = '';

// Handle SAVE/UPDATE application (only if not submitted)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_application'])) {
    $programme = mysqli_real_escape_string($conn, $_POST['programme']);

    if ($app && $app['submitted']) {
        $error = 'Your application has been submitted and academic qualifications are locked.';
    } else {
        // Save or create application
        if (!$app) {
            mysqli_query($conn, "INSERT INTO applications (student_id, programme) VALUES ($sid, '$programme')");
            $app_id = mysqli_insert_id($conn);
            $app = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM applications WHERE id=$app_id"));
        } else {
            mysqli_query($conn, "UPDATE applications SET programme='$programme' WHERE id={$app['id']}");
            $app['programme'] = $programme;
        }

        $app_id = $app['id'];

        // Delete old results
        mysqli_query($conn, "DELETE FROM bgcse_results WHERE application_id=$app_id");

        // Insert new results
        $total_points = 0;
        if (isset($_POST['subjects'])) {
            foreach ($_POST['subjects'] as $subject) {
                $subj  = mysqli_real_escape_string($conn, $subject['name']);
                $grade = mysqli_real_escape_string($conn, $subject['grade']);
                if ($subj && $grade && isset($grade_points[$grade])) {
                    $pts = $grade_points[$grade];
                    $total_points += $pts;
                    mysqli_query($conn, "INSERT INTO bgcse_results (application_id, subject, grade, points) VALUES ($app_id,'$subj','$grade',$pts)");
                }
            }
        }

        // Update total points
        mysqli_query($conn, "UPDATE applications SET total_bgcse_points=$total_points WHERE id=$app_id");
        $success = 'Application saved successfully!';
        $app = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM applications WHERE id=$app_id"));
    }
}

// Handle SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    $app_id = (int)$app['id'];

    // Check documents uploaded
    $doc_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM documents WHERE application_id=$app_id"));
    $res_check  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM bgcse_results WHERE application_id=$app_id"));

    if ($res_check['c'] == 0) {
        $error = 'Please add at least one BGCSE result before submitting.';
    } elseif ($doc_check['c'] == 0) {
        $error = 'Please upload at least one document before submitting. <a href="documents.php?app_id='.$app_id.'">Upload Documents</a>';
    } else {
        mysqli_query($conn, "UPDATE applications SET submitted=1, status='submitted', submitted_at=NOW() WHERE id=$app_id");
        $success = '🎉 Application submitted successfully! Your qualifications are now locked.';
        $app = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM applications WHERE id=$app_id"));
    }
}

// Get existing results
$existing_results = [];
if ($app) {
    $res = mysqli_query($conn, "SELECT * FROM bgcse_results WHERE application_id={$app['id']}");
    while ($r = mysqli_fetch_assoc($res)) {
        $existing_results[] = $r;
    }
}

$locked = $app && $app['submitted'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Form | UB Admissions</title>
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
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">My Profile</a>
        <a href="application.php" class="active">Application</a>
        <a href="logout.php" style="color:rgba(255,255,255,0.6);">Logout</a>
    </div>
</nav>

<div class="page-body">
    <div class="page-header">
        <h1>My Application</h1>
        <p>Fill in your BGCSE results and select your preferred programme</p>
    </div>

    <!-- Steps -->
    <div class="steps">
        <div class="step <?= $app ? 'done' : 'active' ?>">
            <div class="step-num"><?= $app ? '✓' : '1' ?></div>
            <div class="step-label">Fill Application</div>
        </div>
        <div class="step-line"></div>
        <div class="step <?= ($app && count($existing_results)>0) ? 'done' : ($app ? 'active' : '') ?>">
            <div class="step-num"><?= ($app && count($existing_results)>0) ? '✓' : '2' ?></div>
            <div class="step-label">BGCSE Results</div>
        </div>
        <div class="step-line"></div>
        <div class="step <?= ($app && mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM documents WHERE application_id=".($app?$app['id']:0)))['c'] > 0) ? 'done' : '' ?>">
            <div class="step-num">3</div>
            <div class="step-label">Upload Documents</div>
        </div>
        <div class="step-line"></div>
        <div class="step <?= $locked ? 'done' : '' ?>">
            <div class="step-num"><?= $locked ? '✓' : '4' ?></div>
            <div class="step-label">Submit</div>
        </div>
    </div>

    <?php if ($error): ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>

    <?php if ($locked): ?>
    <div class="alert alert-info">
        🔒 Your application has been <strong>submitted</strong> on <?= date('d M Y', strtotime($app['submitted_at'])) ?>. 
        Academic qualifications are locked and cannot be changed. You can still update your personal information in <a href="profile.php">My Profile</a>.
        <br><strong>Status:</strong> <span class="badge badge-<?= $app['status'] ?>"><?= ucfirst($app['status']) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" action="">
        <!-- Programme Selection -->
        <div class="card" style="margin-bottom:24px;">
            <div class="card-header">
                <h2>🎓 Programme Selection</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Preferred Programme <span class="required">*</span></label>
                    <select name="programme" class="form-control" <?= $locked ? 'disabled' : '' ?> required>
                        <option value="">-- Select a Programme --</option>
                        <?php foreach ($programmes as $p): ?>
                        <option value="<?= $p ?>" <?= (($app['programme'] ?? '') === $p) ? 'selected' : '' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- BGCSE Results -->
        <div class="card" style="margin-bottom:24px;">
            <div class="card-header">
                <h2>📊 BGCSE Results</h2>
                <?php if (!$locked): ?>
                <button type="button" class="btn btn-blue btn-sm" onclick="addSubjectRow()">+ Add Subject</button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($locked): ?>
                <!-- Read-only view -->
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>#</th><th>Subject</th><th>Grade</th><th>Points</th></tr></thead>
                        <tbody>
                        <?php foreach ($existing_results as $i => $r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($r['subject']) ?></td>
                            <td><strong><?= $r['grade'] ?></strong></td>
                            <td><?= $r['points'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr style="background:var(--off-white);">
                            <td colspan="3"><strong>Total BGCSE Points</strong></td>
                            <td><strong style="color:var(--ub-blue); font-size:1.1rem;"><?= $app['total_bgcse_points'] ?></strong></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <!-- Editable rows -->
                <div id="subjects-container">
                    <?php if (count($existing_results) > 0): ?>
                        <?php foreach ($existing_results as $i => $r): ?>
                        <div class="subject-row" id="row_<?= $i ?>">
                            <div class="form-group" style="margin:0;">
                                <select name="subjects[<?= $i ?>][name]" class="form-control" onchange="calcTotal()">
                                    <option value="">-- Select Subject --</option>
                                    <?php foreach($subjects_list as $s): ?>
                                    <option value="<?= $s ?>" <?= ($r['subject']==$s)?'selected':'' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <select name="subjects[<?= $i ?>][grade]" class="form-control grade-select" onchange="calcTotal()">
                                    <option value="">Grade</option>
                                    <?php foreach($grade_points as $g=>$p): ?>
                                    <option value="<?= $g ?>" <?= ($r['grade']==$g)?'selected':'' ?>><?= $g ?> (<?= $p ?>pts)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="button" onclick="removeRow(this)" style="background:var(--danger); color:white; border:none; border-radius:var(--radius-sm); padding:8px 12px; cursor:pointer; font-size:0.9rem;">✕</button>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div class="subject-row" id="row_0">
                        <div class="form-group" style="margin:0;">
                            <select name="subjects[0][name]" class="form-control" onchange="calcTotal()">
                                <option value="">-- Select Subject --</option>
                                <?php foreach($subjects_list as $s): ?>
                                <option value="<?= $s ?>"><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <select name="subjects[0][grade]" class="form-control grade-select" onchange="calcTotal()">
                                <option value="">Grade</option>
                                <?php foreach($grade_points as $g=>$p): ?>
                                <option value="<?= $g ?>"><?= $g ?> (<?= $p ?>pts)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="button" onclick="removeRow(this)" style="background:var(--danger); color:white; border:none; border-radius:var(--radius-sm); padding:8px 12px; cursor:pointer;">✕</button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Total Points Display -->
                <div style="margin-top:18px; padding:14px 18px; background:var(--off-white); border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:space-between; border:1px solid var(--light-gray);">
                    <span style="font-weight:600; color:var(--text-dark);">Total BGCSE Points:</span>
                    <span id="total-points" style="font-family:'Playfair Display',serif; font-size:1.6rem; color:var(--ub-blue); font-weight:700;">0</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Documents reminder -->
        <?php if ($app && !$locked): ?>
        <div class="alert alert-warning">
            📎 Remember to <a href="documents.php?app_id=<?= $app['id'] ?>" style="font-weight:600;">upload your documents</a> (certificates, ID/Omang) before submitting.
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <?php if (!$locked): ?>
        <div style="display:flex; gap:14px; flex-wrap:wrap;">
            <button type="submit" name="save_application" class="btn btn-blue">💾 Save Application</button>
            <?php if ($app): ?>
            <a href="documents.php?app_id=<?= $app['id'] ?>" class="btn btn-outline" style="color:var(--ub-blue); border-color:var(--ub-blue);">📎 Upload Documents</a>
            <button type="submit" name="submit_application" class="btn btn-primary" onclick="return confirm('Are you sure you want to submit? Your academic qualifications will be LOCKED and cannot be changed after submission.')">
                ✅ Submit Application
            </button>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div style="display:flex; gap:14px;">
            <a href="documents.php?app_id=<?= $app['id'] ?>" class="btn btn-blue">📎 View Documents</a>
            <a href="dashboard.php" class="btn btn-outline" style="color:var(--ub-blue); border-color:var(--ub-blue);">← Back to Dashboard</a>
        </div>
        <?php endif; ?>
    </form>
</div>

<footer class="footer">
    © <?= date('Y') ?> <span>University of Botswana</span> — Online Admissions Portal.
</footer>

<script>
const subjects_list = <?= json_encode($subjects_list) ?>;
const grade_points  = <?= json_encode($grade_points) ?>;
let rowCount = <?= max(count($existing_results), 1) ?>;

function addSubjectRow() {
    const container = document.getElementById('subjects-container');
    const row = document.createElement('div');
    row.className = 'subject-row';
    row.id = 'row_' + rowCount;

    let subjectOptions = '<option value="">-- Select Subject --</option>';
    subjects_list.forEach(s => { subjectOptions += `<option value="${s}">${s}</option>`; });

    let gradeOptions = '<option value="">Grade</option>';
    Object.entries(grade_points).forEach(([g,p]) => { gradeOptions += `<option value="${g}">${g} (${p}pts)</option>`; });

    row.innerHTML = `
        <div class="form-group" style="margin:0;">
            <select name="subjects[${rowCount}][name]" class="form-control" onchange="calcTotal()">${subjectOptions}</select>
        </div>
        <div class="form-group" style="margin:0;">
            <select name="subjects[${rowCount}][grade]" class="form-control grade-select" onchange="calcTotal()">${gradeOptions}</select>
        </div>
        <button type="button" onclick="removeRow(this)" style="background:var(--danger);color:white;border:none;border-radius:var(--radius-sm);padding:8px 12px;cursor:pointer;">✕</button>
    `;
    container.appendChild(row);
    rowCount++;
    calcTotal();
}

function removeRow(btn) {
    const row = btn.closest('.subject-row');
    if (document.querySelectorAll('.subject-row').length > 1) {
        row.remove();
        calcTotal();
    } else {
        alert('You must have at least one subject.');
    }
}

function calcTotal() {
    let total = 0;
    document.querySelectorAll('.grade-select').forEach(sel => {
        if (sel.value && grade_points[sel.value] !== undefined) {
            total += grade_points[sel.value];
        }
    });
    document.getElementById('total-points').textContent = total;
}

// Initial calc
calcTotal();
</script>
</body>
</html>
