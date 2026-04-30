<?php
require __DIR__ . '/../../../config/db.php';

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    echo "<script>
        alert('Access Denied! Only administrators can access this page.');
        window.location.href = 'index.php?page=dashboard';
    </script>";
    exit;
}

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Helper function for month-over-month comparison
function getMonthSales($pdo, $monthsAgo = 0)
{
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount), 0) 
        FROM orders 
        WHERE YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL ? MONTH))
          AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL ? MONTH))
          AND payment_status = 'successful'
    ");
    $stmt->execute([$monthsAgo, $monthsAgo]);
    return $stmt->fetchColumn();
}

// ================================
// 1. TOTAL SALES THIS MONTH
// ================================
$totalSalesThisMonth = getMonthSales($pdo, 0);
$totalSalesLastMonth = getMonthSales($pdo, 1);

$salesChange = $totalSalesLastMonth > 0
    ? round((($totalSalesThisMonth - $totalSalesLastMonth) / $totalSalesLastMonth) * 100, 1)
    : 0;

// ================================
// 2. TOTAL ORDERS THIS MONTH
// ================================
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM orders 
    WHERE YEAR(created_at) = YEAR(CURDATE()) 
      AND MONTH(created_at) = MONTH(CURDATE())
");
$stmt->execute();
$totalOrdersThisMonth = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM orders 
    WHERE YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
      AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
");
$stmt->execute();
$totalOrdersLastMonth = $stmt->fetchColumn();

$orderChange = $totalOrdersLastMonth > 0
    ? round((($totalOrdersThisMonth - $totalOrdersLastMonth) / $totalOrdersLastMonth) * 100, 1)
    : 0;

// ================================
// 3. CUSTOMERS
// ================================
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM customers 
    WHERE YEAR(created_at) = YEAR(CURDATE()) 
      AND MONTH(created_at) = MONTH(CURDATE())
");
$stmt->execute();
$newCustomersThisMonth = $stmt->fetchColumn();

// ================================
// 4. PRODUCTS & LOW STOCK
// ================================
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

$lowStock = $pdo->query("
    SELECT COUNT(*) FROM products 
    WHERE stock_quantity <= 10 AND status = 'active'
")->fetchColumn();

// ================================
// 5. TOP 5 PRODUCTS (by quantity sold)
// ================================
$stmt = $pdo->query("
    SELECT 
        p.name,
        SUM(oi.quantity) AS total_sold,
        SUM(oi.quantity * oi.price) AS revenue
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE o.payment_status = 'successful'
    GROUP BY p.id, p.name
    ORDER BY total_sold DESC, revenue DESC
    LIMIT 5
");
$topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ================================
// 6. SALES LAST 7 DAYS (for chart)
// ================================
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) AS sale_date,
        COALESCE(SUM(total_amount), 0) AS daily_total
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
      AND payment_status = 'successful'
    GROUP BY DATE(created_at)
    ORDER BY sale_date ASC
");
$stmt->execute();
$salesData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Fill last 7 days (including days with 0 sales)
$chartData = [];
$chartLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayName = date('D', strtotime($date)); // Mon, Tue, etc.

    $chartData[] = $salesData[$date] ?? 0;
    $chartLabels[] = $dayName;
}

include __DIR__ . '/../../../admin/includes/header.php';
?>

<div class="admin-container">

    <!-- Stats Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon sales"><i class="fas fa-chart-line"></i></div>
                <h3>Total Sales</h3>
            </div>
            <div class="stat-value">K<?= number_format($totalSalesThisMonth, 2) ?></div>
            <div class="stat-period">This Month</div>
            <div class="stat-change <?= $salesChange >= 0 ? 'positive' : 'negative' ?>">
                <?= $salesChange >= 0 ? '+' : '' ?><?= $salesChange ?>% from last month
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon orders"><i class="fas fa-shopping-bag"></i></div>
                <h3>Total Orders</h3>
            </div>
            <div class="stat-value"><?= number_format($totalOrdersThisMonth) ?></div>
            <div class="stat-period">This Month</div>
            <div class="stat-change <?= $orderChange >= 0 ? 'positive' : 'negative' ?>">
                <?= $orderChange >= 0 ? '+' : '' ?><?= $orderChange ?>% from last month
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon customers"><i class="fas fa-users"></i></div>
                <h3>Total Customers</h3>
            </div>
            <div class="stat-value"><?= number_format($totalCustomers) ?></div>
            <div class="stat-period">Registered</div>
            <div class="stat-change positive">
                +<?= $newCustomersThisMonth ?> new this month
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon products"><i class="fas fa-box"></i></div>
                <h3>Inventory</h3>
            </div>
            <div class="stat-value"><?= number_format($totalProducts) ?></div>
            <div class="stat-period">Total Products</div>
            <div class="stat-change neutral">
                <?= $lowStock ?> low stock items
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <!-- Sales Trend Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>Sales Trend (Last 7 Days)</h3>
            </div>
            <div class="chart-placeholder">
                <div class="bars">
                    <?php
                    $maxSales = !empty($chartData) ? max($chartData) : 1;
                    foreach ($chartData as $value):
                        $height = ($value / $maxSales) * 100;
                    ?>
                        <div class="bar" style="height: <?= $height ?>%;"></div>
                    <?php endforeach; ?>
                </div>
                <div class="chart-labels">
                    <?php foreach ($chartLabels as $label): ?>
                        <span><?= htmlspecialchars($label) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>Top 5 Products</h3>
                <a href="../index.php?page=products" class="view-more">View All →</a>
            </div>
            <div class="top-products">
                <?php if (empty($topProducts)): ?>
                    <p>No sales data yet.</p>
                <?php else: ?>
                    <?php foreach ($topProducts as $index => $p): ?>
                        <div class="product-row">
                            <div class="product-rank"><?= $index + 1 ?></div>
                            <div class="product-info">
                                <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="product-sales"><?= number_format($p['total_sold']) ?> sold</div>
                            </div>
                            <div class="product-revenue">
                                K<?= number_format($p['revenue'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Additional Stats (Dynamic where possible) -->
    <div class="detailed-stats">


        <!-- Daily Revenue (Last 5 days example - can be extended) -->
        <div class="detail-card">
            <div class="card-header">
                <h3>Recent Daily Revenue</h3>
            </div>
            <div class="revenue-list">
                <?php
                // Reuse chart data for recent days (reverse for display)
                $recentDays = array_slice(array_reverse($chartLabels), 0, 5);
                $recentValues = array_slice(array_reverse($chartData), 0, 5);
                $maxRecent = max($recentValues) ?: 1;

                foreach ($recentDays as $idx => $day):
                    $val = $recentValues[$idx];
                    $width = ($val / $maxRecent) * 100;
                ?>
                    <div class="revenue-item">
                        <span class="day-name"><?= $day ?></span>
                        <div class="revenue-bar">
                            <div class="bar-fill" style="width: <?= $width ?>%;"></div>
                        </div>
                        <span class="day-revenue">K<?= number_format($val, 2) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* ================================================
   DASHBOARD PAGE - DARK MODE
   ================================================ */

    /* Container */
    .admin-container {
        max-width: 1200px;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        border-color: var(--accent);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.15);
        transform: translateY(-2px);
    }

    .stat-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }

    .stat-icon.sales {
        background: linear-gradient(135deg, #FF6B35 0%, #ff8c5a 100%);
    }

    .stat-icon.orders {
        background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
    }

    .stat-icon.customers {
        background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    }

    .stat-icon.products {
        background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
    }

    .stat-header h3 {
        font-size: 14px;
        font-weight: 600;
        color: #94a3b8;
        margin: 0;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }

    .stat-period {
        font-size: 12px;
        color: #94a3b8;
        margin-bottom: 8px;
    }

    .stat-change {
        font-size: 12px;
        font-weight: 600;
    }

    .stat-change.positive {
        color: var(--success);
    }

    .stat-change.neutral {
        color: var(--accent);
    }

    /* Charts Section */
    .charts-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .chart-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .chart-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .chart-filter {
        padding: 8px 12px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 12px;
        color: var(--text-primary);
    }

    .view-more {
        font-size: 12px;
        color: var(--accent);
        text-decoration: none;
        font-weight: 600;
    }

    .view-more:hover {
        text-decoration: underline;
    }

    /* Chart Placeholder (Bars) */
    .chart-placeholder {
        height: 200px;
        display: flex;
        align-items: flex-end;
        justify-content: space-around;
        padding: 20px 0;
    }

    .bars {
        display: flex;
        align-items: flex-end;
        justify-content: space-around;
        width: 100%;
        height: 150px;
        gap: 8px;
    }

    .bar {
        flex: 1;
        background: linear-gradient(180deg, var(--accent) 0%, #ff8c5a 100%);
        border-radius: 6px 6px 0 0;
        transition: all 0.3s;
    }

    .bar:hover {
        opacity: 0.85;
    }

    /* Top Products */
    .top-products {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .product-row {
        display: flex;
        align-items: center;
        padding: 12px;
        background: #334155;
        border-radius: 8px;
        gap: 12px;
    }

    .product-rank {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--accent);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        flex-shrink: 0;
    }

    .product-info {
        flex: 1;
    }

    .product-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .product-sales {
        font-size: 12px;
        color: #94a3b8;
    }

    .product-revenue {
        font-weight: 700;
        color: var(--accent);
        min-width: 80px;
        text-align: right;
    }

    /* Detailed Stats */
    .detailed-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .detail-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
    }

    .card-header {
        margin-bottom: 20px;
    }

    .card-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    /* Category List */
    .category-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .category-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .category-info {
        min-width: 120px;
    }

    .category-name {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .category-count {
        display: block;
        font-size: 11px;
        color: #94a3b8;
    }

    .category-progress {
        flex: 1;
        height: 8px;
        background: #334155;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--accent) 0%, #ff8c5a 100%);
        border-radius: 4px;
    }

    .category-revenue {
        min-width: 60px;
        text-align: right;
        font-size: 13px;
        font-weight: 700;
        color: var(--accent);
    }

    /* Revenue List */
    .revenue-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .revenue-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .day-name {
        min-width: 80px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .revenue-bar {
        flex: 1;
        height: 24px;
        background: #334155;
        border-radius: 4px;
        overflow: hidden;
    }

    .bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--accent) 0%, #ff8c5a 100%);
    }

    .day-revenue {
        min-width: 70px;
        text-align: right;
        font-size: 13px;
        font-weight: 700;
        color: var(--accent);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .charts-section {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .chart-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .detailed-stats {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .stat-value {
            font-size: 24px;
        }

        .bars {
            gap: 4px;
        }
    }

    /*  */
    .stat-change.negative {
        color: #ef4444;
    }

    .chart-labels {
        display: flex;
        justify-content: space-around;
        font-size: 11px;
        color: #94a3b8;
        margin-top: 8px;
    }

    .chart-labels span {
        flex: 1;
        text-align: center;
    }
</style>

</body>

</html>