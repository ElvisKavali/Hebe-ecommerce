<?php
/**
 * Hebe Store — Database Configuration
 *
 * Usage (anywhere in the project):
 *   require_once __DIR__ . '/../config/db.php';
 *   $pdo = db_connect();
 *
 * On XAMPP the defaults below work out of the box.
 * On a live host, set the environment variables instead of editing this file:
 *
 *   DB_HOST   DB_PORT   DB_NAME   DB_USER   DB_PASS
 */

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'hebe_store');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');          // XAMPP default is empty

define('DB_CHARSET', 'utf8mb4');

/**
 * Return a PDO connection (singleton — one connection per request).
 *
 * @throws RuntimeException on connection failure (in production this
 *         message is suppressed so credentials are never leaked to users).
 */
function db_connect(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,   // real prepared statements
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Never expose credentials or stack traces to end-users
        $isDevMode = (DB_HOST === '127.0.0.1' || DB_HOST === 'localhost');
        if ($isDevMode) {
            throw new RuntimeException('[DEV] Database connection failed: ' . $e->getMessage());
        }
        error_log('DB connection error: ' . $e->getMessage());
        throw new RuntimeException('A database error occurred. Please try again later.');
    }

    return $pdo;
}
