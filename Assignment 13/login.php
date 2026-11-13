<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        die('❌ Invalid request (CSRF)');
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Simple check (replace with real DB)
    if ($username === 'admin' && $password === 'Secure@123') {

        // 🛡️ Regenerate session ID to prevent Session Fixation
        session_regenerate_id(true);

        $_SESSION['user']       = $username;
        $_SESSION['login_time'] = time();
        $_SESSION['ip']         = $_SERVER['REMOTE_ADDR'];

        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid credentials';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body{font-family:Arial;background:#1e293b;color:#fff;display:flex;
             justify-content:center;align-items:center;height:100vh;margin:0}
        .box{background:#334155;padding:2rem;border-radius:10px;width:320px}
        input{width:100%;padding:10px;margin:8px 0;border-radius:6px;border:none;
              box-sizing:border-box}
        button{width:100%;padding:10px;background:#22c55e;color:#fff;
               border:none;border-radius:6px;cursor:pointer;font-weight:bold}
        .err{color:#f87171;margin-top:10px}
    </style>
</head>
<body>
    <form class="box" method="POST">
        <h2>🔐 Login</h2>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Sign In</button>
        <?php if ($error): ?><p class="err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <p style="font-size:12px;color:#94a3b8">Demo: admin / Secure@123</p>
    </form>
</body>
</html>