<?php
// ================================
// DB CONNECTION + AUTH
// ================================
require_once __DIR__ . '/../../../config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// UPDATED AUTH CHECK - Using correct session key
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'customer') {
    header("Location: ../index.php?page=login");
    exit;
}

// Use correct customer ID from session
$customerId = $_SESSION['user_id'];

// ================================
// PAGINATION
// ================================
$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// ================================
// FILTERS
// ================================
$statusFilter = $_GET['status'] ?? 'all';
$dateFilter   = $_GET['date'] ?? 'all';
$sortBy       = $_GET['sort'] ?? 'newest';

// Build WHERE clause
$where = "WHERE o.customer_id = :customer_id";
$params = [':customer_id' => $customerId];

if ($statusFilter !== 'all') {
    $where .= " AND o.status = :status";
    $params[':status'] = $statusFilter;
}

if ($dateFilter === '30days') {
    $where .= " AND o.created_at >= NOW() - INTERVAL 30 DAY";
} elseif ($dateFilter === '60days') {
    $where .= " AND o.created_at >= NOW() - INTERVAL 60 DAY";
} elseif ($dateFilter === 'year') {
    $where .= " AND YEAR(o.created_at) = YEAR(NOW())";
}

// Sorting
$orderBy = "ORDER BY o.created_at DESC";
if ($sortBy === 'oldest') $orderBy = "ORDER BY o.created_at ASC";
elseif ($sortBy === 'amount-high') $orderBy = "ORDER BY o.total_amount DESC";
elseif ($sortBy === 'amount-low') $orderBy = "ORDER BY o.total_amount ASC";

// Count total for pagination
$countSql = "SELECT COUNT(DISTINCT o.id) AS total FROM orders o $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalOrders = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalOrders / $perPage);

// Main Query - Fetch orders with items
$sql = "
    SELECT 
        o.*,
        oi.quantity,
        p.name AS product_name,
        p.price AS product_price,
        pi.image_url
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    $where
    $orderBy
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
$params[':limit'] = $perPage;
$params[':offset'] = $offset;
$stmt->execute($params);

// Group items by order
$orders = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $orderId = $row['id'];
    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'id'              => $row['id'],
            'order_reference' => $row['order_reference'],
            'created_at'      => $row['created_at'],
            'total_amount'    => $row['total_amount'],
            'status'          => $row['status'],
            'items'           => []
        ];
    }
    if (!empty($row['product_name'])) {
        $orders[$orderId]['items'][] = [
            'name'     => $row['product_name'],
            'quantity' => $row['quantity'],
            'price'    => $row['product_price'],
            'image'    => $row['image_url']
        ];
    }
}
$orders = array_values($orders);
?>

<?php include __DIR__ . '/../../../users/includes/header.php'; ?>
<?php include __DIR__ . '/../../../includes/page-loader.php'; ?>

<div class="dashboard-container">

    <div class="page-header">
        <h1>My Orders</h1>
        <p>Track and manage your orders (<?= $totalOrders ?> total)</p>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="filters">
            <div class="filter-group">
                <select id="statusFilter" class="select" onchange="updateFilters()">
                    <option value="all">All Orders</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="paid" <?= $statusFilter === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="shipped" <?= $statusFilter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="delivered" <?= $statusFilter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>

            <div class="filter-group">
                <select id="dateFilter" class="select" onchange="updateFilters()">
                    <option value="all">All Time</option>
                    <option value="30days" <?= $dateFilter === '30days' ? 'selected' : '' ?>>Last 30 Days</option>
                    <option value="60days" <?= $dateFilter === '60days' ? 'selected' : '' ?>>Last 60 Days</option>
                    <option value="year" <?= $dateFilter === 'year' ? 'selected' : '' ?>>This Year</option>
                </select>
            </div>

            <div class="filter-group">
                <select id="sortBy" class="select" onchange="updateFilters()">
                    <option value="newest">Newest First</option>
                    <option value="oldest" <?= $sortBy === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="amount-high" <?= $sortBy === 'amount-high' ? 'selected' : '' ?>>Highest Amount</option>
                    <option value="amount-low" <?= $sortBy === 'amount-low' ? 'selected' : '' ?>>Lowest Amount</option>
                </select>
            </div>
        </div>
    </div>

    <?php if (empty($orders)): ?>
        <div class="card empty-state">
            <p>No orders found matching your filters.</p>
            <a href="index.php?page=products" class="btn btn-dark">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="card order-card">

                    <div class="order-header">
                        <div>
                            <div class="order-id">#<?= htmlspecialchars($order['order_reference'] ?? substr($order['id'], 0, 8)) ?></div>
                            <div class="order-date"><?= date('M d, Y • h:i A', strtotime($order['created_at'])) ?></div>
                        </div>

                        <span class="badge 
                            <?= in_array(strtolower($order['status']), ['delivered', 'paid']) ? 'badge-success' : (strtolower($order['status']) === 'shipped' ? 'badge-info' : (strtolower($order['status']) === 'pending' ? 'badge-warning' : 'badge-danger')) ?>">
                            <?= ucfirst(htmlspecialchars($order['status'])) ?>
                        </span>
                    </div>

                    <div class="order-items">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="order-item">
                                <div class="order-item-image">
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="/tematech-innovation/public/<?= htmlspecialchars($item['image']) ?>"
                                            alt="<?= htmlspecialchars($item['name']) ?>"
                                            style="width:60px;height:60px;object-fit:contain;">
                                    <?php else: ?>
                                        <i class="fas fa-box"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="order-item-details">
                                    <div class="order-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="order-item-qty">Qty: <?= $item['quantity'] ?></div>
                                </div>
                                <div class="order-item-price">
                                    K<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-footer">
                        <div>
                            <span class="muted">Total</span>
                            <div class="order-total">K<?= number_format($order['total_amount'], 2) ?></div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&status=<?= urlencode($statusFilter) ?>&date=<?= urlencode($dateFilter) ?>&sort=<?= urlencode($sortBy) ?>" class="page-btn">← Previous</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?page=<?= $i ?>&status=<?= urlencode($statusFilter) ?>&date=<?= urlencode($dateFilter) ?>&sort=<?= urlencode($sortBy) ?>"
                        class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&status=<?= urlencode($statusFilter) ?>&date=<?= urlencode($dateFilter) ?>&sort=<?= urlencode($sortBy) ?>" class="page-btn">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    function updateFilters() {
        const params = new URLSearchParams(window.location.search);

        const status = document.getElementById('statusFilter').value;
        const date = document.getElementById('dateFilter').value;
        const sort = document.getElementById('sortBy').value;

        params.set('status', status);
        params.set('date', date);
        params.set('sort', sort);

        // Remove empty 'all' values to keep URL clean
        if (status === 'all') params.delete('status');
        if (date === 'all') params.delete('date');
        if (sort === 'newest') params.delete('sort');

        window.location.href = 'index.php?page=orders' + (params.toString() ? '&' + params.toString() : '');
    }
</script>

<style>
    :root {
        --bg-primary: #0f172a;
        --bg-secondary: #1e2937;
        --bg-card: #1e2937;
        --text-primary: #f1f5f9;
        --text-secondary: #cbd5e1;
        --border-color: #334155;
        --accent: #FF6B35;
        --success: #34d399;
        --warning: #fbbf24;
        --danger: #f87171;
        --info: #60a5fa;
    }

    body {
        background: var(--bg-primary);
        font-family: Inter, system-ui, sans-serif;
        color: var(--text-primary);
    }

    .page-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 4px;
        color: var(--text-primary);
    }

    .page-header p {
        color: #94a3b8;
    }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .filters {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .select {
        padding: 10px 14px;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        background: var(--bg-secondary);
        color: var(--text-primary);
        font-size: 14px;
        min-width: 160px;
    }

    .select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.2);
    }

    .orders-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .order-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        transition: all 0.2s;
    }

    .order-card:hover {
        border-color: #475569;
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .order-id {
        font-weight: 600;
        font-size: 15px;
        color: var(--text-primary);
    }

    .order-date {
        font-size: 13px;
        color: #94a3b8;
    }

    .order-items {
        margin: 15px 0;
    }

    .order-item {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .order-item:last-child {
        border-bottom: none;
    }

    .order-item-image img {
        width: 60px;
        height: 60px;
        object-fit: contain;
        border-radius: 8px;
        background: var(--bg-secondary);
        padding: 4px;
    }

    .order-item-details {
        flex: 1;
    }

    .order-item-name {
        font-weight: 500;
        color: var(--text-primary);
    }

    .order-item-qty {
        color: #94a3b8;
        font-size: 13px;
    }

    .order-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid var(--border-color);
    }

    .order-total {
        font-size: 18px;
        font-weight: 700;
        color: var(--accent);
    }

    /* Badges */
    .badge {
        padding: 6px 14px;
        border-radius: 9999px;
        font-size: 13px;
        font-weight: 500;
    }

    .badge-success {
        background: rgba(52, 211, 153, 0.2);
        color: var(--success);
    }

    .badge-warning {
        background: rgba(251, 191, 36, 0.2);
        color: var(--warning);
    }

    .badge-info {
        background: rgba(96, 165, 250, 0.2);
        color: var(--info);
    }

    .badge-danger {
        background: rgba(248, 113, 113, 0.2);
        color: var(--danger);
    }

    .btn-dark {
        background: var(--accent);
        color: white;
        padding: 10px 18px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-dark:hover {
        background: #e55a28;
        transform: translateY(-1px);
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 30px;
    }

    .page-btn {
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        text-decoration: none;
        color: var(--text-secondary);
        background: var(--bg-secondary);
        transition: all 0.2s;
    }

    .page-btn:hover {
        border-color: var(--accent);
        color: var(--accent);
    }

    .page-btn.active {
        background: var(--accent);
        color: white;
        border-color: var(--accent);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .filters {
            flex-direction: column;
        }

        .select {
            width: 100%;
        }

        .profile-main {
            grid-template-columns: 1fr;
        }
    }
</style>