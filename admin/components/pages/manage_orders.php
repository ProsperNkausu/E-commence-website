<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/db.php';

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$currentPageName = 'manage_orders';

// ================= FILTERS =================
$statusFilter = $_GET['status'] ?? 'all';
$dateFilter   = $_GET['date'] ?? 'all';
$sortBy       = $_GET['sort'] ?? 'newest';

// ================= PAGINATION =================
$limit = 10;
$currentPage = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($currentPage - 1) * $limit;

// ================= FILTER FUNCTION =================
function applyFilters(&$sql, &$params, $status, $date)
{
    if ($status !== 'all') {
        $sql .= " AND o.status = ?";
        $params[] = $status;
    }

    if ($date === 'today') {
        $sql .= " AND DATE(o.created_at) = CURDATE()";
    } elseif ($date === 'week') {
        $sql .= " AND YEARWEEK(o.created_at,1)=YEARWEEK(CURDATE(),1)";
    }
}

// ================= COUNT =================
$countSql = "SELECT COUNT(*) FROM orders o WHERE 1=1";
$countParams = [];

applyFilters($countSql, $countParams, $statusFilter, $dateFilter);

$stmt = $pdo->prepare($countSql);
$stmt->execute($countParams);

$totalOrders = $stmt->fetchColumn();
$totalPages = max(1, ceil($totalOrders / $limit));

// ================= MAIN QUERY =================
$sql = "SELECT o.*, c.first_name, c.last_name
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE 1=1";

$params = [];
applyFilters($sql, $params, $statusFilter, $dateFilter);

// SORT
if ($sortBy === 'oldest') {
    $sql .= " ORDER BY o.created_at ASC";
} else {
    $sql .= " ORDER BY o.created_at DESC";
}

$sql .= " LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include_once __DIR__ . '/../../includes/header.php'; ?>

<html>


<body>



    <div class="admin-container">

        <!-- Toast Notifications -->
        <div id="toastContainer"></div>

        <!-- FILTERS -->
        <div class="controls-section">
            <select id="statusFilter">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
            </select>

            <select id="dateFilter">
                <option value="all">All Dates</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
            </select>

            <select id="sortBy">
                <option value="newest">Newest</option>
                <option value="oldest">Oldest</option>
                <option value="amount-high">Amount High → Low</option>
                <option value="amount-low">Amount Low → High</option>
            </select>

            <button onclick="loadOrders(1)">Apply</button>
        </div>

        <!-- ORDERS TABLE -->
        <table class="orders-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="ordersBody"></tbody>
        </table>

        <!-- PAGINATION -->
        <div id="pagination"></div>

    </div>

    <!-- MODAL -->
    <div id="orderModal">
        <div id="modalContent">
            <header>
                <h3 id="modalTitle"></h3>
                <button id="closeModalBtn">&times;</button>
            </header>
            <section id="modalCustomer"></section>
            <section id="modalItems">
                <ul></ul>
            </section>
            <footer>
                <button id="closeModalFooter">Close</button>
            </footer>
        </div>
    </div>

    <script>
        let currentPage = 1;

        // ====================
        // Load Orders (Live AJAX)
        // ====================
        function loadOrders(page = 1) {
            currentPage = page;

            const status = document.getElementById('statusFilter').value;
            const date = document.getElementById('dateFilter').value;
            const sort = document.getElementById('sortBy').value;

            fetch(`/tematech-innovation/admin/ajax/filter-orders.php?status=${status}&date=${date}&sort=${sort}&p=${page}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return;

                    renderTable(data.orders);
                    renderPagination(data.totalPages, data.currentPage);
                });
        }

        // ====================
        // Render Table
        // ====================
        function renderTable(orders) {
            const tbody = document.getElementById('ordersBody');
            if (!orders.length) {
                tbody.innerHTML = `<tr><td colspan="5">No orders found</td></tr>`;
                return;
            }

            tbody.innerHTML = orders.map(o => `
        <tr>
            <td>#${o.order_number}</td>
            <td>${o.first_name} ${o.last_name}</td>
            <td>K${parseFloat(o.total_amount).toFixed(2)}</td>
            <td>${o.status}</td>
            <td>
                <button onclick="viewOrder('${o.id}')">View</button>
                <button onclick="shipOrder('${o.id}')">Ship</button>
            </td>
        </tr>
    `).join('');
        }

        // ====================
        // Render Pagination
        // ====================
        function renderPagination(totalPages, currentPage) {
            const container = document.getElementById('pagination');
            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '';
            for (let i = 1; i <= totalPages; i++) {
                html += `<button class="${i===currentPage?'active':''}" onclick="loadOrders(${i})">${i}</button>`;
            }
            container.innerHTML = html;
        }

        // ====================
        // View Order Modal
        // ====================
        function viewOrder(id) {
            fetch(`/tematech-innovation/admin/ajax/get-order-details.php?order_id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return alert(data.message);

                    // Header
                    document.getElementById('modalTitle').textContent = `Order #${data.order.order_number}`;

                    // Customer
                    document.getElementById('modalCustomer').innerHTML =
                        `<strong>Customer:</strong> ${data.order.first_name} ${data.order.last_name}`;

                    // Items
                    const ul = document.querySelector('#modalItems ul');
                    ul.innerHTML = '';
                    data.items.forEach(i => {
                        const li = document.createElement('li');
                        li.textContent = `${i.name} (${i.quantity}) - K${parseFloat(i.price).toFixed(2)}`;
                        ul.appendChild(li);
                    });

                    document.getElementById('orderModal').classList.add('active');
                });
        }

        // Close modal
        function closeModal() {
            document.getElementById('orderModal').classList.remove('active');
        }

        document.getElementById('closeModalBtn').addEventListener('click', closeModal);
        document.getElementById('closeModalFooter').addEventListener('click', closeModal);

        // ====================
        // Ship Order (Live)
        // ====================
        function shipOrder(id) {
            if (!confirm('Ship order?')) return;

            fetch('/tematech-innovation/admin/ajax/update-order-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        order_id: id,
                        status: 'shipped'
                    })
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        showToast('Order shipped successfully!', 'success');
                        loadOrders(currentPage); // reload table live
                    } else {
                        showToast(d.message || 'Something went wrong', 'error');
                    }
                })
                .catch(err => showToast('Network error', 'error'));
        }

        // showToast('This is a success message', 'success');

        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');

            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;

            container.appendChild(toast);

            // Remove toast after 4s
            setTimeout(() => {
                toast.remove();
            }, 10000);
        }

        // ====================
        // Initial Load
        // ====================
        document.addEventListener('DOMContentLoaded', () => loadOrders());
    </script>
    <style>
        /* ================================================
    ORDERS / MANAGE ORDERS PAGE - DARK MODE
    ================================================ */

        /* Base */
        body {
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        /* Controls / Filters Section */
        .controls-section {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .controls-section select,
        .controls-section button {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 14px;
        }

        .controls-section select {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            cursor: pointer;
        }

        .controls-section select:focus {
            border-color: var(--accent);
            outline: none;
        }

        .controls-section button {
            background: var(--accent);
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .controls-section button:hover {
            background: #e55a28;
        }

        /* Toast Notifications */
        #toastContainer {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            min-width: 200px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            font-weight: 600;
            opacity: 0;
            transform: translateX(100%);
            animation: slideIn 0.3s forwards, fadeOut 0.3s 3.5s forwards;
        }

        .toast.success {
            background: var(--success);
        }

        .toast.error {
            background: var(--danger);
        }

        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }

        /* Orders Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--bg-card);
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .orders-table thead {
            background: #334155;
        }

        .orders-table th {
            padding: 14px;
            font-size: 12px;
            text-transform: uppercase;
            color: #94a3b8;
            text-align: left;
        }

        .orders-table td {
            padding: 14px;
            font-size: 14px;
            border-top: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .orders-table tr:hover {
            background: #334155;
        }

        /* Status Badges */
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.pending {
            background: #f59e0b30;
            color: var(--warning);
        }

        .status-badge.processing {
            background: #3b82f630;
            color: #60a5fa;
        }

        .status-badge.shipped {
            background: #1e40af30;
            color: #93c5fd;
        }

        .status-badge.delivered {
            background: #10b98130;
            color: var(--success);
        }

        /* Action Buttons */
        .orders-table button {
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 6px;
            transition: all 0.2s;
        }

        .orders-table button:first-child {
            background: #1e40af30;
            color: #93c5fd;
        }

        .orders-table button:last-child {
            background: #10b98130;
            color: var(--success);
        }

        .orders-table button:hover {
            opacity: 0.85;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 6px;
        }

        .pagination a {
            padding: 8px 14px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 13px;
            transition: all 0.2s;
        }

        .pagination a:hover {
            background: #334155;
            color: var(--text-primary);
        }

        .pagination a.active {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }

        /* Modal */
        #orderModal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        #orderModal.active {
            display: flex;
        }

        #modalContent {
            background: var(--bg-card);
            width: 450px;
            max-width: 90%;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            overflow: hidden;
        }

        #modalContent header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            background: #334155;
            border-bottom: 1px solid var(--border-color);
        }

        #modalContent header h3 {
            margin: 0;
            font-size: 18px;
            color: var(--text-primary);
        }

        #closeModalBtn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #94a3b8;
        }

        #closeModalBtn:hover {
            color: var(--danger);
        }

        #modalCustomer,
        #modalItems {
            padding: 15px 25px;
            color: var(--text-secondary);
        }

        #modalItems ul {
            padding-left: 20px;
        }

        #modalItems li {
            margin-bottom: 8px;
        }

        #modalContent footer {
            padding: 15px 25px;
            background: #334155;
            border-top: 1px solid var(--border-color);
            text-align: right;
        }

        #closeModalFooter {
            padding: 8px 18px;
            background: var(--accent);
            border: none;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        #closeModalFooter:hover {
            background: #e55a28;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .controls-section {
                flex-direction: column;
            }

            .orders-table th,
            .orders-table td {
                padding: 10px;
                font-size: 12px;
            }
        }

        @media (max-width: 500px) {
            #modalContent {
                width: 95%;
            }
        }
    </style>
</body>

</html>