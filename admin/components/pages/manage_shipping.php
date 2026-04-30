<?php
include __DIR__ . '/../../includes/header.php';

// Generate CSRF Token (kept for future use if needed)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Fetch all orders
$stmt = $pdo->prepare("
    SELECT o.id, o.order_number, o.order_reference, o.total_amount, 
           o.status, o.payment_status, o.payment_method, o.created_at, o.updated_at,
           o.customer_first_name, o.customer_last_name, 
           o.customer_email, o.customer_phone,
           o.customer_address, o.customer_city, o.customer_country
    FROM orders o
    ORDER BY o.created_at DESC
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="admin-container">
    <div class="page-header">
        <h1><i class="fas fa-truck-loading"></i> Shipping Overview</h1>
        <p>Track shipping status of all orders • Default: Not Shipped</p>
    </div>

    <!-- Filters -->
    <div class="filters">
        <select id="statusFilter" onchange="filterOrders()">
            <option value="">All Orders</option>
            <option value="shipped">Shipped</option>
            <option value="not_shipped">Not Shipped</option>
        </select>

        <input type="text" id="searchInput" placeholder="Search by Order Number or Customer..." onkeyup="filterOrders()">
    </div>

    <div class="table-container">
        <table class="shipping-table" id="shippingTable">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Shipping Status</th>
                    <th>Order Date</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order):
                    // Determine shipping status
                    $isShipped = ($order['status'] === 'shipped');
                    $shippingStatus = $isShipped ? 'Shipped' : 'Not Shipped';
                    $statusClass = $isShipped ? 'shipped' : 'not-shipped';
                ?>
                    <tr data-id="<?= htmlspecialchars($order['id']) ?>"
                        data-shipping="<?= $isShipped ? 'shipped' : 'not_shipped' ?>">
                        <td>
                            <strong><?= htmlspecialchars($order['order_number']) ?></strong><br>
                            <small><?= htmlspecialchars($order['order_reference'] ?? '-') ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($order['customer_first_name'] . ' ' . $order['customer_last_name']) ?><br>
                            <small><?= htmlspecialchars($order['customer_email']) ?></small>
                        </td>
                        
                        
                        <td>
                            <span class="shipping-status <?= $statusClass ?>">
                                <i class="fas <?= $isShipped ? 'fa-check-circle' : 'fa-clock' ?>"></i>
                                <?= $shippingStatus ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
                        <td><?= $order['updated_at'] ? date('M d, Y H:i', strtotime($order['updated_at'])) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" class="no-data">No orders found in the system.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="toast"></div>

<style>
    .page-header h1 {
        color: #f1f5f9;
        font-size: 28px;
        margin-bottom: 8px;
    }

    .page-header p {
        color: #94a3b8;
    }

    .filters {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .filters select,
    .filters input {
        padding: 12px 16px;
        background: #1e2937;
        border: 1px solid #334155;
        border-radius: 8px;
        color: #e2e8f0;
        font-size: 15px;
    }

    .filters input {
        flex: 1;
        min-width: 300px;
    }

    .shipping-table {
        width: 100%;
        border-collapse: collapse;
        background: #1e2937;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .shipping-table th {
        background: #0f172a;
        padding: 18px 16px;
        text-align: left;
        color: #94a3b8;
        font-weight: 600;
    }

    .shipping-table td {
        padding: 16px;
        border-bottom: 1px solid #334155;
        color: #e2e8f0;
    }

    .shipping-status {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .shipping-status.shipped {
        background: #166534;
        color: #86efac;
    }

    .shipping-status.not-shipped {
        background: #78350f;
        color: #fcd34d;
    }

    .payment-badge {
        background: #334155;
        color: #cbd5e1;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 13px;
    }

    .no-data {
        text-align: center;
        padding: 40px;
        color: #94a3b8;
        font-style: italic;
    }

    .toast {
        position: fixed;
        bottom: 30px;
        right: 30px;
        padding: 14px 24px;
        border-radius: 8px;
        color: white;
        display: none;
        z-index: 3000;
        animation: slideUp 0.4s ease;
    }

    @keyframes slideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<script>
    function filterOrders() {
        const filterValue = document.getElementById('statusFilter').value;
        const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
        const rows = document.querySelectorAll('#shippingTable tbody tr');

        rows.forEach(row => {
            if (row.classList.contains('no-data')) return;

            const shippingStatus = row.dataset.shipping;
            const rowText = row.textContent.toLowerCase();

            const matchesFilter = !filterValue ||
                (filterValue === 'shipped' && shippingStatus === 'shipped') ||
                (filterValue === 'not_shipped' && shippingStatus === 'not_shipped');

            const matchesSearch = !searchTerm || rowText.includes(searchTerm);

            row.style.display = (matchesFilter && matchesSearch) ? '' : 'none';
        });
    }

    // Simple toast function (for future use)
    function showToast(message, type = "success") {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.style.background = type === "error" ? "#7f1d1d" : "#166534";
        toast.style.display = "block";

        setTimeout(() => {
            toast.style.opacity = "0";
            setTimeout(() => {
                toast.style.display = "none";
                toast.style.opacity = "1";
            }, 400);
        }, 4000);
    }
</script>