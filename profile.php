<?php
require_once 'includes/db.php';
// Redirect to login if not logged in
if (!isset($_SESSION['student_id'])) { header('Location: login.php'); exit; }

$sid = (int)$_SESSION['student_id'];
// Fetch current student data to pre-fill the form
$student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id=$sid"));
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize all personal info fields
    $phone   = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $dob     = mysqli_real_escape_string($conn, $_POST['date_of_birth']);
    $gender  = mysqli_real_escape_string($conn, $_POST['gender']);
    $nat     = mysqli_real_escape_string($conn, trim($_POST['nationality']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $omang   = mysqli_real_escape_string($conn, trim($_POST['omang_passport']));
    $fname   = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $lname   = mysqli_real_escape_string($conn, trim($_POST['last_name']));

    // Handle optional password change — only runs if new_password field is filled
    $pass_sql = '';
    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $error = 'New passwords do not match.';
        } elseif (strlen($_POST['new_password']) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif (!password_verify($_POST['current_password'], $student['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            $new_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $pass_sql = ", password='$new_hash'";
        }
    }

    if (!$error) {
        // Update personal info only — email stays the same (it is the login identifier)
        $sql = "UPDATE students SET 
            first_name='$fname', last_name='$lname',
            phone='$phone', date_of_birth='$dob',
            gender='$gender', nationality='$nat',
            address='$address', omang_passport='$omang'
            $pass_sql
            WHERE id=$sid";

        if (mysqli_query($conn, $sql)) {
            // Update the name stored in session so navbar shows the new name
            $_SESSION['student_name'] = $fname . ' ' . $lname;
            $success = 'Profile updated successfully!';
            $student = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id=$sid"));
        } else {
            $error = 'Update failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | UB Admissions</title>
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
        <a href="profile.php" class="active">My Profile</a>
        <a href="application.php">Application</a>
        <a href="logout.php" style="color:rgba(255,255,255,0.6);">Logout</a>
    </div>
</nav>

<div class="page-body">
    <div class="page-header">
        <h1>👤 My Profile</h1>
        <p>Update your personal information. Note: academic qualifications can only be changed before submitting your application.</p>
    </div>

    <div class="alert alert-info">
        ℹ️ You can update all personal details here at any time — even after submitting your application.
        <strong>Academic qualifications (BGCSE results)</strong> are managed in the <a href="application.php">Application</a> page and are locked once submitted.
    </div>

    <?php if ($error): ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success">✅ <?= $success ?></div><?php endif; ?>

    <form method="POST" action="">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start;">

            <!-- Personal Info -->
            <div class="card">
                <div class="card-header"><h2>Personal Information</h2></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name <span class="required">*</span></label>
                            <input type="text" name="first_name" class="form-control"
                                   placeholder="e.g. Kagiso"
                                   value="<?= htmlspecialchars($student['first_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name <span class="required">*</span></label>
                            <input type="text" name="last_name" class="form-control"
                                   placeholder="e.g. Motse"
                                   value="<?= htmlspecialchars($student['last_name']) ?>" required>
                        </div>
                    </div>

                    <!-- Email is disabled — it cannot be changed as it is the login identifier -->
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" disabled style="background:var(--off-white); color:var(--mid-gray);">
                        <p class="form-hint">Email address cannot be changed</p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" class="form-control"
                                   placeholder="e.g. 71234567"
                                   value="<?= htmlspecialchars($student['phone']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Omang / Passport No.</label>
                            <input type="text" name="omang_passport" class="form-control"
                                   placeholder="e.g. 123456789"
                                   value="<?= htmlspecialchars($student['omang_passport']) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($student['date_of_birth']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" class="form-control">
                                <option value="">Select</option>
                                <option value="Male"   <?= $student['gender']==='Male'   ? 'selected':'' ?>>Male</option>
                                <option value="Female" <?= $student['gender']==='Female' ? 'selected':'' ?>>Female</option>
                                <option value="Other"  <?= $student['gender']==='Other'  ? 'selected':'' ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nationality</label>
                        <input type="text" name="nationality" class="form-control"
                               placeholder="e.g. Motswana"
                               value="<?= htmlspecialchars($student['nationality']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Postal Address</label>
                        <textarea name="address" class="form-control" rows="3"
                                  placeholder="e.g. Plot 123, Gaborone, Botswana"
                                  style="resize:vertical;"><?= htmlspecialchars($student['address']) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Password Change -->
            <div>
                <div class="card">
                    <div class="card-header"><h2>🔐 Change Password</h2></div>
                    <div class="card-body">
                        <p style="font-size:0.85rem; color:var(--mid-gray); margin-bottom:18px;">Leave blank if you don't want to change your password.</p>
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" class="form-control"
                                   placeholder="Enter your current password">
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control"
                                   placeholder="At least 6 characters"
                                   minlength="6">
                            <p class="form-hint">Minimum 6 characters</p>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control"
                                   placeholder="Repeat your new password">
                        </div>
                    </div>
                </div>

                <div class="card" style="margin-top:18px;">
                    <div class="card-header"><h2>📋 Account Info</h2></div>
                    <div class="card-body">
                        <div style="display:flex; flex-direction:column; gap:12px;">
                            <div style="display:flex; justify-content:space-between; font-size:0.88rem;">
                                <span style="color:var(--mid-gray);">Member Since</span>
                                <span style="font-weight:600;"><?= date('d M Y', strtotime($student['created_at'])) ?></span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:0.88rem;">
                                <span style="color:var(--mid-gray);">Email</span>
                                <span style="font-weight:600;"><?= htmlspecialchars($student['email']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top:24px; display:flex; gap:14px;">
            <button type="submit" class="btn btn-blue">💾 Save Changes</button>
            <a href="dashboard.php" class="btn btn-outline" style="color:var(--ub-blue); border-color:var(--ub-blue);">← Cancel</a>
        </div>
    </form>
</div>

<footer class="footer">
    © <?= date('Y') ?> <span>University of Botswana</span> — Online Admissions Portal.
</footer>
</body>
</html>
