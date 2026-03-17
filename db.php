<?php
// ── db.php ── Database connection ────────────────────────────────────────────
// Edit the constants below to match your MySQL setup.

define('DB_HOST', 'sql113.infinityfree.com');
define('DB_USER', 'if0_41417482');
define('DB_PASS', 'ishikent18');
define('DB_NAME', 'if0_41417482_neulibrarydb');
define('DB_PORT', 3306);

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            DB_HOST, DB_PORT, DB_NAME
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}