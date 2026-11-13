<?php
/**
 * functions.php
 * Safe and unsafe query execution functions
 */

declare(strict_types=1);

/**
 * ❌ UNSAFE query — direct string concatenation (VULNERABLE)
 *
 * @return array{query: string, results: array}
 */
function unsafeSearch(PDO $pdo, string $username): array
{
    // ⚠️ Danger: user input concatenated directly into the query string
    $query = "SELECT id, username, role FROM users WHERE username = '$username'";

    $stmt = $pdo->query($query);
    $results = $stmt->fetchAll();

    return ['query' => $query, 'results' => $results];
}

/**
 * ✅ SAFE query — Prepared Statement (PROTECTED)
 *
 * @return array{query: string, results: array}
 */
function safeSearch(PDO $pdo, string $username): array
{
    $sql = "SELECT id, username, role FROM users WHERE username = :username";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);
    $results = $stmt->fetchAll();

    return [
        'query'   => $sql . "  -- [:username = " . var_export($username, true) . "]",
        'results' => $results,
    ];
}

/**
 * Execute a search based on the selected mode
 *
 * @return array{query: string, results: array, error: ?string}
 */
function performSearch(PDO $pdo, string $mode, string $username): array
{
    try {
        if ($mode === 'unsafe') {
            $result = unsafeSearch($pdo, $username);
        } else {
            $result = safeSearch($pdo, $username);
        }
        return $result + ['error' => null];
    } catch (PDOException $e) {
        // Never leak error details to the user
        error_log('[SQLi Defense] ' . $e->getMessage());
        return [
            'query'   => '(hidden due to error)',
            'results' => [],
            'error'   => 'Query execution failed. Access denied.',
        ];
    }
}