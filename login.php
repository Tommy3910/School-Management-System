<?php
ob_start();
session_start();
$conn = mysqli_connect("localhost", "root", "", "rad_project");

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email    = $_POST['email']    ?? '';
    $password = $_POST['password'] ?? '';   // ← lowercase, matches input name

    if (empty($email) || empty($password)) {  // ← lowercase $password
        $error = "Please fill all fields";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE useremail = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {               // ← fetch once only
            $row = $result->fetch_assoc();

            if ($password == $row['PASSWORD']) {    // plain text check
                $_SESSION['useremail'] = $email;
                $_SESSION['role']      = $row['role'];

                switch ($row['role']) {
                    case 'student':
                        header("Location: studentHomePage.php");
                        exit();
                    case 'teacher':
                        header("Location: teacherHomePage.php");
                        exit();
                    default:
                        $error = "Unknown role assigned to this account.";
                }
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with that email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Maplewood Learning Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            --forest:   #1a3c2e;
            --leaf:     #2d6a4f;
            --sage:     #74c69d;
            --cream:    #fdf8f0;
            --gold:     #c9972c;
            --gold-lt:  #f4c96a;
            --text:     #1a2e22;
            --muted:    #6b8f71;
            --white:    #ffffff;
            --shadow:   0 20px 60px rgba(26,60,46,0.18);
        }

        body.dark-mode {
            --forest:   #0a1f18;
            --leaf:     #1a4d39;
            --sage:     #4a9d6f;
            --cream:    #1a1a1a;
            --gold:     #d4a574;
            --gold-lt:  #e8c285;
            --text:     #e8e8e8;
            --muted:    #a0b5a0;
            --white:    #2a2a2a;
            --shadow:   0 20px 60px rgba(0,0,0,0.5);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--forest);
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
        }

        /* ── Left panel ── */
        .panel-left {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 60px 64px;
            background: linear-gradient(145deg, #1a3c2e 0%, #0f2419 100%);
            overflow: hidden;
        }

        /* decorative leaf rings */
        .panel-left::before,
        .panel-left::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            border: 1.5px solid rgba(116,198,157,0.15);
        }
        .panel-left::before {
            width: 520px; height: 520px;
            top: -160px; left: -160px;
        }
        .panel-left::after {
            width: 320px; height: 320px;
            bottom: -100px; right: -60px;
        }

        .ring-extra {
            position: absolute;
            border-radius: 50%;
            border: 1.5px solid rgba(116,198,157,0.08);
            width: 700px; height: 700px;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        .school-badge {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 52px;
            animation: fadeUp 0.7s ease both;
        }

        .badge-icon {
            width: 64px; height: 64px;
            background: var(--white);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(201,151,44,0.3);
            flex-shrink: 0;
        }
        .badge-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .badge-text {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            color: var(--white);
            line-height: 1.3;
        }
        .badge-text span {
            display: block;
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            font-weight: 300;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--sage);
            margin-top: 2px;
        }

        .hero-heading {
            font-family: 'Playfair Display', serif;
            font-size: clamp(34px, 3.5vw, 52px);
            color: var(--white);
            line-height: 1.18;
            margin-bottom: 20px;
            animation: fadeUp 0.7s 0.1s ease both;
        }
        .hero-heading em {
            font-style: normal;
            color: var(--gold-lt);
        }

        .hero-sub {
            font-size: 15px;
            color: var(--muted);
            line-height: 1.7;
            max-width: 340px;
            animation: fadeUp 0.7s 0.2s ease both;
        }

        .stats-row {
            display: flex;
            gap: 32px;
            margin-top: 52px;
            animation: fadeUp 0.7s 0.3s ease both;
        }
        .stat { display: flex; flex-direction: column; gap: 4px; }
        .stat-num {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: var(--white);
        }
        .stat-label {
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--muted);
        }
        .stat-divider {
            width: 1px;
            background: rgba(116,198,157,0.2);
            align-self: stretch;
        }

        /* ── Right panel ── */
        .panel-right {
            background: var(--cream);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 48px;
            position: relative;
        }

        /* subtle dot grid */
        .panel-right::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(45,106,79,0.08) 1px, transparent 1px);
            background-size: 24px 24px;
            pointer-events: none;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            animation: fadeUp 0.8s 0.15s ease both;
        }

        .card-header {
            margin-bottom: 36px;
        }
        .card-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: var(--text);
            margin-bottom: 8px;
        }
        .card-header p {
            font-size: 14px;
            color: var(--muted);
        }

        .divider {
            width: 48px;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--gold-lt));
            border-radius: 2px;
            margin: 14px 0 0;
        }

        /* form */
        .field {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text);
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }
        .input-wrap svg {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            pointer-events: none;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px 14px 46px;
            border: 1.5px solid #d4e6da;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            color: var(--text);
            background: var(--white);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: var(--leaf);
            box-shadow: 0 0 0 3px rgba(45,106,79,0.1);
        }
        input::placeholder { color: #aac4b0; }

        .row-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 6px 0 28px;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--muted);
            cursor: pointer;
        }
        .remember input[type="checkbox"] {
            width: 16px; height: 16px;
            padding: 0;
            accent-color: var(--leaf);
            border-radius: 4px;
        }
        .forgot {
            font-size: 13px;
            color: var(--leaf);
            text-decoration: none;
            font-weight: 500;
        }
        .forgot:hover { text-decoration: underline; }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--leaf), var(--forest));
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 500;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 6px 20px rgba(26,60,46,0.3);
            position: relative;
            overflow: hidden;
        }
        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.08), transparent);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(26,60,46,0.38);
        }
        .btn-login:active { transform: translateY(0); }

        .card-footer {
            margin-top: 28px;
            text-align: center;
        }
        .card-footer p {
            font-size: 13px;
            color: var(--muted);
        }
        .card-footer a {
            color: var(--leaf);
            font-weight: 500;
            text-decoration: none;
        }
        .card-footer a:hover { text-decoration: underline; }

        .role-tabs {
            display: flex;
            background: #e8f5ee;
            border-radius: 10px;
            padding: 4px;
            margin-bottom: 28px;
        }
        .role-tab {
            flex: 1;
            padding: 9px;
            text-align: center;
            font-size: 13px;
            font-weight: 500;
            color: var(--muted);
            border-radius: 7px;
            cursor: pointer;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            user-select: none;
        }
        .role-tab.active {
            background: var(--white);
            color: var(--forest);
            box-shadow: 0 2px 8px rgba(26,60,46,0.12);
        }

        .dark-mode-toggle {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 1000;
            background: var(--white);
            border: 1.5px solid var(--muted);
            width: 48px;
            height: 48px;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(26,60,46,0.1);
        }

        .dark-mode-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(26,60,46,0.15);
        }

        .dark-mode-toggle svg {
            width: 24px;
            height: 24px;
            color: var(--forest);
            transition: color 0.3s ease;
        }

        /* animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* responsive */
        @media (max-width: 768px) {
            body { grid-template-columns: 1fr; }
            .panel-left { display: none; }
            .panel-right { padding: 40px 24px; min-height: 100vh; }
        }
    </style>
</head>
<body>

<!-- Dark Mode Toggle -->
<button class="dark-mode-toggle" id="darkModeToggle" title="Toggle dark mode" aria-label="Toggle dark mode">
    <svg id="sunIcon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
    </svg>
    <svg id="moonIcon" style="display:none;" fill="currentColor" viewBox="0 0 24 24">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
    </svg>
</button>

<!-- ═══ Left Panel ═══ -->
<div class="panel-left">
    <div class="ring-extra"></div>

    <div class="school-badge">
        <div class="badge-icon">
            <img src="maple_wood_logo.png" alt="Maplewood Logo">
        </div>
        <div class="badge-text">
            Maplewood Learning Center
            <span>School Management System</span>
        </div>
    </div>

    <h1 class="hero-heading">
        Empowering<br><em>Every</em> Learner,<br>Every Day.
    </h1>
    <p class="hero-sub">
        A unified portal for students, teachersto manage academic life with clarity and ease.
    </p>

    <div class="stats-row">
        <div class="stat">
            <span class="stat-num">1,200+</span>
            <span class="stat-label">Students</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat">
            <span class="stat-num">80+</span>
            <span class="stat-label">Teachers</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat">
            <span class="stat-num">24 / 7</span>
            <span class="stat-label">Access</span>
        </div>
    </div>
</div>

<!-- ═══ Right Panel ═══ -->
<div class="panel-right">
    <div class="login-card">

        <div class="card-header">
            <h2>Welcome Back</h2>
            <p>Sign in to your account to continue</p>
            <div class="divider"></div>
        </div>

        <!-- role selector -->
        <div class="role-tabs">
            <div class="role-tab active" onclick="setRole(this)">Student</div>
            <div class="role-tab" onclick="setRole(this)">Teacher</div>
           
        </div>

        <form action="login.php" method="POST">

            <div class="field">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <input type="email" id="email" name="email" placeholder="you@maplewood.edu" required>
                </div>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
            </div>

            <div class="row-options">
                <label class="remember">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="forgot.php" class="forgot">Forgot password?</a>
            </div>

            <button type="submit" class="btn-login">Sign In</button>

        </form>

        <div class="card-footer">
            <p>Need an account? <a href="register.php">Contact your administrator</a></p>
        </div>

    </div>
</div>

<script>
    // Initialize dark mode from localStorage
    function initDarkMode() {
        const isDarkMode = localStorage.getItem('darkMode') === 'true';
        if (isDarkMode) {
            document.body.classList.add('dark-mode');
            updateToggleIcon();
        }
    }

    function updateToggleIcon() {
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');
        const isDark = document.body.classList.contains('dark-mode');
        
        if (isDark) {
            sunIcon.style.display = 'none';
            moonIcon.style.display = 'block';
        } else {
            sunIcon.style.display = 'block';
            moonIcon.style.display = 'none';
        }
    }

    // Toggle dark mode
    document.getElementById('darkModeToggle').addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const isDarkMode = document.body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', isDarkMode);
        updateToggleIcon();
    });

    // Initialize on page load
    window.addEventListener('DOMContentLoaded', initDarkMode);

    function setRole(el) {
        document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
    }
</script>
</body>
</html>