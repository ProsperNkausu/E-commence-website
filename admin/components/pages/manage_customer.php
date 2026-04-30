<?php

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

require_once __DIR__ . '/../../../config/db.php'; // PDO connection
$query = "
    SELECT 
        c.id,
        CONCAT(c.first_name, ' ', c.last_name) AS name,
        c.email,
        c.phone,
        COUNT(o.id) AS total_orders,
        COALESCE(SUM(o.total_amount), 0) AS total_spent,
        c.created_at,
        c.status
    FROM customers c
    LEFT JOIN orders o ON o.customer_id = c.id
    GROUP BY c.id, c.first_name, c.last_name, c.email, c.phone, c.created_at, c.status
    ORDER BY c.created_at DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<?php include __DIR__ . '/../../../admin/includes/header.php'; ?>

<div class="admin-container">
    <!-- Controls Section -->
    <div class="controls-section">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search by name, email, or ID..." onkeyup="applyFilters()">
        </div>

        <div class="filters-group">
            <select id="statusFilter" onchange="applyFilters()">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
            </select>

            <select id="joinDateFilter" onchange="applyFilters()">
                <option value="all">All Time</option>
                <option value="last30">Last 30 Days</option>
                <option value="last90">Last 90 Days</option>
                <option value="last-year">Last Year</option>
            </select>

            <button class="btn-search" onclick="applyFilters()">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Total Orders</th>
                    <th>Total Spent</th>


                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $cust): ?>
                    <tr>
                        <td><?= htmlspecialchars($cust['name']) ?></td>
                        <td><?= htmlspecialchars($cust['email']) ?></td>
                        <td><?= htmlspecialchars($cust['phone']) ?></td>
                        <td><span class="badge-count"><?= $cust['total_orders'] ?></span></td>
                        <td><span class="price">K<?= number_format($cust['total_spent'], 2) ?></span></td>


                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    /* ================================================
   PAYMENTS / MANAGE PAYMENTS PAGE - DARK MODE
   ================================================ */

    /* Container */
    .admin-container {
        max-width: 1200px;
    }

    /* Controls Section */
    .controls-section {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
        align-items: center;
        flex-wrap: wrap;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
    }

    .search-bar {
        flex: 1;
        min-width: 250px;
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-bar i {
        position: absolute;
        left: 14px;
        color: #94a3b8;
        font-size: 14px;
    }

    .search-bar input {
        width: 100%;
        padding: 10px 14px 10px 36px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        color: var(--text-primary);
        transition: all 0.2s;
    }

    .search-bar input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.2);
    }

    .filters-group {
        display: flex;
        gap: 12px;
    }

    .filters-group select {
        padding: 10px 14px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        color: var(--text-primary);
        cursor: pointer;
        transition: all 0.2s;
    }

    .filters-group select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.2);
    }

    .btn-search {
        padding: 10px 16px;
        background: var(--accent);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-search:hover {
        background: #e55a28;
    }

    /* Table Wrapper & Table */
    .table-wrapper {
        width: 100%;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
    }

    .admin-table thead {
        background: #334155;
    }

    .admin-table th {
        padding: 12px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: #e2e8f0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--border-color);
    }

    .admin-table td {
        padding: 16px;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
        color: var(--text-primary);
    }

    .admin-table tbody tr:hover {
        background: #334155;
    }

    .admin-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge.active,
    .badge.successful {
        background: #10b98130;
        color: var(--success);
    }

    .badge.inactive,
    .badge.pending {
        background: #f59e0b30;
        color: var(--warning);
    }

    .badge.suspended,
    .badge.failed {
        background: #ef444430;
        color: var(--danger);
    }

    /* Price */
    .price {
        color: var(--accent);
        font-weight: 700;
    }

    /* Actions */
    .btn-action {
        padding: 6px 8px;
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        cursor: pointer;
        color: #60a5fa;
        font-size: 13px;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin: 0 2px;
    }

    .btn-action:hover {
        background: #1e40af30;
        border-color: #60a5fa;
    }

    .btn-action.delete {
        color: var(--danger);
    }

    .btn-action.delete:hover {
        background: #7f1d1d30;
        border-color: var(--danger);
    }

    /* Badge Count */
    .badge-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        background: #f59e0b30;
        color: var(--warning);
        border-radius: 50%;
        font-size: 12px;
        font-weight: 700;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .controls-section {
            flex-direction: column;
            align-items: stretch;
        }

        .search-bar {
            min-width: auto;
        }

        .filters-group {
            flex-wrap: wrap;
        }

        .admin-table th,
        .admin-table td {
            padding: 12px;
            font-size: 12px;
        }
    }

    @media (max-width: 768px) {
        .admin-table {
            font-size: 12px;
        }

        .admin-table th,
        .admin-table td {
            padding: 8px;
        }

        .btn-action {
            padding: 4px 6px;
            font-size: 11px;
        }

        .filters-group {
            width: 100%;
            flex-direction: column;
        }

        .filters-group select {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .table-wrapper {
            border-radius: 0;
            border-left: none;
            border-right: none;
        }

        .admin-table th {
            font-size: 11px;
        }

        .admin-table td {
            padding: 8px;
        }

        .btn-action {
            padding: 4px;
            margin: 0 1px;
        }
    }
</style>

<script>
    function applyFilters() {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;
        const joinDate = document.getElementById('joinDateFilter').value;

        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (status !== 'all') params.append('status', status);
        if (joinDate !== 'all') params.append('joinDate', joinDate);

        const queryString = params.toString();
        const url = '../index.php?page=customers' + (queryString ? '&' + queryString : '');
        window.location.href = url;
    }

    function viewCustomer(customerId) {
        alert('View customer details: ' + customerId);
        // TODO: Open customer detail modal/page
    }

    function editCustomer(customerId) {
        alert('Edit customer: ' + customerId);
        // TODO: Open customer edit form
    }

    function suspendCustomer(customerId) {
        if (confirm('Are you sure you want to suspend this customer?')) {
            alert('Customer suspended: ' + customerId);
            // TODO: Suspend customer via API
        }
    }
</script>

</body>

</html>