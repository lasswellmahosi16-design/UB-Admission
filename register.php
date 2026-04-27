<?php
require_once 'includes/db.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize all form fields — mysqli_real_escape_string prevents SQL injection
    $first   = trim(mysqli_real_escape_string($conn, $_POST['first_name']));
    $last    = trim(mysqli_real_escape_string($conn, $_POST['last_name']));
    $email   = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $phone   = trim(mysqli_real_escape_string($conn, $_POST['phone']));
    $dob     = mysqli_real_escape_string($conn, $_POST['date_of_birth']);
    $gender  = mysqli_real_escape_string($conn, $_POST['gender']);
    $nat     = mysqli_real_escape_string($conn, $_POST['nationality']);
    $omang   = trim(mysqli_real_escape_string($conn, $_POST['omang_passport']));
    $pass    = $_POST['password'];
    $pass2   = $_POST['confirm_password'];

    // Validate inputs before touching the database
    if (!$first || !$last || !$email || !$pass) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($pass !== $pass2) {
        $error = 'Passwords do not match.';
    } else {
        // Check if this email is already registered
        $check = mysqli_query($conn, "SELECT id FROM students WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'An account with this email already exists. Please <a href="login.php">login</a>.';
        } else {
            // Hash the password — never store plain text passwords
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO students (first_name,last_name,email,password,phone,date_of_birth,gender,nationality,omang_passport)
                    VALUES ('$first','$last','$email','$hash','$phone','$dob','$gender','$nat','$omang')";
            if (mysqli_query($conn, $sql)) {
                $success = 'Account created successfully! You can now <a href="login.php">log in</a> and start your application.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | UB Admissions</title>
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
        <a href="index.php">Home</a>
        <a href="login.php">Login</a>
        <a href="register.php" class="btn-nav active">Apply Now</a>
    </div>
</nav>

<div style="background: linear-gradient(135deg,var(--ub-blue) 0%,var(--ub-blue-light) 100%); padding:36px 1.5rem 48px;">
    <div style="max-width:680px; margin:0 auto; text-align:center; color:white;">
        <h1 style="font-family:'Playfair Display',serif; font-size:2rem; margin-bottom:8px;">Create Your Account</h1>
        <p style="opacity:0.8;">Fill in your personal details to register and begin your application.</p>
    </div>
</div>

<div style="max-width:680px; margin: -28px auto 60px; padding:0 1.5rem;">
    <div class="card">
        <div class="card-body">

            <?php if ($error): ?>
            <div class="alert alert-danger">⚠️ <?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= $success ?></div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST" action="">
                <h3 style="font-size:1rem; color:var(--ub-blue); margin-bottom:18px; padding-bottom:10px; border-bottom:2px solid var(--light-gray);">👤 Personal Information</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>First Name <span class="required">*</span></label>
                        <input type="text" name="first_name" class="form-control"
                               placeholder="e.g. Kagiso"
                               value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name <span class="required">*</span></label>
                        <input type="text" name="last_name" class="form-control"
                               placeholder="e.g. Motse"
                               value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control"
                           placeholder="e.g. kagiso.motse@gmail.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    <p class="form-hint">This will be your login username</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number</label>
                        <!-- Botswana numbers are 8 digits, starting with 7 for mobile -->
                        <input type="tel" name="phone" class="form-control"
                               placeholder="e.g. 71234567"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Omang / Passport No.</label>
                        <input type="text" name="omang_passport" class="form-control"
                               placeholder="e.g. 123456789"
                               value="<?= htmlspecialchars($_POST['omang_passport'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control"
                               value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" class="form-control">
                            <option value="">Select</option>
                            <option value="Male"   <?= (($_POST['gender'] ?? '')==='Male')   ? 'selected':'' ?>>Male</option>
                            <option value="Female" <?= (($_POST['gender'] ?? '')==='Female') ? 'selected':'' ?>>Female</option>
                            <option value="Other"  <?= (($_POST['gender'] ?? '')==='Other')  ? 'selected':'' ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nationality</label>
                        <input type="text" name="nationality" class="form-control"
                               placeholder="e.g. Motswana"
                               value="<?= htmlspecialchars($_POST['nationality'] ?? 'Motswana') ?>">
                    </div>
                </div>

                <h3 style="font-size:1rem; color:var(--ub-blue); margin: 24px 0 18px; padding-bottom:10px; border-bottom:2px solid var(--light-gray);">🔐 Set Password</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>Password <span class="required">*</span></label>
                        <input type="password" name="password" class="form-control"
                               placeholder="At least 6 characters"
                               required minlength="6">
                        <p class="form-hint">Minimum 6 characters</p>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password <span class="required">*</span></label>
                        <input type="password" name="confirm_password" class="form-control"
                               placeholder="Repeat your password"
                               required>
                    </div>
                </div>

                <button type="submit" class="btn btn-blue" style="width:100%; justify-content:center; margin-top:8px;">
                    Create Account →
                </button>
            </form>
            <?php endif; ?>

            <p style="text-align:center; margin-top:20px; font-size:0.87rem; color:var(--mid-gray);">
                Already have an account? <a href="login.php" style="color:var(--ub-blue); font-weight:600;">Sign In</a>
            </p>
        </div>
    </div>
</div>

<footer class="footer">
    © <?= date('Y') ?> <span>University of Botswana</span> — Online Admissions Portal.
</footer>
</body>
</html>
