<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// 
require_once __DIR__ . '/generate-invoice.php';
require_once __DIR__ . '/send-email.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true) ?: $_POST;
$reference = $input['reference'] ?? null;

if (!$reference) {
    echo json_encode([
        'success' => false,
        'message' => 'Reference missing'
    ]);
    exit;
}

// Lenco Secret Key
$secretKey = 'sk_test_XXXXXXXXXXXXXXXXXXXXXXXX';

// CORRECT ENDPOINT
$url = "https://api.lenco.co/access/v2/collections/status/" . urlencode($reference);

// cURL request
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $secretKey",
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Debug safety
if ($httpCode !== 200 || !$response) {
    echo json_encode([
        'success' => false,
        'message' => 'Lenco API error',
        'http_code' => $httpCode,
        'raw_response' => $response,
        'url' => $url
    ]);
    exit;
}

$result = json_decode($response, true);

// Validate structure
if (!$result || !isset($result['data'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid response from Lenco',
        'raw' => $result
    ]);
    exit;
}

$data = $result['data'];

// CHECK PAYMENT STATUS
if (($data['status'] ?? null) !== 'successful') {
    echo json_encode([
        'success' => false,
        'message' => 'Payment not successful',
        'status' => $data['status'] ?? null
    ]);
    exit;
}

// Extract values
$orderReference = $data['reference'];
$amount = (float)$data['amount'];
$paymentMethod = $data['type'] ?? 'unknown';
$transactionId = $data['id'] ?? null;

try {
    $pdo->beginTransaction();

    // NEW: Get order + customer BEFORE updates (FIXES YOUR BUG)
    $stmt = $pdo->prepare("
        SELECT id, customer_id 
        FROM orders 
        WHERE order_reference = ?
    ");
    $stmt->execute([$orderReference]);
    $orderData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orderData) {
        throw new Exception("Order not found");
    }

    $orderId = $orderData['id'];
    $customerId = $orderData['customer_id'];

    // Update order (UNCHANGED)
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET 
            status = 'paid',
            payment_status = 'successful',
            payment_method = ?,
            updated_at = NOW()
        WHERE order_reference = ?
    ");

    $stmt->execute([
        $paymentMethod,
        $orderReference
    ]);

    // Update payment history (UNCHANGED)
    $stmt = $pdo->prepare("
        UPDATE payment_history 
        SET payment_status = 'successful',
            payment_method = ?,
            transaction_id = ?
        WHERE order_reference = ?
    ");
    $stmt->execute([
        $paymentMethod,
        $transactionId,
        $orderReference
    ]);

    // FIXED: Update cart (removed broken fetchColumn)
    if ($customerId) {
        $stmt = $pdo->prepare("
            UPDATE carts 
            SET status = 'ordered', updated_at = NOW()
            WHERE customer_id = ? AND status = 'active'
        ");
        $stmt->execute([$customerId]);
    }

    // NEW: Generate invoice
    $invoice = generateInvoice($pdo, $orderReference);

    // NEW: Send email
    if (!empty($invoice['customer_email'])) {
        sendInvoiceEmail(
            $invoice['customer_email'],
            $invoice['invoice_url'],
            $invoice['invoice_number'],
            $invoice['file_path']
        );
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Payment verified successfully',
        'order_reference' => $orderReference,
        'invoice_url' => $invoice['invoice_url'] ?? null
    ]);
} catch (Exception $e) {
    $pdo->rollBack();

    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'error' => $e->getMessage()
    ]);
}