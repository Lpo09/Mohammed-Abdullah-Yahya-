<?php
// config.php
// ⚠️ Must be included at the start of every file, before any output

// Default secure session settings
session_set_cookie_params([
    'lifetime' => 0,                        // Session ends when browser closes
    'path'     => '/',
    'domain'   => '',
    'secure'   => isset($_SERVER['HTTPS']), // true only over HTTPS
    'httponly' => true,                     // 🛡️ Blocks JavaScript access
    'samesite' => 'Strict',                 // 🛡️ Blocks CSRF
]);

// Extra hardening
ini_set('session.use_strict_mode', 1);      // Reject unknown session IDs
ini_set('session.use_only_cookies', 1);     // No ID in URL
ini_set('session.cookie_httponly', 1);
ini_set('session.gc_maxlifetime', 1800);    // 30 minutes

session_start();

// Generate CSRF token if not present
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// CSRF verification helper
function verify_csrf($token) {
    return isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}