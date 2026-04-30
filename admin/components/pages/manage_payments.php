<?php
require_once __DIR__ . '/../../../config/db.php';

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Pagination settings
$limit = 15; // items per page
$page = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;
$offset = ($page - 1) * $limit;

// Filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$method = $_GET['method'] ?? '';

// Summary statistics
$summaryQuery = "
SELECT
    SUM(CASE WHEN payment_status = 'successful' THEN total_amount ELSE 0 END) AS total_revenue,
    COUNT(CASE WHEN payment_status = 'successful' THEN 1 END) AS completed_payments,
    COUNT(CASE WHEN payment_status = 'pending' THEN 1 END) AS pending_payments,
    COUNT(CASE WHEN payment_status = 'failed' THEN 1 END) AS failed_payments,
    SUM(CASE WHEN payment_status = 'pending' THEN total_amount ELSE 0 END) AS pending_amount
FROM orders
";

$stmt = $pdo->prepare($summaryQuery);
$stmt->execute();
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

$totalRevenue      = $summary['total_revenue'] ?? 0;
$completedPayments = $summary['completed_payments'] ?? 0;
$pendingPayments   = $summary['pending_payments'] ?? 0;
$failedPayments    = $summary['failed_payments'] ?? 0;
$pendingAmount     = $summary['pending_amount'] ?? 0;

// Main query with pagination
$sql = "
SELECT
    o.id,
    o.order_number,
    o.total_amount,
    o.payment_status,
    o.payment_method,
    o.created_at,
    c.first_name,
    c.last_name
FROM orders o
LEFT JOIN customers c ON o.customer_id = c.id
WHERE 1=1
";

$params = [];

if (!empty($search)) {
    $sql .= " AND (o.order_number LIKE ? OR o.id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status)) {
    $sql .= " AND o.payment_status = ?";
    $params[] = $status;
}

if (!empty($method)) {
    $sql .= " AND o.payment_method = ?";
    $params[] = $method;
}

$sql .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total records for pagination
$countSql = "SELECT COUNT(*) as total FROM orders o WHERE 1=1";
$countParams = [];

if (!empty($search)) {
    $countSql .= " AND (o.order_number LIKE ? OR o.id LIKE ?)";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
}
if (!empty($status)) {
    $countSql .= " AND o.payment_status = ?";
    $countParams[] = $status;
}
if (!empty($method)) {
    $countSql .= " AND o.payment_method = ?";
    $countParams[] = $method;
}

$stmt = $pdo->prepare($countSql);
$stmt->execute($countParams);
$totalRecords = $stmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);
?>

<?php include __DIR__ . '/../../../admin/includes/header.php'; ?>
<link rel="stylesheet" href="../../../css/admin-dark.css">

<div class="admin-container">

    <!-- Controls Section -->
    <div class="controls-section">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search by order number or ID..."
                value="<?= htmlspecialchars($search) ?>" onkeyup="if(event.key === 'Enter') applyFilters()">
        </div>

        <div class="filters-group">
            <select id="statusFilter" onchange="applyFilters()">
                <option value="">All Status</option>
                <option value="successful" <?= $status === 'successful' ? 'selected' : '' ?>>Successful</option>
                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="failed" <?= $status === 'failed' ? 'selected' : '' ?>>Failed</option>
            </select>

            <select id="methodFilter" onchange="applyFilters()">
                <option value="">All Payment Methods</option>
                <option value="mobile-money" <?= $method === 'mobile-money' ? 'selected' : '' ?>>Mobile Money</option>
                <option value="card" <?= $method === 'card' ? 'selected' : '' ?>>Card</option>
                <option value="bank-transfer" <?= $method === 'bank-transfer' ? 'selected' : '' ?>>Bank Transfer</option>
            </select>

            <button class="btn-filter" onclick="applyFilters()">
                <i class="fas fa-filter"></i> Apply Filters
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-section">
        <div class="summary-card">
            <div class="summary-label">Total Revenue</div>
            <div class="summary-value">K<?= number_format($totalRevenue, 2) ?></div>
            <div class="summary-trend positive">From all successful payments</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Completed Payments</div>
            <div class="summary-value"><?= number_format($completedPayments) ?></div>
            <div class="summary-trend positive">Successful transactions</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Pending Payments</div>
            <div class="summary-value"><?= number_format($pendingPayments) ?></div>
            <div class="summary-trend neutral">K<?= number_format($pendingAmount, 2) ?> pending</div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Failed Transactions</div>
            <div class="summary-value"><?= number_format($failedPayments) ?></div>
            <div class="summary-trend negative">Requires attention</div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['order_number']) ?></strong></td>
                            <td><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></td>
                            <td class="price">K<?= number_format($p['total_amount'], 2) ?></td>
                            <td><?= htmlspecialchars(ucfirst(str_replace('-', ' ', $p['payment_method'] ?? 'N/A'))) ?></td>
                            <td>
                                <span class="badge <?= htmlspecialchars($p['payment_status']) ?>">
                                    <?= ucfirst($p['payment_status']) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y • H:i', strtotime($p['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">No payments found matching your filters.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page_num' => $page - 1])) ?>" class="page-link">Previous</a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page_num' => $i])) ?>"
                    class="page-link <?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page_num' => $page + 1])) ?>" class="page-link">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>


<script>
    function applyFilters() {
        const search = document.getElementById('searchInput').value.trim();
        const status = document.getElementById('statusFilter').value;
        const method = document.getElementById('methodFilter').value;

        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (status) params.append('status', status);
        if (method) params.append('method', method);

        window.location.href = '../index.php?page=payments' + (params.toString() ? '&' + params.toString() : '');
    }
</script>