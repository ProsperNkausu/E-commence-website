<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$customerId = $_SESSION['customer_id'];
$data = json_decode(file_get_contents("php://input"), true);

$items = $data['items'];

try {

    $stmt = $pdo->prepare("SELECT id FROM carts WHERE customer_id=? AND status='active'");
    $stmt->execute([$customerId]);
    $cart = $stmt->fetch();

    if (!$cart) {
        $stmt = $pdo->prepare("INSERT INTO carts (customer_id) VALUES (?)");
        $stmt->execute([$customerId]);

        $stmt = $pdo->prepare("SELECT id FROM carts WHERE customer_id=? AND status='active'");
        $stmt->execute([$customerId]);
        $cart = $stmt->fetch();
    }

    $cartId = $cart['id'];

    $pdo->prepare("DELETE FROM cart_items WHERE cart_id=?")->execute([$cartId]);

    foreach ($items as $item) {

        $stmt = $pdo->prepare("
INSERT INTO cart_items (cart_id,product_id,quantity,price_at_time)
VALUES (?,?,?,?)
");

        $stmt->execute([
            $cartId,
            $item['id'],
            $item['quantity'],
            $item['price']
        ]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false]);
}
