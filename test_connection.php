<?php
/**
 * Hebe Store — Connection Diagnostic
 *
 * Run this in your browser at:
 *   http://localhost/hebe-store/test_connection.php
 *
 * ⚠️  DELETE or MOVE this file before deploying to production.
 *      It reveals your table structure to anyone who opens it.
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== Hebe Store — DB Connection Test ===\n\n";

// ── 1. Connection ────────────────────────────────────────────────────────────
try {
    $pdo = db_connect();
    echo "[OK] Connected to MySQL as '" . DB_USER . "'@'" . DB_HOST . "'\n";
    echo "[OK] Database : " . DB_NAME . "\n";
    $row = $pdo->query("SELECT VERSION() AS v")->fetch();
    echo "[OK] Server   : " . $row['v'] . "\n\n";
} catch (RuntimeException $e) {
    echo "[FAIL] " . $e->getMessage() . "\n";
    exit(1);
}

// ── 2. Charset ───────────────────────────────────────────────────────────────
$charset = $pdo->query("SELECT @@character_set_database AS c")->fetchColumn();
echo "[OK] Charset  : $charset\n";

// ── 3. Tables ────────────────────────────────────────────────────────────────
$required = [
    'users', 'addresses', 'categories', 'brands', 'products',
    'product_images', 'cart',
    'coupons', 'orders', 'order_items', 'payments',
    'reviews', 'contacts', 'wishlist', 'settings',
];

$existing = $pdo
    ->query("SHOW TABLES")
    ->fetchAll(PDO::FETCH_COLUMN);

echo "\n--- Tables ---\n";
$missing = [];
foreach ($required as $t) {
    if (in_array($t, $existing)) {
        echo "[OK]     $t\n";
    } else {
        echo "[MISSING] $t  ← run schema.sql first!\n";
        $missing[] = $t;
    }
}

// ── 4. FK check ──────────────────────────────────────────────────────────────
echo "\n--- Foreign Keys ---\n";
$fkRows = $pdo->query("
    SELECT TABLE_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
      AND REFERENCED_TABLE_NAME IS NOT NULL
    ORDER BY TABLE_NAME, CONSTRAINT_NAME
")->fetchAll();

if (!$fkRows) {
    echo "[WARN] No foreign keys found — did schema.sql run successfully?\n";
} else {
    foreach ($fkRows as $fk) {
        echo "[OK] {$fk['TABLE_NAME']}.{$fk['CONSTRAINT_NAME']} → {$fk['REFERENCED_TABLE_NAME']}\n";
    }
}

// ── 5. Settings seed ─────────────────────────────────────────────────────────
echo "\n--- Settings ---\n";
if (in_array('settings', $existing)) {
    $rows = $pdo->query("SELECT `key` AS setting_key, `value` AS setting_value FROM settings")->fetchAll();
    if ($rows) {
        foreach ($rows as $r) {
            echo "  {$r['setting_key']} = {$r['setting_value']}\n";
        }
    } else {
        echo "[WARN] settings table is empty — re-run INSERT section of schema.sql\n";
    }
}

// ── 6. Summary ───────────────────────────────────────────────────────────────
echo "\n=== Summary ===\n";
if (!$missing) {
    echo "✅  All " . count($required) . " tables present. Connection is healthy.\n";
} else {
    echo "❌  Missing tables: " . implode(', ', $missing) . "\n";
    echo "    → Open phpMyAdmin, select 'hebe_store', and import schema.sql\n";
}
