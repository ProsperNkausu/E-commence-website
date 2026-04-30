<?php
require_once __DIR__ . '/../config/db.php';

use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/../vendor/autoload.php';


function generateInvoice($pdo, $orderReference)
{
    // Get order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_reference = ?");
    $stmt->execute([$orderReference]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order not found");
    }

    // Get customer
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$order['customer_id']]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get items
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order['id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculations
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }

    $tax = $subtotal * 0.16;
    $total = $subtotal + $tax;

    // Invoice number
    $invoiceNumber = 'INV-' . strtoupper(substr(md5($orderReference), 0, 8));

    // Paths
    $dir = __DIR__ . '/../receipts/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $fileName = $invoiceNumber . '.pdf';
    $filePath = $dir . $fileName;
    $fileUrl = '/tematech-innovation/receipts/' . $fileName;

    // =========================
    // HTML TEMPLATE (for PDF)
    // =========================
    ob_start();

    // Convert logo to base64 (so Dompdf can render it reliably)
    $logoSrc = '';

    $logoPath = __DIR__ . '/../public/images/TemaTech_logo.jpeg';
    if (file_exists($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/jpeg;base64,' . $logoData;
    }

    ob_start();
?>

    <head>
        <style>
            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 12px;
            }

            .header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 2px solid #eee;
                padding-bottom: 10px;
            }

            .logo {
                height: 60px;
            }

            .company {
                text-align: right;
            }

            h2 {
                margin: 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
            }

            th,
            td {
                border: 1px solid #ddd;
                padding: 8px;
            }

            th {
                background: #f5f5f5;
            }

            .total {
                text-align: right;
                margin-top: 10px;
            }

            .meta {
                margin-top: 10px;
            }
        </style>
    </head>

    <body>

        <div class="header">
            <img src="<?= $logoSrc ?>" class="logo">
            <div class="company">
                <h2>TemaTech Innovations</h2>
                <p><strong>Invoice:</strong> <?= $invoiceNumber ?></p>
                <p><strong>Date:</strong> <?= date('Y-m-d') ?></p>
            </div>
        </div>

        <div class="meta">
            <p><strong>Customer Email:</strong> <?= htmlspecialchars($customer['email']) ?></p>
        </div>

        <table>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>K<?= number_format($item['price'], 2) ?></td>
                    <td>K<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="total">
            <p>Subtotal: K<?= number_format($subtotal, 2) ?></p>
            <p>Tax (16%): K<?= number_format($tax, 2) ?></p>
            <h3>Total: K<?= number_format($total, 2) ?></h3>
        </div>

    </body>

    </html>
<?php
    $html = ob_get_clean();

    // =========================
    // GENERATE PDF
    // =========================
    $options = new Options();
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Save PDF
    file_put_contents($filePath, $dompdf->output());

    // =========================
    // SAVE TO DB
    // =========================
    $stmt = $pdo->prepare("
        INSERT INTO invoices 
        (id, order_id, invoice_number, invoice_url, subtotal, tax, total)
        VALUES (UUID(), ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $order['id'],
        $invoiceNumber,
        $fileUrl,
        $subtotal,
        $tax,
        $total
    ]);

    return [
        'invoice_number' => $invoiceNumber,
        'invoice_url' => $fileUrl,
        'file_path' => $filePath, // needed for email attachment
        'customer_email' => $customer['email']
    ];
}
