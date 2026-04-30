<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // 1. Get active cart for this user
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE customer_id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$userId]);
    $cart = $stmt->fetch();

    if (!$cart) {
        echo json_encode([
            'success' => true,
            'message' => 'No active cart'
        ]);
        exit;
    }

    $cartId = $cart['id'];

    // 2. Delete all items from cart_items
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $stmt->execute([$cartId]);

    // 3. Set cart status to inactive
    $stmt = $pdo->prepare("UPDATE carts SET status = 'inactive' WHERE id = ?");
    $stmt->execute([$cartId]);

    echo json_encode([
        'success' => true,
        'message' => 'Cart cleared successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
