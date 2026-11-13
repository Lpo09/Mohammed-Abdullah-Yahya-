<?php
/**
 * config.php
 * Application and database configuration
 */

declare(strict_types=1);

// Database settings (in-memory SQLite)
define('DB_DSN', 'sqlite::memory:');

// PDO options
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);

// View settings
define('APP_TITLE', 'SQL Injection Defense Tool');

// Hide errors from users (production: keep as false)
ini_set('display_errors', '0');
ini_set('log_errors', '1');