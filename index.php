<?php require_once 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UB Online Admissions | University of Botswana</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a href="index.php" class="navbar-brand">
        <div class="logo-circle">UB</div>
        <div class="brand-text">
            <div class="top">University of Botswana</div>
            <div class="sub">Online Admissions Portal</div>
        </div>
    </a>
    <div class="nav-links">
        <a href="index.php" class="active">Home</a>
        <a href="login.php">Login</a>
        <a href="register.php" class="btn-nav">Apply Now</a>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">📋 2024/2025 Admissions Open</div>
        <h1>Start Your Journey at the University of Botswana</h1>
        <p>Apply for undergraduate admission online. Submit your documents, track your application status, and receive updates — all in one place.</p>
        <div class="hero-actions">
            <a href="register.php" class="btn btn-primary">🎓 Apply Now</a>
            <a href="login.php" class="btn btn-outline">Sign In to Portal</a>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section style="padding: 60px 1.5rem;">
    <div class="container">
        <div style="text-align:center; margin-bottom: 40px;">
            <h2 style="font-family:'Playfair Display',serif; color:var(--ub-blue); font-size:1.8rem; margin-bottom:8px;">How It Works</h2>
            <p style="color:var(--mid-gray);">Four simple steps to complete your application</p>
        </div>
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:24px;">
            <?php
            $steps = [
                ['icon'=>'📝','title'=>'Create Account','desc'=>'Register with your email and personal details to get started.'],
                ['icon'=>'🎓','title'=>'Fill Application','desc'=>'Enter your BGCSE results and choose your preferred programme.'],
                ['icon'=>'📎','title'=>'Upload Documents','desc'=>'Upload your certificates, ID/Omang, and transcripts securely.'],
                ['icon'=>'✅','title'=>'Submit & Track','desc'=>'Submit your application and check your status anytime.'],
            ];
            foreach ($steps as $i => $s): ?>
            <div style="background:white; border-radius:var(--radius); padding:28px 22px; border:1px solid var(--light-gray); text-align:center; box-shadow:var(--shadow-sm);">
                <div style="font-size:2rem; margin-bottom:14px;"><?= $s['icon'] ?></div>
                <div style="width:28px; height:28px; background:var(--ub-blue); color:white; border-radius:50%; font-size:0.8rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; margin-bottom:12px;"><?= $i+1 ?></div>
                <h3 style="font-size:1rem; color:var(--ub-blue); margin-bottom:8px;"><?= $s['title'] ?></h3>
                <p style="font-size:0.85rem; color:var(--mid-gray);"><?= $s['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PROGRAMMES HIGHLIGHT -->
<section style="padding: 0 1.5rem 60px;">
    <div class="container">
        <div style="background: linear-gradient(135deg,var(--ub-blue),var(--ub-blue-light)); border-radius:var(--radius); padding:48px; color:white; display:grid; grid-template-columns:1fr 1fr; gap:40px; align-items:center;">
            <div>
                <div class="hero-badge" style="margin-bottom:16px;">Available Programmes</div>
                <h2 style="font-family:'Playfair Display',serif; font-size:2rem; margin-bottom:14px;">BSc General & More</h2>
                <p style="opacity:0.85; margin-bottom:24px;">Explore a wide range of undergraduate programmes across science, humanities, business, and engineering. Applications are ranked by BGCSE points.</p>
                <a href="register.php" class="btn btn-primary">Start Application →</a>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <?php
                $progs = ['BSc General','Bachelor of Education','BA Social Sciences','BEng Civil Eng.','BCom Accounting','Bachelor of Nursing','BSc Computer Science','BBA Management'];
                foreach($progs as $p): ?>
                <div style="background:rgba(255,255,255,0.1); border-radius:var(--radius-sm); padding:12px 14px; font-size:0.82rem; border:1px solid rgba(255,255,255,0.15);"><?= $p ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    © <?= date('Y') ?> <span>University of Botswana</span> — Online Admissions Portal. Department of Computer Science.
</footer>

</body>
</html>
