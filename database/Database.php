<?php
/**
 * Hebe Store — Database Singleton
 *
 * All models should use this class instead of calling db_connect() directly:
 *
 *   require_once __DIR__ . '/../database/Database.php';
 *   $pdo = \HebeStore\Database::getInstance();
 *   $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
 *   $stmt->execute([$id]);
 *   $product = $stmt->fetch();
 */

namespace HebeStore;

require_once __DIR__ . '/../config/db.php';

class Database
{
    private static ?\PDO $instance = null;

    /**
     * Returns the singleton PDO connection.
     * Delegates to db_connect() so all config lives in config/db.php.
     */
    public static function getInstance(): \PDO
    {
        if (self::$instance === null) {
            self::$instance = db_connect();
        }
        return self::$instance;
    }

    /**
     * Convenience: prepare + execute + return all rows.
     *
     * @param  string  $sql    SQL with ? or :named placeholders
     * @param  array   $params Bound parameters
     * @return array           Rows as associative arrays
     */
    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Convenience: prepare + execute + return the first row only (or null).
     */
    public static function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Convenience: prepare + execute (INSERT / UPDATE / DELETE).
     * Returns the number of affected rows.
     */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Returns the last inserted auto-increment ID.
     */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    /**
     * Run a callable inside a transaction; rolls back on any exception.
     *
     * Usage:
     *   Database::transaction(function() {
     *       Database::execute("INSERT INTO orders ...", [...]);
     *       Database::execute("INSERT INTO order_items ...", [...]);
     *   });
     */
    public static function transaction(callable $callback): void
    {
        $pdo = self::getInstance();
        $pdo->beginTransaction();
        try {
            $callback();
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // Prevent instantiation / cloning
    private function __construct() {}
    private function __clone() {}
}
