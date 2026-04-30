<?php
require __DIR__ . '../../config/db.php';

$orderId = $_GET['id'] ?? null;

if (!$orderId) {
    echo "Invalid order";
    exit;
}

/* ORDER */
$stmt = $pdo->prepare("
    SELECT o.*, c.first_name, c.last_name, c.email
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

/* ITEMS */
$stmt = $pdo->prepare("
    SELECT oi.*, p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* INVOICE */
$stmt = $pdo->prepare("
    SELECT * FROM invoices WHERE order_id = ?
");
$stmt->execute([$orderId]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "Order not found";
    exit;
}
?>

<h2>Order #<?= htmlspecialchars($order['order_number']) ?></h2>

<div class="modal-section">
    <h3>Status</h3>
    <p>
        <strong><?= ucfirst($order['status']) ?></strong> |
        Payment: <?= ucfirst($order['payment_status']) ?>
    </p>
</div>

<div class="modal-section">
    <h3>Customer</h3>
    <p><?= $order['first_name'] ?> <?= $order['last_name'] ?></p>
    <p><?= $order['email'] ?></p>
</div>

<div class="modal-section">
    <h3>Items</h3>

    <table class="modal-table">
        <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Total</th>
        </tr>

        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= $item['name'] ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>K<?= number_format($item['price'], 2) ?></td>
                <td>K<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="modal-total">
        Total: K<?= number_format($order['total_amount'], 2) ?>
    </div>
</div>

<?php if ($invoice): ?>
    <div class="modal-section">
        <h3>Invoice</h3>
        <a href="<?= $invoice['invoice_url'] ?>" target="_blank" class="btn-invoice">
            Download Invoice
        </a>
    </div>
<?php endif; ?>