<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

$orderId = $data['order_id'] ?? null;
$newStatus = $data['status'] ?? null;

if (!$orderId || !$newStatus) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

/* ===============================
   VALIDATE STATUS
================================ */
$allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

if (!in_array($newStatus, $allowedStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $pdo->beginTransaction();

    /* ===============================
       GET ORDER + CUSTOMER
    ================================= */
    $stmt = $pdo->prepare("
        SELECT 
            o.order_number,
            o.status,
            c.first_name,
            c.email
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Order not found');
    }

    // Embed logo if exists
    $logoPath = __DIR__ . '/../public/images/TemaTech_logo.jpeg';
    if (file_exists($logoPath)) {
        $mail->addEmbeddedImage($logoPath, 'logo');
    }


    /* ===============================
       UPDATE STATUS
    ================================= */
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $orderId]);

    /* ===============================
       SEND EMAIL (ONLY IF SHIPPED)
    ================================= */
    if ($newStatus === 'shipped' && !empty($order['email'])) {

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'deepscale.info@gmail.com';
            $mail->Password = 'xdtdgdsnmfucdhin';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('deepscale.info@gmail.com', 'TemaTech Innovations');
            $mail->addAddress($order['email'], $order['first_name']);

            $mail->isHTML(true);
            $mail->Subject = "Your Order Has Been Shipped - #{$order['product_name']}";

            $mail->Body = "
             <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', sans-serif; color: #333; }
                .container { max-width: 600px; margin: auto; padding: 20px; }
                .header { text-align: center; border-bottom: 2px solid #ff8c00; padding-bottom: 15px; }
                .content { padding: 20px 0; }
                .footer { text-align: center; font-size: 12px; color: #777; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='cid:logo' width='150'><br>
                </div>

                <div class='content'>
                    <h2 style='color:#ff8c00;'>Your Order Has Been Shipped</h2>

                    <p>Good News, {$order['first_name']}!</p>

                    <p>It has been handed over to a trusted courier service and is now on its way to you.
                    You will be contacted shortly regarding delivery.</p>


                    <p>Thank you for choosing TemaTech Innovations.</p>

                    <p>If you have any questions, contact us at 
                    <strong>tematechinnovatons@gmail.com</strong></p>
                </div>

                <div class='footer'>
                    <p>&copy; " . date('Y') . " TemaTech Innovations</p>
                </div>
            </div>
        </body>
        </html>            ";

            $mail->AltBody = "Your order #{$order['order_number']} has been shipped.";

            $mail->send();
        } catch (Exception $e) {
            // Do NOT fail transaction because of email
            error_log("Mail error: " . $mail->ErrorInfo);
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order updated successfully'
    ]);
} catch (Exception $e) {

    $pdo->rollBack();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
