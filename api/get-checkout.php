<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

// Accept JSON
$data = json_decode(file_get_contents("php://input"), true) ?: $_POST;
if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'No data received'
    ]);
    exit;
}

// Basic validation
$requiredFields = ['firstName', 'lastName', 'email', 'phone', 'address'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        echo json_encode([
            'success' => false,
            'message' => "$field is required"
        ]);
        exit;
    }
}

// Get user's pending order or create new
$stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ? AND status = 'pending' LIMIT 1");
$stmt->execute([$userId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    // Create pending order
    $stmt = $pdo->prepare("INSERT INTO orders (customer_id, status, created_at) VALUES (?, 'pending', NOW())");
    $stmt->execute([$userId]);
    $orderId = $pdo->lastInsertId();
} else {
    $orderId = $order['id'];
}

// Calculate total
$stmt = $pdo->prepare("SELECT SUM(price * quantity) as total FROM order_items WHERE order_id = ?");
$stmt->execute([$orderId]);
$total = $stmt->fetchColumn();
if (!$total || $total <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Order is empty'
    ]);
    exit;
}

// Generate order reference
$orderReference = 'TT-' . strtoupper(substr(md5(uniqid()), 0, 8));

// Update order with reference (still pending)
$stmt = $pdo->prepare("
    UPDATE orders 
    SET 
        order_reference = ?,
        customer_email = ?,
        customer_first_name = ?,
        customer_last_name = ?,
        customer_phone = ?,
        customer_address = ?,
        customer_city = ?,
        customer_state = ?,
        customer_zip = ?,
        customer_country = ?,
        updated_at = NOW()
    WHERE id = ?
");

$stmt->execute([
    $orderReference,
    $data['email'],
    $data['firstName'],
    $data['lastName'],
    $data['phone'],
    $data['address'],
    $data['city'] ?? null,
    $data['state'] ?? null,
    $data['zipCode'] ?? null,
    $data['country'] ?? null,
    $orderId
]);

// Insert payment history (pending)
$paymentId = bin2hex(random_bytes(16));
$stmt = $pdo->prepare("INSERT INTO payment_history 
    (id, order_id, order_reference, amount, payment_method, payment_status, transaction_id)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $paymentId,
    $orderId,
    $orderReference,
    $total,
    'pending', // placeholder; actual method updated after payment
    'pending',
    null
]);

// Return JSON for JS / Lenco
echo json_encode([
    'success' => true,
    'message' => 'Order ready for payment',
    'order_id' => $orderId,
    'order_reference' => $orderReference,
    'amount' => $total,
    'lenco_public_key' => 'pk_test_XXXXXXXXXXXXXXXXXXXXXXXX' // Lenco public key
]);
