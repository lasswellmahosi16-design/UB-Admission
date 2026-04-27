<?php
require_once 'includes/db.php';
if (!isset($_SESSION['student_id'])) { header('Location: login.php'); exit; }

$sid = (int)$_SESSION['student_id'];
$app_id = (int)($_GET['app_id'] ?? 0);

// Verify application belongs to this student
$app = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM applications WHERE id=$app_id AND student_id=$sid"));
if (!$app) { header('Location: dashboard.php'); exit; }

$error = ''; $success = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $doc_type  = mysqli_real_escape_string($conn, $_POST['doc_type']);
    $file      = $_FILES['document'];
    $allowed   = ['pdf','jpg','jpeg','png'];
    $max_size  = 5 * 1024 * 1024; // 5MB

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'File upload error. Please try again.';
    } elseif (!in_array($ext, $allowed)) {
        $error = 'Only PDF, JPG, and PNG files are allowed.';
    } elseif ($file['size'] > $max_size) {
        $error = 'File size must not exceed 5MB.';
    } else {
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $new_name  = $doc_type . '_' . $sid . '_' . time() . '.' . $ext;
        $file_path = 'uploads/' . $new_name;
        $dest      = $upload_dir . $new_name;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $orig_name = mysqli_real_escape_string($conn, $file['name']);
            $fp        = mysqli_real_escape_string($conn, $file_path);
            mysqli_query($conn, "INSERT INTO documents (application_id, doc_type, file_name, file_path) VALUES ($app_id, '$doc_type', '$orig_name', '$fp')");
            $success = 'Document uploaded successfully!';
        } else {
            $error = 'Failed to save file. Check server permissions.';
        }
    }
}

// Handle delete
if (isset($_GET['delete_doc'])) {
    $doc_id = (int)$_GET['delete_doc'];
    $doc = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM documents WHERE id=$doc_id AND application_id=$app_id"));
    if ($doc) {
        @unlink(__DIR__ . '/' . $doc['file_path']);
        mysqli_query($conn, "DELETE FROM documents WHERE id=$doc_id");
        $success = 'Document deleted.';
    }
}

// Get existing documents
$docs = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM documents WHERE application_id=$app_id ORDER BY uploaded_at DESC"), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Documents | UB Admissions</title>
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
        <a href="application.php">Application</a>
        <a href="logout.php" style="color:rgba(255,255,255,0.6);">Logout</a>
    </div>
</nav>

<div class="page-body">
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:28px;">
        <a href="application.php?id=<?= $app_id ?>" style="color:var(--mid-gray); text-decoration:none; font-size:0.88rem;">← Back to Application</a>
    </div>

    <div class="page-header">
        <h1>📎 Upload Documents</h1>
        <p>Upload your certificates, national ID, and academic transcripts. Accepted formats: PDF, JPG, PNG (max 5MB each).</p>
    </div>

    <?php if ($error): ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start;">

        <!-- Upload Form -->
        <div class="card">
            <div class="card-header"><h2>Upload New Document</h2></div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Document Type <span class="required">*</span></label>
                        <select name="doc_type" class="form-control" required>
                            <option value="">-- Select Type --</option>
                            <option value="certificate">BGCSE Certificate</option>
                            <option value="national_id">National ID / Omang / Passport</option>
                            <option value="transcript">Academic Transcript</option>
                            <option value="other">Other Document</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>File <span class="required">*</span></label>
                        <div class="file-drop-zone" id="drop-zone" onclick="document.getElementById('fileInput').click()">
                            <input type="file" name="document" id="fileInput" accept=".pdf,.jpg,.jpeg,.png" style="display:none;" required onchange="showFileName(this)">
                            <div class="upload-icon">📄</div>
                            <p id="file-label">Drag & drop or <span class="browse-link">browse file</span></p>
                            <p style="font-size:0.75rem; margin-top:4px; color:#bbb;">PDF, JPG, PNG — max 5MB</p>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-blue" style="width:100%; justify-content:center;">
                        ⬆️ Upload Document
                    </button>
                </form>
            </div>
        </div>

        <!-- Required Documents Checklist -->
        <div>
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header"><h2>Required Documents</h2></div>
                <div class="card-body">
                    <?php
                    $doc_types = ['certificate'=>'BGCSE Certificate','national_id'=>'National ID / Omang','transcript'=>'Academic Transcript'];
                    foreach ($doc_types as $type => $label):
                        $uploaded = false;
                        foreach ($docs as $d) { if ($d['doc_type'] === $type) { $uploaded = true; break; } }
                    ?>
                    <div style="display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid var(--light-gray);">
                        <span style="font-size:1.3rem;"><?= $uploaded ? '✅' : '⭕' ?></span>
                        <div>
                            <div style="font-weight:600; font-size:0.88rem; color:var(--text-dark);"><?= $label ?></div>
                            <div style="font-size:0.78rem; color:<?= $uploaded ? 'var(--success)' : 'var(--mid-gray)' ?>;">
                                <?= $uploaded ? 'Uploaded' : 'Not yet uploaded' ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Uploaded Documents -->
    <?php if (count($docs) > 0): ?>
    <div class="card" style="margin-top:24px;">
        <div class="card-header"><h2>Uploaded Documents (<?= count($docs) ?>)</h2></div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrapper" style="border:none; border-radius:0;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Document Type</th>
                            <th>File Name</th>
                            <th>Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($docs as $i => $d): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td>
                            <?php
                            $type_labels = ['certificate'=>'📜 Certificate','national_id'=>'🪪 National ID','transcript'=>'📋 Transcript','other'=>'📄 Other'];
                            echo $type_labels[$d['doc_type']] ?? $d['doc_type'];
                            ?>
                        </td>
                        <td><?= htmlspecialchars($d['file_name']) ?></td>
                        <td><?= date('d M Y H:i', strtotime($d['uploaded_at'])) ?></td>
                        <td>
                            <a href="<?= htmlspecialchars($d['file_path']) ?>" target="_blank" class="btn btn-blue btn-sm">View</a>
                            <?php if (!$app['submitted']): ?>
                            <a href="?app_id=<?= $app_id ?>&delete_doc=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this document?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div style="margin-top:24px;">
        <a href="application.php?id=<?= $app_id ?>" class="btn btn-blue">← Back to Application</a>
    </div>
</div>

<footer class="footer">
    © <?= date('Y') ?> <span>University of Botswana</span> — Online Admissions Portal.
</footer>

<script>
function showFileName(input) {
    const label = document.getElementById('file-label');
    if (input.files && input.files[0]) {
        label.textContent = '📎 ' + input.files[0].name;
        label.style.color = 'var(--ub-blue)';
        label.style.fontWeight = '600';
    }
}

const dropZone = document.getElementById('drop-zone');
dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    const fi = document.getElementById('fileInput');
    fi.files = e.dataTransfer.files;
    showFileName(fi);
});
</script>
</body>
</html>
