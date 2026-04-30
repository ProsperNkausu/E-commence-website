<!-- get-cart.php -->
<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized',
        'cart' => []
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

try {

    // 1. Get active cart
    $stmt = $pdo->prepare("
        SELECT id 
        FROM carts 
        WHERE customer_id = :customer_id 
        AND status = 'active'
        LIMIT 1
    ");
    $stmt->execute(['customer_id' => $userId]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cart) {
        echo json_encode([
            'success' => true,
            'cart' => []
        ]);
        exit;
    }

    $cartId = $cart['id'];

    // 2. Get cart items with product details
    $stmt = $pdo->prepare("
        SELECT 
            ci.product_id,
            ci.quantity,
            ci.price_at_time AS price,
            p.name AS product_name,
            p.image,
            p.category
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.cart_id = :cart_id
    ");

    $stmt->execute(['cart_id' => $cartId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'cart' => $items
    ]);
} catch (PDOException $e) {

    error_log("Get cart error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'cart' => []
    ]);
}
