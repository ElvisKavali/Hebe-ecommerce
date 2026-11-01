<?php
/**
 * Hebe Store — Cart Helpers
 *
 * Guest cart lives in session. On login, call cart_merge() to pull
 * the session cart into the database row for the logged-in user.
 *
 * Usage:
 *   require_once __DIR__ . '/../cart/cart.php';
 *   cart_add($productId, $quantity, $variantId);
 *   $items = cart_items();
 *   $total = cart_total();
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/auth.php';

const CART_SESSION_KEY = 'hebe_cart';  // ['product_id:variant_id' => qty, ...]

// ── Internal helpers ──────────────────────────────────────────────────────────

function _cart_key(int $productId, ?int $variantId): string
{
    return $productId . ':' . ($variantId ?? 0);
}

// ── Public API ────────────────────────────────────────────────────────────────

/**
 * Add a product to the cart (or increase quantity if already present).
 */
function cart_add(int $productId, int $qty = 1, ?int $variantId = null): void
{
    $key = _cart_key($productId, $variantId);
    $_SESSION[CART_SESSION_KEY][$key] = ($_SESSION[CART_SESSION_KEY][$key] ?? 0) + $qty;

    if (auth_check()) {
        _cart_upsert_db($productId, $qty, $variantId);
    }
}

/** Set a cart item to an exact quantity (0 = remove). */
function cart_update(int $productId, int $qty, ?int $variantId = null): void
{
    $key = _cart_key($productId, $variantId);
    if ($qty <= 0) {
        unset($_SESSION[CART_SESSION_KEY][$key]);
        if (auth_check()) {
            _cart_remove_db($productId, $variantId);
        }
    } else {
        $_SESSION[CART_SESSION_KEY][$key] = $qty;
        if (auth_check()) {
            _cart_upsert_db($productId, $qty, $variantId, replace: true);
        }
    }
}

/** Remove one item entirely. */
function cart_remove(int $productId, ?int $variantId = null): void
{
    cart_update($productId, 0, $variantId);
}

/** Empty the whole cart. */
function cart_clear(): void
{
    unset($_SESSION[CART_SESSION_KEY]);
    if (auth_check()) {
        db_connect()
            ->prepare("DELETE FROM cart_items WHERE user_id = ?")
            ->execute([$_SESSION['user']['id']]);
    }
}

/**
 * Return cart rows joined with product/variant details.
 * Each row: [product_id, variant_id, product_name, unit_price, quantity, line_total, image]
 *
 * @return array
 */
function cart_items(): array
{
    $items = $_SESSION[CART_SESSION_KEY] ?? [];
    if (!$items) {
        return [];
    }

    $pdo    = db_connect();
    $result = [];

    foreach ($items as $key => $qty) {
        [$productId, $variantId] = explode(':', $key);
        $variantId = (int)$variantId ?: null;

        if ($variantId) {
            $row = $pdo->prepare("
                SELECT p.id AS product_id, pv.id AS variant_id,
                       CONCAT(p.name, ' – ', pv.variant_name, ': ', pv.variant_value) AS product_name,
                       (p.price + pv.price_adjustment) AS unit_price,
                       pi.image_path AS image
                FROM products p
                JOIN product_variants pv ON pv.id = ?
                LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
                WHERE p.id = ? AND p.status = 'active'
                LIMIT 1
            ");
            $row->execute([$variantId, $productId]);
        } else {
            $row = $pdo->prepare("
                SELECT p.id AS product_id, NULL AS variant_id, p.name AS product_name,
                       COALESCE(p.discount_price, p.price) AS unit_price,
                       pi.image_path AS image
                FROM products p
                LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
                WHERE p.id = ? AND p.status = 'active'
                LIMIT 1
            ");
            $row->execute([$productId]);
        }

        $product = $row->fetch();
        if (!$product) {
            continue;  // Product was deleted or deactivated; skip silently
        }

        $product['quantity']   = (int) $qty;
        $product['line_total'] = round($product['unit_price'] * $qty, 2);
        $result[] = $product;
    }

    return $result;
}

/** Return grand total of all cart items. */
function cart_total(): float
{
    return round(array_sum(array_column(cart_items(), 'line_total')), 2);
}

/** Return number of distinct items in the cart. */
function cart_count(): int
{
    return array_sum($_SESSION[CART_SESSION_KEY] ?? []);
}

/**
 * Merge guest session cart into DB after login.
 * Call this immediately after auth_login() succeeds.
 */
function cart_merge(): void
{
    $sessionItems = $_SESSION[CART_SESSION_KEY] ?? [];
    foreach ($sessionItems as $key => $qty) {
        [$productId, $variantId] = explode(':', $key);
        _cart_upsert_db((int)$productId, (int)$qty, (int)$variantId ?: null);
    }
}

// ── DB operations ─────────────────────────────────────────────────────────────

function _cart_upsert_db(int $productId, int $qty, ?int $variantId, bool $replace = false): void
{
    $userId = $_SESSION['user']['id'];
    $pdo    = db_connect();

    if ($replace) {
        $pdo->prepare("
            INSERT INTO cart_items (user_id, product_id, variant_id, quantity)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)
        ")->execute([$userId, $productId, $variantId, $qty]);
    } else {
        $pdo->prepare("
            INSERT INTO cart_items (user_id, product_id, variant_id, quantity)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
        ")->execute([$userId, $productId, $variantId, $qty]);
    }
}

function _cart_remove_db(int $productId, ?int $variantId): void
{
    $userId = $_SESSION['user']['id'];
    $pdo    = db_connect();
    $pdo->prepare("
        DELETE FROM cart_items
        WHERE user_id = ? AND product_id = ? AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL))
    ")->execute([$userId, $productId, $variantId, $variantId]);
}
