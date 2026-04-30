<?php
require __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$orderId = $_GET['order_id'] ?? null;

if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'Missing order ID']);
    exit;
}

/* ===============================
   ORDER + CUSTOMER
================================ */
$stmt = $pdo->prepare("
    SELECT 
        o.id,
        o.order_number,
        o.status,
        o.payment_status,
        o.total_amount,
        c.first_name,
        c.last_name,
        c.email,
        c.phone
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

/* ===============================
   ORDER ITEMS
================================ */
$stmt = $pdo->prepare("
    SELECT 
        oi.quantity,
        oi.price,
        p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items
]);
