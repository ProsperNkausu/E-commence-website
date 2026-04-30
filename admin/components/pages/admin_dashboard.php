<?php
require __DIR__ . '/../../../config/db.php';
// Get admin data from session
$adminName = ($_SESSION['admin_first_name'] ?? '') . ' ' . ($_SESSION['admin_last_name'] ?? '');
$adminRole = ucfirst($_SESSION['admin_role_name'] ?? '');

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

/* ===============================
   SALES - THIS MONTH
================================ */
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(total_amount),0)
    FROM orders
    WHERE MONTH(created_at) = MONTH(CURDATE())
    AND YEAR(created_at) = YEAR(CURDATE())
    AND payment_status = 'successful'
");
$stmt->execute();
$totalSales = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| Recent Orders
|--------------------------------------------------------------------------
*/
$stmt = $pdo->query("
    SELECT 
        o.id,
        o.order_number,
        o.total_amount,
        o.created_at,
        o.status,
        c.first_name,
        c.last_name
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    WHERE o.status IN ('pending', 'paid')
    ORDER BY o.created_at DESC
    LIMIT 3
");
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Latest Paid Orders with Invoice
|-------------------------------------------------------------------------- 
*/
$stmt = $pdo->query("
    SELECT 
        o.order_reference,
        o.total_amount,
        o.created_at,
        c.first_name,
        c.last_name,
        i.invoice_number,
        i.invoice_url
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    LEFT JOIN invoices i ON i.order_id = o.id
    WHERE o.payment_status = 'successful'
    ORDER BY o.created_at DESC
    LIMIT 5
");
$paidOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Cancelled Orders
|--------------------------------------------------------------------------
*/
$stmt = $pdo->query("
    SELECT 
        o.id,
        o.order_number,
        o.total_amount,
        o.created_at,
        c.first_name,
        c.last_name
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    WHERE o.status = 'cancelled'
    ORDER BY o.created_at DESC
    LIMIT 2
");
$cancelledOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   SALES - LAST MONTH
================================ */
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(total_amount),0)
    FROM orders
    WHERE MONTH(created_at) = MONTH(CURDATE() - INTERVAL 1 MONTH)
    AND YEAR(created_at) = YEAR(CURDATE() - INTERVAL 1 MONTH)
    AND payment_status = 'completed'
");
$stmt->execute();
$lastMonthSales = $stmt->fetchColumn();

/* ===============================
   SALES CHANGE %
================================ */
$salesChange = 0;

if ($lastMonthSales > 0) {
    $salesChange = (($totalSales - $lastMonthSales) / $lastMonthSales) * 100;
}

/* ===============================
   ORDERS THIS MONTH
================================ */
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM orders
    WHERE MONTH(created_at) = MONTH(CURDATE())
    AND YEAR(created_at) = YEAR(CURDATE())
");
$stmt->execute();
$totalOrders = $stmt->fetchColumn();

/* ===============================
   ORDERS LAST MONTH
================================ */
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM orders
    WHERE MONTH(created_at) = MONTH(CURDATE() - INTERVAL 1 MONTH)
    AND YEAR(created_at) = YEAR(CURDATE() - INTERVAL 1 MONTH)
");
$stmt->execute();
$lastMonthOrders = $stmt->fetchColumn();

$orderChange = 0;

if ($lastMonthOrders > 0) {
    $orderChange = (($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100;
}

/* ===============================
   TOTAL CUSTOMERS
================================ */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM customers");
$stmt->execute();
$totalCustomers = $stmt->fetchColumn();

/* ===============================
   NEW CUSTOMERS THIS MONTH
================================ */
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM customers
    WHERE MONTH(created_at) = MONTH(CURDATE())
    AND YEAR(created_at) = YEAR(CURDATE())
");
$stmt->execute();
$newCustomers = $stmt->fetchColumn();

/* ===============================
   TOTAL PRODUCTS
================================ */
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products");
$stmt->execute();
$totalProducts = $stmt->fetchColumn();

/* ===============================
   OUT OF STOCK
================================ */
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM products
    WHERE stock_quantity = 0
");
$stmt->execute();
$outOfStock = $stmt->fetchColumn();

/* ===============================
   LOW STOCK
================================ */
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM products
    WHERE stock_quantity > 0
    AND stock_quantity <= 10
");
$stmt->execute();
$lowStock = $stmt->fetchColumn();
?>

<?php include __DIR__ . '/../../../admin/includes/header.php'; ?>

<link rel="stylesheet" href="/tematech-innovation/admin/css/admin-dark.css">

<div class="admin-container">
    <div class="dashboard-layout">
        <!-- Left Column - Stat Cards -->
        <div class="left-column">
            <!-- Total Sales Card -->
            <div class="stat-card sales">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3>Total Sales</h3>
                </div>
                <div class="stat-value">K<?= number_format($totalSales, 2) ?></div>
                <div class="stat-subtitle">This Month</div>
                <div class="stat-footer">
                    <span class="stat-change positive"><?= round($salesChange) ?>% from last month</span>
                </div>
            </div>

            <!-- Total Orders Card -->
            <div class="stat-card orders">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h3>Total Orders</h3>
                </div>
                <div class="stat-value"><?php echo $totalOrders; ?></div>
                <div class="stat-subtitle">This Month</div>
                <div class="stat-footer">
                    <span class="stat-change positive"><?= round($orderChange) ?>% from last month</span>
                </div>
            </div>

            <!-- Total Customers Card -->
            <div class="stat-card customers">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Total Customers</h3>
                </div>
                <div class="stat-value"><?= $totalCustomers ?></div>
                <div class="stat-subtitle">Active Users</div>
                <div class="stat-footer">
                    <span class="stat-change positive"><?= $newCustomers ?> new this month</span>
                </div>
            </div>

            <!-- Total Products Card -->
            <div class="stat-card products">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3>Total Products</h3>
                </div>
                <div class="stat-value"><?= $totalProducts ?></div>
                <div class="stat-subtitle">In Inventory</div>
                <div class="stat-footer">
                    <span class="stat-change neutral"><?= $lowStock ?> low stock alerts</span>
                </div>
            </div>

            <!-- Out of Stock Card -->
            <div class="stat-card outofstock">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h3>Out of Stock</h3>
                </div>
                <div class="stat-value"><?= $outOfStock ?></div>
                <div class="stat-subtitle">Products</div>
                <div class="stat-footer">
                    <a href="../index.php?page=products" class="stat-link">View Products</a>
                </div>
            </div>
        </div>

        <!-- Right Column - Notifications -->
        <div class="right-column">
            <!-- Recent & Cancelled Orders -->
            <div class="notification-card">
                <div class="card-header">
                    <h3>Recent Activity</h3>
                    <a href="../index.php?page=orders" class="view-all">View All</a>
                </div>

                <div class="activity-section">
                    <h4 class="section-title">Recent Orders</h4>
                    <div class="activity-list">

                        <?php foreach ($recentOrders as $order): ?>

                            <div class="activity-item view-order" data-id="<?= $order['id'] ?>">

                                <div class="activity-icon 
        <?= $order['status'] === 'paid' ? 'completed' : 'pending' ?>">
                                    <i class="fas 
            <?= $order['status'] === 'paid' ? 'fa-check-circle' : 'fa-clock' ?>">
                                    </i>
                                </div>

                                <div class="activity-info">
                                    <div class="activity-title">
                                        Order #<?= htmlspecialchars($order['order_number']) ?>
                                    </div>

                                    <div class="activity-customer">
                                        <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?>
                                    </div>

                                    <div class="activity-time">
                                        <?= date('M d, H:i', strtotime($order['created_at'])) ?>
                                    </div>
                                </div>

                                <div class="activity-amount">
                                    K<?= number_format($order['total_amount'], 2) ?>
                                </div>
                            </div>

                        <?php endforeach; ?>

                    </div>
                </div>



                <!--  -->

                <div class="divider"></div>

                <div class="activity-section">
                    <h4 class="section-title">Paid Orders (Invoices)</h4>

                    <div class="activity-list">
                        <?php foreach ($paidOrders as $order): ?>

                            <div class="activity-item">
                                <div class="activity-icon completed">
                                    <i class="fas fa-check-circle"></i>
                                </div>

                                <div class="activity-info">
                                    <div class="activity-title">
                                        <?= htmlspecialchars($order['invoice_number'] ?? 'No Invoice') ?>
                                    </div>

                                    <div class="activity-customer">
                                        <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?>
                                    </div>

                                    <div class="activity-time">
                                        <?= date('M d, H:i', strtotime($order['created_at'])) ?>
                                    </div>
                                </div>

                                <div class="activity-amount">
                                    K<?= number_format($order['total_amount'], 2) ?>
                                </div>

                                <?php if (!empty($order['invoice_url'])): ?>
                                    <a href="<?= $order['invoice_url'] ?>"
                                        target="_blank"
                                        class="stat-link">
                                        View
                                    </a>
                                <?php endif; ?>
                            </div>

                        <?php endforeach; ?>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>

<!-- model -->
<div id="orderModal" class="modal">
    <div class="modal-content">

        <!-- Header -->
        <div class="modal-header">
            <h2 id="modalTitle">Order Details</h2>
            <span class="close">&times;</span>
        </div>

        <!-- Body -->
        <div id="modalBody" class="modal-body">
            Loading...
        </div>

    </div>
</div>

<script>
    document.querySelectorAll('.view-order').forEach(item => {
        item.addEventListener('click', async () => {
            const orderId = item.dataset.id;

            const modal = document.getElementById('orderModal');
            const modalBody = document.getElementById('modalBody');

            modal.style.display = 'block';
            modalBody.innerHTML = 'Loading...';

            try {
                const res = await fetch(`/tematech-innovation/api/get-order-details.php?id=${orderId}`);
                const html = await res.text();

                modalBody.innerHTML = html;
            } catch (err) {
                modalBody.innerHTML = 'Error loading order';
            }
        });
    });

    // Close modal
    document.querySelector('.close').onclick = () => {
        document.getElementById('orderModal').style.display = 'none';
    };

    window.onclick = (e) => {
        if (e.target.id === 'orderModal') {
            document.getElementById('orderModal').style.display = 'none';
        }
    };
</script>

<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        backdrop-filter: blur(4px);
    }

    .modal-content {
        background: var(--bg-primary);
        width: 720px;
        max-width: 95%;
        margin: 60px auto;
        border-radius: 14px;
        overflow: hidden;
        animation: modalFade 0.25s ease;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
    }

    /* Header */
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 22px;
        border-bottom: 1px solid #e5e7eb;
        /* background: #f9fafb; */
    }

    .modal-header h2 {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
        color: var(--accent);
    }

    .close {
        font-size: 22px;
        cursor: pointer;
        color: var(--text-secondary);
        transition: 0.2s;
    }

    .close:hover {
        color: #ef4444;
    }

    /* Body */
    .modal-body {
        padding: 20px;
        max-height: 70vh;
        overflow-y: auto;
    }

    /* Sections inside modal */
    .modal-section {
        margin-bottom: 20px;
    }

    .modal-section h3 {
        font-size: 14px;
        font-weight: 700;
        color: var(--accent);
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    /* Table */
    .modal-table {
        width: 100%;
        border-collapse: collapse;
    }

    .modal-table th {
        text-align: left;
        font-size: 12px;
        color: var(--accent);
        border-bottom: 1px solid #e5e7eb;
        padding: 8px;
    }

    .modal-table td {
        padding: 10px 8px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }

    /* Footer total */
    .modal-total {
        text-align: right;
        font-weight: 700;
        font-size: 16px;
        margin-top: 10px;
    }

    /* Button */
    .btn-invoice {
        display: inline-block;
        margin-top: 10px;
        background: #FF6B35;
        color: #fff;
        padding: 8px 14px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        transition: 0.2s;
    }

    .btn-invoice:hover {
        background: #e85a2a;
    }

    /* Animation */
    @keyframes modalFade {
        from {
            opacity: 0;
            transform: translateY(-10px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .activity-item {
        transition: all 0.25s ease;
        cursor: pointer;
    }

    .activity-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
    }

    /* subtle click feedback */
    .activity-item:active {
        transform: scale(0.98);
    }

    .dashboard-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 24px;
        height: calc(100vh - 150px);
    }

    /* Left Column - Stat Cards */
    .left-column {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-content: flex-start;
    }

    .stat-card {
        flex: 0 1 calc(33.333% - 14px);
        /* background: #fff; */
        /* border: 1px solid #e5e7eb; */
        border-radius: 12px;
        padding: 24px;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .stat-card:nth-child(4),
    .stat-card:nth-child(5) {
        flex: 0 1 calc(50% - 10px);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        border-radius: 12px 12px 0 0;
    }

    .stat-card.sales::before {
        background: linear-gradient(90deg, #FF6B35 0%, #ff8c5a 100%);
    }

    .stat-card.orders::before {
        background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 100%);
    }

    .stat-card.customers::before {
        background: linear-gradient(90deg, #10b981 0%, #34d399 100%);
    }

    .stat-card.products::before {
        background: linear-gradient(90deg, #8b5cf6 0%, #a78bfa 100%);
    }

    .stat-card.outofstock::before {
        background: linear-gradient(90deg, #ef4444 0%, #f87171 100%);
    }

    .stat-card:hover {
        border-color: #FF6B35;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.1);
        transform: translateY(-2px);
    }

    .stat-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #fff;
    }

    .stat-card.sales .stat-icon {
        background: linear-gradient(135deg, #FF6B35 0%, #ff8c5a 100%);
    }

    .stat-card.orders .stat-icon {
        background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
    }

    .stat-card.customers .stat-icon {
        background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    }

    .stat-card.products .stat-icon {
        background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
    }

    .stat-card.outofstock .stat-icon {
        background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
    }

    .stat-header h3 {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .stat-subtitle {
        font-size: 12px;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .stat-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .stat-change {
        font-size: 12px;
        font-weight: 600;
    }

    .stat-change.positive {
        color: #10b981;
    }

    .stat-change.neutral {
        color: #FF6B35;
    }

    .stat-link {
        font-size: 12px;
        color: #FF6B35;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }

    .stat-link:hover {
        text-decoration: underline;
    }

    /* Right Column - Notifications */
    .right-column {
        display: flex;
        flex-direction: column;
        gap: 24px;
        max-height: 100%;
        overflow-y: auto;
        padding-right: 8px;
    }

    .right-column::-webkit-scrollbar {
        width: 6px;
    }

    .right-column::-webkit-scrollbar-track {
        /* background: transparent; */

    }

    .right-column::-webkit-scrollbar-thumb {
        /* background: #d1d5db; */
        border-radius: 3px;
    }

    .right-column::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }



    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: var(--text-primary);
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .view-all {
        font-size: 12px;
        color: #FF6B35;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }

    .view-all:hover {
        text-decoration: underline;
    }

    /* Activity Section */
    .activity-section {
        margin-bottom: 16px;
    }

    .activity-section:last-child {
        margin-bottom: 0;
    }

    .section-title {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-primary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0 0 12px 0;
    }

    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: var(--bg-primary);
        border-radius: 8px;
        transition: all 0.2s;
    }

    .activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .activity-icon.completed {
        background: #d1fae5;
        color: #10b981;
    }

    .activity-icon.cancelled {
        background: #fee2e2;
        color: #ef4444;
    }

    .activity-info {
        flex: 1;
    }

    .activity-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .activity-customer {
        font-size: 12px;
        color: var(--text-secondary);
    }

    .activity-time {
        font-size: 11px;
        color: var(--text-tertiary);
    }

    .activity-amount {
        font-size: 13px;
        font-weight: 700;
        color: #FF6B35;
        white-space: nowrap;
    }

    .divider {
        height: 1px;
        background: #e5e7eb;
        margin: 16px 0;
    }

    /* Support List */
    .support-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .support-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: #f9fafb;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .support-item:hover {
        background: #f3f4f6;
    }

    .support-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #FF6B35 0%, #ff8c5a 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .support-info {
        flex: 1;
    }

    .support-name {
        font-size: 13px;
        font-weight: 600;
        color: #111827;
    }

    .support-message {
        font-size: 12px;
        color: #6b7280;
    }

    .support-time {
        font-size: 11px;
        color: #9ca3af;
    }

    .support-status {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .support-status.urgent {
        background: #fee2e2;
        color: #ef4444;
    }

    .support-status.normal {
        background: #dbeafe;
        color: #3b82f6;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .dashboard-layout {
            grid-template-columns: 1fr 350px;
        }

        .stat-value {
            font-size: 28px;
        }
    }

    @media (max-width: 1024px) {
        .dashboard-layout {
            grid-template-columns: 1fr;
        }

        .left-column {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }

        .stat-card {
            flex: 0 1 calc(50% - 8px);
            padding: 16px;
        }

        .stat-card:nth-child(4),
        .stat-card:nth-child(5) {
            flex: 0 1 calc(50% - 8px);
        }

        .stat-value {
            font-size: 24px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            font-size: 20px;
        }

        .right-column {
            max-height: 500px;
        }
    }

    @media (max-width: 768px) {
        .left-column {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .stat-card {
            flex: 0 1 calc(50% - 6px);
        }

        .stat-card:nth-child(4),
        .stat-card:nth-child(5) {
            flex: 0 1 calc(50% - 6px);
        }

        .stat-header h3 {
            font-size: 12px;
        }

        .stat-value {
            font-size: 20px;
        }

        .notification-card {
            padding: 16px;
        }

        .card-header h3 {
            font-size: 14px;
        }

        .activity-item,
        .support-item {
            padding: 10px;
        }

        .activity-title,
        .support-name {
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        .left-column {
            grid-template-columns: 1fr;
        }

        .stat-card {
            padding: 12px;
        }

        .stat-value {
            font-size: 18px;
        }

        .stat-icon {
            width: 36px;
            height: 36px;
            font-size: 18px;
        }

        .activity-item,
        .support-item {
            flex-direction: column;
            text-align: center;
        }

        .activity-amount {
            text-align: center;
        }
    }
</style>

</body>

</html>