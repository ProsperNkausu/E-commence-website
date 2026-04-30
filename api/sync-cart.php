<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['items']) || !is_array($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'No items']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // 1. Get or create active cart
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE customer_id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$userId]);
    $cart = $stmt->fetch();

    if (!$cart) {
        $stmt = $pdo->prepare("INSERT INTO carts (customer_id) VALUES (?)");
        $stmt->execute([$userId]);

        $stmt = $pdo->prepare("SELECT id FROM carts WHERE customer_id = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$userId]);
        $cartId = $stmt->fetchColumn();
    } else {
        $cartId = $cart['id'];
    }

    // ============================
    // DELETE REMOVED ITEMS (FIX)
    // ============================
    $productIds = array_column($data['items'], 'product_id');

    if (!empty($productIds)) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

        $stmt = $pdo->prepare("
        DELETE FROM cart_items 
        WHERE cart_id = ? 
        AND product_id NOT IN ($placeholders)
    ");

        $stmt->execute(array_merge([$cartId], $productIds));
    } else {
        // If cart is empty → clear all items
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$cartId]);

        // Optional but IMPORTANT: mark cart inactive
        $stmt = $pdo->prepare("UPDATE carts SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$cartId]);

        echo json_encode(['success' => true]);
        exit;
    }

    // 2. Insert / Update items
    foreach ($data['items'] as $item) {

        $productId = $item['product_id'];
        $quantity = (int)$item['quantity'];

        // Get product price
        $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) continue;

        $price = $product['price'];

        // Insert or update
        $stmt = $pdo->prepare("
    INSERT INTO cart_items (cart_id, product_id, quantity, price_at_time)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), price_at_time = VALUES(price_at_time)
");

        $stmt->execute([$cartId, $productId, $quantity, $price]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}