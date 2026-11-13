<?php
require_once 'config.php';

// Delete the cookie, then destroy the session
$params = session_get_cookie_params();
setcookie(session_name(), '', time() - 42000,
    $params['path'], $params['domain'],
    $params['secure'], $params['httponly']);

$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;