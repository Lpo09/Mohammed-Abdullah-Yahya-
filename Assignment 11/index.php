<?php
/**
 * index.php
 * Front Controller — handles the request, then renders the view
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

// ============================================================
// 1) Read input
// ============================================================
$mode     = $_POST['mode']     ?? 'safe';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? ''; // demo only
$isPost   = ($_SERVER['REQUEST_METHOD'] === 'POST');

// ============================================================
// 2) Process request
// ============================================================
$pdo     = getDatabase();
$query   = '';
$results = [];
$error   = null;

if ($isPost) {
    $outcome = performSearch($pdo, $mode, $username);
    $query   = $outcome['query'];
    $results = $outcome['results'];
    $error   = $outcome['error'];
}

// ============================================================
// 3) Pass data to the template and render
// ============================================================
require __DIR__ . '/views/home.php';