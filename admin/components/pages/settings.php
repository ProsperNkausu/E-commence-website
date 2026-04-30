<?php
include __DIR__ . '/../../../admin/includes/header.php';

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

// Fetch Customers
$stmt = $pdo->prepare("SELECT id, first_name, last_name, email, phone, status, created_at 
                       FROM customers 
                       ORDER BY created_at DESC");
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Admins & Staff
$stmt = $pdo->prepare("
    SELECT a.id, a.first_name, a.last_name, a.email, a.phone, a.is_active, 
           a.last_login_at, a.created_at, r.name AS role 
    FROM admins a 
    JOIN admin_roles r ON a.role_id = r.id 
    ORDER BY a.created_at DESC
");
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="admin-container">
    <div class="page-header">
        <h1><i class="fas fa-users-cog"></i> User & Account Management</h1>
        <p>Manage customers and administrator accounts • Reset passwords • Delete users</p>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab-btn active" onclick="switchUserTab(event, 'customers-tab')">
            Customers (<?= count($customers) ?>)
        </button>
        <button class="tab-btn" onclick="switchUserTab(event, 'admins-tab')">
            Admins & Staff (<?= count($admins) ?>)
        </button>
    </div>

    <!-- Customers Tab -->
    <div id="customers-tab" class="user-tab active">
        <div class="table-container">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $c): ?>
                        <tr data-id="<?= htmlspecialchars($c['id']) ?>" data-type="customer">
                            <td><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></td>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                            <td>
                                <span class="status-badge <?= strtolower($c['status']) === 'active' ? 'active' : 'inactive' ?>">
                                    <?= ucfirst($c['status']) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                            <td class="actions">
                                <button onclick="resetUserPassword(this)" class="btn-action btn-reset" title="Reset Password">
                                    <i class="fas fa-key"></i>
                                </button>
                                <button onclick="toggleUserStatus(this)" class="btn-action btn-toggle" title="Toggle Status">
                                    <i class="fas fa-toggle-on"></i>
                                </button>
                                <button onclick="deleteUser(this)" class="btn-action btn-delete" title="Delete Account">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="6" class="no-data">No customers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Admins Tab -->
    <div id="admins-tab" class="user-tab">
        <div class="table-container">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $a): ?>
                        <tr data-id="<?= htmlspecialchars($a['id']) ?>" data-type="admin">
                            <td><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></td>
                            <td><?= htmlspecialchars($a['email']) ?></td>
                            <td><span class="role-badge"><?= ucfirst($a['role']) ?></span></td>
                            <td>
                                <span class="status-badge <?= $a['is_active'] ? 'active' : 'inactive' ?>">
                                    <?= $a['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td><?= $a['last_login_at'] ? date('M d, Y H:i', strtotime($a['last_login_at'])) : 'Never' ?></td>
                            <td class="actions">
                                <button onclick="resetUserPassword(this)" class="btn-action btn-reset" title="Reset Password">
                                    <i class="fas fa-key"></i>
                                </button>
                                <button onclick="toggleUserStatus(this)" class="btn-action btn-toggle" title="Toggle Status">
                                    <i class="fas fa-toggle-on"></i>
                                </button>
                                <?php if ($a['id'] !== $_SESSION['admin_id']): ?>
                                    <button onclick="deleteUser(this)" class="btn-action btn-delete" title="Delete Account">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($admins)): ?>
                        <tr>
                            <td colspan="6" class="no-data">No admin accounts found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="resetModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-key"></i> Reset Password</h3>
        <p id="modal-user-info"></p>

        <div class="form-group">
            <label>New Password</label>
            <input type="password" id="new-password" placeholder="Minimum 6 characters" autocomplete="new-password">
        </div>

        <div class="modal-actions">
            <button onclick="closeModal()" class="btn-cancel">Cancel</button>
            <button onclick="confirmResetPassword()" class="btn-primary" id="reset-btn">
                Reset Password
            </button>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="toast"></div>

<style>
    /* Same dark mode styles as before - kept clean */
    

    .page-header h1 {
        color: #f1f5f9;
        font-size: 28px;
        margin-bottom: 6px;
    }

    .page-header p {
        color: #94a3b8;
    }

    .tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        border-bottom: 2px solid #334155;
    }

    .tab-btn {
        padding: 14px 28px;
        background: #1e2937;
        color: #cbd5e1;
        border: none;
        border-radius: 8px 8px 0 0;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .tab-btn.active {
        background: #0f172a;
        color: #f97316;
        border-bottom: 3px solid #f97316;
    }

    .user-tab {
        display: none;
    }

    .user-tab.active {
        display: block;
    }

    .table-container {
        background: #1e2937;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .user-table {
        width: 100%;
        border-collapse: collapse;
        color: #e2e8f0;
    }

    .user-table th {
        background: #0f172a;
        padding: 18px 16px;
        text-align: left;
        font-weight: 600;
        color: #94a3b8;
    }

    .user-table td {
        padding: 16px;
        border-bottom: 1px solid #334155;
    }

    .status-badge {
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
    }

    .status-badge.active {
        background: #166534;
        color: #86efac;
    }

    .status-badge.inactive {
        background: #7f1d1d;
        color: #fca5a5;
    }

    .role-badge {
        background: #1e40af;
        color: #bfdbfe;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 13px;
    }

    .actions {
        display: flex;
        gap: 10px;
    }

    .btn-action {
        width: 42px;
        height: 42px;
        border: none;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-reset {
        background: #78350f;
        color: #fcd34d;
    }

    .btn-toggle {
        background: #1e3a8a;
        color: #93c5fd;
    }

    .btn-delete {
        background: #7f1d1d;
        color: #fda4af;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 2000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.85);
    }

    .modal-content {
        background: #1e2937;
        margin: 8% auto;
        padding: 30px;
        width: 440px;
        border-radius: 12px;
        color: #e2e8f0;
    }

    .modal-content h3 {
        color: #f97316;
        margin-bottom: 8px;
    }

    .btn-cancel {
        background: #334155;
        color: #cbd5e1;
    }

    .btn-primary {
        background: #f97316;
        color: white;
    }

    .toast {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #166534;
        color: white;
        padding: 14px 24px;
        border-radius: 8px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
        display: none;
        align-items: center;
        gap: 10px;
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
    // CSRF Token
    const csrfToken = "<?= $csrf_token ?>";

    // ... rest of your JavaScript (switchUserTab, closeModal, showToast remain the same)

    // Updated Reset Password Function
    function confirmResetPassword() {
        const newPass = document.getElementById('new-password').value.trim();
        const btn = document.getElementById('reset-btn');

        if (newPass.length < 6) {
            showToast("Password must be at least 6 characters", "error");
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Resetting...`;

        const formData = new URLSearchParams();
        formData.append('type', currentType);
        formData.append('id', currentId);
        formData.append('new_password', newPass);
        formData.append('csrf_token', csrfToken);

        fetch('actions/reset_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || "Password reset successfully!", "success");
                    closeModal();
                } else {
                    showToast(data.message || "Failed to reset password", "error");
                }
            })
            .catch(() => showToast("Network error. Please try again.", "error"))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = "Reset Password";
            });
    }

    // Updated Toggle Status
    function toggleUserStatus(btn) {
        const row = btn.closest('tr');
        const type = row.dataset.type;
        const id = row.dataset.id;

        if (!confirm(`Change status of this ${type}?`)) return;

        const formData = new URLSearchParams();
        formData.append('type', type);
        formData.append('id', id);
        formData.append('csrf_token', csrfToken);

        fetch('actions/toggle_user_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    location.reload();
                } else {
                    showToast(data.message, "error");
                }
            });
    }

    // Updated Delete User
    function deleteUser(btn) {
        const row = btn.closest('tr');
        const type = row.dataset.type;
        const id = row.dataset.id;
        const name = row.cells[0].textContent;

        if (!confirm(`Permanently delete ${name}? This action cannot be undone.`)) return;

        const formData = new URLSearchParams();
        formData.append('type', type);
        formData.append('id', id);
        formData.append('csrf_token', csrfToken);

        fetch('actions/delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || `${name} has been deleted.`, "success");
                    row.remove();
                } else {
                    showToast(data.message || "Failed to delete user", "error");
                }
            });
    }
</script>