<?php
// ================================
// ERROR REPORTING (Development)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ================================
// SESSION + DATABASE
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

// ================================
// UPDATED AUTH CHECK (Critical Fix)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'customer') {
    header("Location: ../index.php?page=login");
    exit;
}

// Use the correct session key
$customerId = $_SESSION['user_id'];
$userName   = trim(($_SESSION['customer_first_name'] ?? '') . ' ' . ($_SESSION['customer_last_name'] ?? ''));

// ================================
// FETCH FRESH USER INFO
$userEmail = '';
try {
    $stmt = $pdo->prepare("SELECT email, first_name, last_name FROM customers WHERE id = ?");
    $stmt->execute([$customerId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        $userEmail = $userData['email'] ?? '';
        if (!empty($userData['first_name']) && !empty($userData['last_name'])) {
            $userName = trim($userData['first_name'] . ' ' . $userData['last_name']);
        }
    }
} catch (Exception $e) {
    error_log("User info error: " . $e->getMessage());
}

// ================================
// DASHBOARD STATISTICS
$totalOrders = 0;
$totalSpent  = 0.00;
$pendingOrders = 0;
$shippedOrders = 0;
$deliveredOrders = 0;

try {
    $statsStmt = $pdo->prepare("
        SELECT 
            COUNT(*) AS total_orders,
            COALESCE(SUM(total_amount), 0) AS total_spent,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_orders,
            SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) AS shipped_orders,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered_orders
        FROM orders 
        WHERE customer_id = ?
    ");
    $statsStmt->execute([$customerId]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    if ($stats) {
        $totalOrders     = (int)$stats['total_orders'];
        $totalSpent      = (float)$stats['total_spent'];
        $pendingOrders   = (int)$stats['pending_orders'];
        $shippedOrders   = (int)$stats['shipped_orders'];
        $deliveredOrders = (int)$stats['delivered_orders'];
    }
} catch (Exception $e) {
    error_log("Stats error: " . $e->getMessage());
}

// ================================
// RECENT ORDERS
$orders = [];
try {
    $ordersStmt = $pdo->prepare("
        SELECT 
            o.id AS order_id,
            o.total_amount,
            o.status,
            o.created_at,
            GROUP_CONCAT(p.name SEPARATOR ', ') AS items
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.customer_id = ?
        GROUP BY o.id, o.total_amount, o.status, o.created_at
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $ordersStmt->execute([$customerId]);
    $orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Orders error: " . $e->getMessage());
}

// ================================
// ACTIVE CART COUNT
$cartCount = 0;
try {
    $cartStmt = $pdo->prepare("
        SELECT COALESCE(SUM(ci.quantity), 0)
        FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.id
        WHERE c.customer_id = ? AND c.status = 'active'
    ");
    $cartStmt->execute([$customerId]);
    $cartCount = (int)$cartStmt->fetchColumn();
} catch (Exception $e) {
    error_log("Cart error: " . $e->getMessage());
}
?>

<?php include __DIR__ . '/../../../users/includes/header.php'; ?>
<?php include __DIR__ . '/../../../includes/page-loader.php'; ?>

<div class="dashboard-container">

    <!-- HEADER -->
    <div style="margin-bottom: 25px;">
        <h1>Welcome back, <?= htmlspecialchars($userName) ?></h1>
        <p>Here's what's happening with your account today.</p>
    </div>

    <div class="main-layout">

        <!-- MAIN CONTENT -->
        <div class="grid" style="gap: 25px;">

            <!-- STATS -->
            <div class="grid grid-5">
                <div class="card stat-card">
                    <div>
                        <div class="stat-title">Total Orders</div>
                        <div class="stat-value"><?= $totalOrders ?></div>
                    </div>
                    <div class="icon-box blue"><?= icon('orders') ?></div>
                </div>

                <div class="card stat-card">
                    <div>
                        <div class="stat-title">Total Spent</div>
                        <div class="stat-value">K<?= number_format($totalSpent, 2) ?></div>
                    </div>
                    <div class="icon-box green"><?= icon('money') ?></div>
                </div>

                <div class="card stat-card">
                    <div>
                        <div class="stat-title">Pending</div>
                        <div class="stat-value"><?= $pendingOrders ?></div>
                    </div>
                    <div class="icon-box amber"><?= icon('pending') ?></div>
                </div>

                <div class="card stat-card">
                    <div>
                        <div class="stat-title">Shipped</div>
                        <div class="stat-value"><?= $shippedOrders ?></div>
                    </div>
                    <div class="icon-box blue"><?= icon('shipped') ?></div>
                </div>
            </div>

            <!-- RECENT ORDERS -->
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h2>Recent Orders</h2>
                    <a href="index.php?page=orders" class="btn btn-outline">View all</a>
                </div>

                <div style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>

                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($orders)): ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>

                                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                        <td title="<?= htmlspecialchars($order['items'] ?? '') ?>">
                                            <?= htmlspecialchars(mb_strimwidth($order['items'] ?? 'No items', 0, 40, '...')) ?>
                                        </td>
                                        <td><strong>K<?= number_format($order['total_amount'], 2) ?></strong></td>
                                        <td>
                                            <span class="badge 
                                                <?= strtolower($order['status']) === 'delivered' || strtolower($order['status']) === 'paid' ? 'badge-success' : (strtolower($order['status']) === 'shipped' ? 'badge-info' : 'badge-warning') ?>">
                                                <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:40px;">
                                        No orders yet.<br><br>
                                        <a href="index.php?page=products" class="btn btn-dark">Start Shopping</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SIDEBAR -->
        <div class="grid" style="gap: 20px;">

            <!-- PROFILE CARD -->
            <div class="card profile-card">
                <div class="avatar">
                    <?= strtoupper(substr($userName, 0, 1)) ?>
                </div>
                <h2><?= htmlspecialchars($userName) ?></h2>
                <p><?= htmlspecialchars($userEmail) ?></p>

                <div style="margin-top: 25px;">
                    <a href="index.php?page=profile" class="btn btn-dark" style="width:100%; margin-bottom:10px;">
                        View Profile
                    </a>
                    <a href="index.php?page=orders" class="btn btn-outline" style="width:100%;">
                        My Orders
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
// Icon function (kept from your code)
function icon($name)
{
    $icons = [
        'orders' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 13V7a2 2 0 00-2-2h-3M4 7v10a2 2 0 002 2h12a2 2 0 002-2v-4M16 3v4M8 3v4M4 11h16"/></svg>',
        'money' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-width="1.8" d="M12 8c-2 0-3 1-3 2s1 2 3 2 3 1 3 2-1 2-3 2m0-10v12"/></svg>',
        'pending' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="1.8"/><path stroke-width="1.8" d="M12 7v5l3 3"/></svg>',
        'shipped' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-width="1.8" d="M3 7h13v10H3z"/><path stroke-width="1.8" d="M16 10h3l2 3v4h-5z"/></svg>',
    ];
    return $icons[$name] ?? '';
}
?>

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
        --info: #60a5fa;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: "Inter", Arial, sans-serif;
        background: var(--bg-primary);
        color: var(--text-primary);
        line-height: 1.6;
    }

    .grid {
        display: grid;
        gap: 20px;
    }

    .grid-5 {
        grid-template-columns: repeat(5, 1fr);
    }

    .main-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
    }

    .card {
        background: var(--bg-card);
        border-radius: 14px;
        padding: 20px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .stat-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .stat-title {
        font-size: 13px;
        color: #94a3b8;
    }

    .stat-value {
        font-size: 22px;
        font-weight: 600;
        margin-top: 5px;
        color: var(--text-primary);
    }

    .icon-box {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Dark mode icon colors */
    .blue {
        background: rgba(37, 99, 235, 0.15);
        color: #60a5fa;
    }

    .green {
        background: rgba(52, 211, 153, 0.15);
        color: var(--success);
    }

    .amber {
        background: rgba(245, 158, 11, 0.15);
        color: var(--warning);
    }

    /* Table Styles */
    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        text-align: left;
        font-size: 12px;
        color: #94a3b8;
        padding: 12px;
        background: #334155;
        border-bottom: 2px solid var(--border-color);
    }

    .table td {
        padding: 14px 12px;
        border-top: 1px solid var(--border-color);
        color: var(--text-secondary);
    }

    .table tr:hover {
        background: #334155;
    }

    /* Badges */
    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
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

    /* Buttons */
    .btn {
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 14px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn-dark {
        background: #111827;
        color: #fff;
        border: none;
    }

    .btn-dark:hover {
        background: #000;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid var(--border-color);
        color: var(--text-primary);
    }

    .btn-outline:hover {
        background: var(--bg-secondary);
        border-color: var(--accent);
        color: var(--accent);
    }

    /* Profile Card */
    .profile-card {
        text-align: center;
    }

    .avatar {
        width: 70px;
        height: 70px;
        border-radius: 14px;
        background: linear-gradient(135deg, #FF6B35, #FF8C42);
        color: white;
        font-size: 26px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }

    /* Responsive */
    @media (max-width: 900px) {
        .main-layout {
            grid-template-columns: 1fr;
        }

        .grid-5 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* Extra improvements for better dark mode feel */
    .card:hover {
        border-color: #475569;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
    }

    .table th {
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>