<?php
/**
 * database.php
 * PDO connection + table and seed data initialization
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Create a PDO connection to in-memory SQLite
 */
function getDatabase(): PDO
{
    try {
        $pdo = new PDO(DB_DSN, null, null, DB_OPTIONS);
        initializeDatabase($pdo);
        return $pdo;
    } catch (PDOException $e) {
        error_log('[DB] Connection failed: ' . $e->getMessage());
        die('Database connection failed.');
    }
}

/**
 * Create the users table and insert seed records
 */
function initializeDatabase(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE users (
            id       INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role     TEXT NOT NULL DEFAULT 'user'
        );
    ");

    $stmt = $pdo->prepare(
        "INSERT INTO users (username, password, role) VALUES (:u, :p, :r)"
    );

    $seed = [
        ['admin',   'Admin@123', 'admin'],
        ['alice',   'Alice@456', 'user'],
        ['bob',     'Bob@789',   'user'],
        ['charlie', 'Char@000',  'user'],
    ];

    foreach ($seed as [$user, $pass, $role]) {
        $stmt->execute([
            ':u' => $user,
            ':p' => password_hash($pass, PASSWORD_DEFAULT),
            ':r' => $role,
        ]);
    }
}