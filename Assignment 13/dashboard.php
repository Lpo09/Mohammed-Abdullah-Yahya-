<?php
require_once 'config.php';

// Must be logged in
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// IP binding (optional extra protection)
if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']) {
    session_destroy();
    die('⚠️ IP changed — please log in again');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Dashboard</title>
    <style>body{font-family:Arial;background:#0f172a;color:#fff;padding:2rem}
           a{color:#38bdf8}</style>
</head>
<body>
    <h1>👋 Welcome <?= htmlspecialchars($_SESSION['user']) ?></h1>
    <p>Current Session ID: <code><?= session_id() ?></code></p>
    <p>Login time: <?= date('H:i:s', $_SESSION['login_time']) ?></p>
    <hr>
    <a href="test.php">🧪 Cookie Inspection Page</a> |
    <a href="logout.php">🚪 Logout</a>
</body>
</html>