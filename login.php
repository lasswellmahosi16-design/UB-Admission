<?php
require_once 'includes/db.php';
$error = '';

// If student is already logged in, skip the login page
if (isset($_SESSION['student_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize email input to prevent SQL injection
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $pass  = $_POST['password'];

    // Look up student by email
    $sql    = "SELECT id, first_name, last_name, password FROM students WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        $student = mysqli_fetch_assoc($result);
        // password_verify() checks the plain text against the stored bcrypt hash
        if (password_verify($pass, $student['password'])) {
            // Save student info in session — used to identify them on other pages
            $_SESSION['student_id']   = $student['id'];
            $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
            header('Location: dashboard.php');
            exit;
        }
    }
    // Vague message on purpose — don't reveal whether email or password was wrong
    $error = 'Invalid email or password. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | UB Admissions</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="display:flex; flex-direction:column; min-height:100vh;">

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
        <a href="register.php">Register</a>
        <a href="login.php" class="btn-nav">Sign In</a>
    </div>
</nav>

<div style="flex:1; display:flex; align-items:center; justify-content:center; padding:60px 1.5rem; background:linear-gradient(135deg, #f0f4ff 0%, #fff 100%);">
    <div style="width:100%; max-width:440px;">

        <div style="text-align:center; margin-bottom:28px;">
            <div style="width:64px; height:64px; background:var(--ub-blue); border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-family:'Playfair Display',serif; color:white; font-size:1.4rem; font-weight:700; margin-bottom:16px;">UB</div>
            <h1 style="font-family:'Playfair Display',serif; color:var(--ub-blue); font-size:1.7rem; margin-bottom:6px;">Welcome Back</h1>
            <p style="color:var(--mid-gray); font-size:0.9rem;">Sign in to access your application portal</p>
        </div>

        <div class="card">
            <div class="card-body">

                <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">✅ Registration successful! Please sign in.</div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label>Email Address <span class="required">*</span></label>
                        <!-- placeholder shows the expected format to guide the user -->
                        <input type="email" name="email" class="form-control"
                               placeholder="e.g. john.doe@gmail.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Password <span class="required">*</span></label>
                        <input type="password" name="password" class="form-control"
                               placeholder="Enter your password"
                               required>
                    </div>
                    <button type="submit" class="btn btn-blue" style="width:100%; justify-content:center; margin-top:4px;">
                        Sign In →
                    </button>
                </form>

                <p style="text-align:center; margin-top:20px; font-size:0.87rem; color:var(--mid-gray);">
                    Don't have an account? <a href="register.php" style="color:var(--ub-blue); font-weight:600;">Register here</a>
                </p>
            </div>
        </div>

        <p style="text-align:center; margin-top:16px; font-size:0.8rem; color:var(--mid-gray);">
            Are you an administrator? <a href="admin/login.php" style="color:var(--ub-blue);">Admin Login</a>
        </p>
    </div>
</div>

<footer class="footer">
    © <?= date('Y') ?> <span>University of Botswana</span> — Online Admissions Portal.
</footer>
</body>
</html>
