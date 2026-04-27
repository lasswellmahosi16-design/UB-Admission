<?php
require_once '../includes/db.php';
$error = '';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $pass     = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM admins WHERE username='$username'");
    if ($result && mysqli_num_rows($result) === 1) {
        $admin = mysqli_fetch_assoc($result);
        if (password_verify($pass, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | UB Admissions</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body style="display:flex; flex-direction:column; min-height:100vh; background:linear-gradient(135deg,#001f5b 0%,#003B8E 100%);">

<div style="flex:1; display:flex; align-items:center; justify-content:center; padding:40px 1.5rem;">
    <div style="width:100%; max-width:400px;">
        <div style="text-align:center; margin-bottom:28px; color:white;">
            <div style="width:64px; height:64px; background:var(--ub-gold); border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-family:'Playfair Display',serif; color:var(--ub-blue); font-size:1.4rem; font-weight:700; margin-bottom:16px;">UB</div>
            <h1 style="font-family:'Playfair Display',serif; font-size:1.7rem; margin-bottom:6px;">Admin Portal</h1>
            <p style="opacity:0.7; font-size:0.88rem;">University of Botswana — Admissions Management</p>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label>Username <span class="required">*</span></label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Password <span class="required">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-blue" style="width:100%; justify-content:center;">
                        Sign In to Admin Portal →
                    </button>
                </form>

                <p style="text-align:center; margin-top:18px; font-size:0.82rem; color:var(--mid-gray);">
                    Default: admin / admin123
                </p>
            </div>
        </div>
        <p style="text-align:center; margin-top:14px; font-size:0.8rem; color:rgba(255,255,255,0.5);">
            <a href="../index.php" style="color:rgba(255,255,255,0.6);">← Back to Student Portal</a>
        </p>
    </div>
</div>

<div style="text-align:center; padding:16px; color:rgba(255,255,255,0.4); font-size:0.78rem;">
    © <?= date('Y') ?> University of Botswana — Admissions Administration
</div>
</body>
</html>
