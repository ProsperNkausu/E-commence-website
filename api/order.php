
<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

try {

    // Get active cart
    $stmt = $pdo->prepare("
        SELECT id 
        FROM carts 
        WHERE customer_id = ? AND status = 'active' 
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $cartId = $stmt->fetchColumn();

    if (!$cartId) {
        echo json_encode(['success' => false, 'message' => 'Cart not found']);
        exit;
    }

    // Get cart items
    $stmt = $pdo->prepare("
       SELECT product_id, quantity, price_at_time 
FROM cart_items 
WHERE cart_id = ?
    ");

    $stmt->execute([$cartId]);
    $items = $stmt->fetchAll();

    if (!$items) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit;
    }

    // Calculate total
    $total = 0;
    foreach ($items as $item) {
        $total += $item['quantity'] * $item['price_at_time'];
    }

    // Generate order ID + number
    $orderId = bin2hex(random_bytes(16));
    $orderNumber = 'ORD-' . time();

    // Insert into orders
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            id, customer_id, order_number, total_amount
        ) VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $orderId,
        $userId,
        $orderNumber,
        $total
    ]);

    // Clear cart_items
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $stmt->execute([$cartId]);

    // Mark cart completed
    $stmt = $pdo->prepare("
    UPDATE carts 
    SET status = 'completed' 
    WHERE id = ?
");

    $stmt->execute([$cartId]);

    // Move cart items → order_items
    foreach ($items as $item) {

        $stmt = $pdo->prepare("
        INSERT INTO order_items (
            id, order_id, product_id, quantity, price
        ) VALUES (?, ?, ?, ?, ?)
    ");

        $stmt->execute([
            bin2hex(random_bytes(16)),
            $orderId,
            $item['product_id'] ?? null,
            $item['quantity'],
            $item['price_at_time']
        ]);
    }

    // OPTIONAL: mark cart inactive
    $stmt = $pdo->prepare("
        UPDATE carts 
        SET status = 'completed' 
        WHERE id = ?
    ");
    $stmt->execute([$cartId]);

    echo json_encode([
        'success' => true,
        'order_id' => $orderId
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
