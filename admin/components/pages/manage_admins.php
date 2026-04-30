<?php
// ================================
// AUTH CHECK - Only Super Admin
// ================================
if (session_status() === PHP_SESSION_NONE) session_start();

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

require_once __DIR__ . '/../../../config/db.php';

// Fetch all admins
try {
    $stmt = $pdo->prepare("
        SELECT 
            a.id, a.first_name, a.last_name, a.email, a.phone, a.is_active, 
            a.last_login_at, a.created_at,
            r.id AS role_id, r.name AS role_name, r.description
        FROM admins a
        JOIN admin_roles r ON a.role_id = r.id
        ORDER BY a.created_at DESC
    ");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $admins = [];
}

// Fetch roles for dropdowns
$roles = $pdo->query("SELECT id, name, description FROM admin_roles ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/../../../admin/includes/header.php'; ?>

<div class="admin-container">

    <div class="controls-section">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search by name or email..." onkeyup="applyFilters()">
        </div>

        <div class="filters-group">
            <select id="statusFilter" onchange="applyFilters()">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <button class="btn-add" onclick="showAddAdminModal()">
                <i class="fas fa-plus"></i> Add New Admin
            </button>
        </div>
    </div>

    <!-- Admins Table -->
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></td>
                        <td><?= htmlspecialchars($admin['email']) ?></td>
                        <td><span class="role-badge"><?= htmlspecialchars(ucfirst($admin['role_name'])) ?></span></td>
                        <td><?= htmlspecialchars($admin['phone'] ?: '—') ?></td>
                        <td>
                            <span class="status-badge <?= $admin['is_active'] ? 'active' : 'inactive' ?>">
                                <?= $admin['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td><?= $admin['last_login_at'] ? date('M d, Y', strtotime($admin['last_login_at'])) : 'Never' ?></td>
                        <td>
                            <button class="btn-action" onclick="showEditAdminModal('<?= htmlspecialchars($admin['id']) ?>')" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Role Permissions Section -->
    <div class="permissions-section">
        <h3>Admin Roles & Permissions</h3>
        <div class="permissions-grid">
            <?php foreach ($roles as $role): ?>
                <div class="permission-card">
                    <div class="permission-header">
                        <i class="fas fa-user-shield"></i>
                        <h4><?= htmlspecialchars(ucfirst($role['name'])) ?></h4>
                    </div>
                    <p class="role-desc"><?= htmlspecialchars($role['description']) ?></p>
                    <ul class="permission-list">
                        <?php if ($role['name'] === 'admin'): ?>
                            <li><i class="fas fa-check"></i> Full system access</li>
                            <li><i class="fas fa-check"></i> Manage all admins & staff</li>
                            <li><i class="fas fa-check"></i> System configuration</li>
                            <li><i class="fas fa-check"></i> View all reports</li>
                        <?php else: ?>
                            <li><i class="fas fa-check"></i> Manage products</li>
                            <li><i class="fas fa-check"></i> Manage orders</li>
                            <li><i class="fas fa-check"></i> View customer support</li>
                            <li><i class="fas fa-times"></i> Manage other admins</li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ====================== ADD ADMIN MODAL ====================== -->
<div id="addAdminModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="hideAddAdminModal()">×</span>
        <h3>Add New Admin</h3>

        <form id="addAdminForm" onsubmit="submitAddAdmin(event)">
            <div class="form-grid">
                <div class="form-group">
                    <label>First Name <span class="required">*</span></label>
                    <input type="text" id="add_first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label>Last Name <span class="required">*</span></label>
                    <input type="text" id="add_last_name" name="last_name" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email Address <span class="required">*</span></label>
                <input type="email" id="add_email" name="email" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" id="add_phone" name="phone">
            </div>

            <div class="form-group">
                <label>Role <span class="required">*</span></label>
                <select id="add_role_id" name="role_id" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>"><?= ucfirst($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Password <span class="required">*</span></label>
                <input type="password" id="add_password" name="password" required>
            </div>

            <div class="form-group">
                <label>Account Status</label>
                <select id="add_is_active" name="is_active">
                    <option value="1" selected>Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="hideAddAdminModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Admin</button>
            </div>
        </form>
    </div>
</div>

<!-- ====================== EDIT ADMIN MODAL ====================== -->
<div id="editAdminModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="hideEditAdminModal()">×</span>
        <h3>Edit Admin Account</h3>

        <form id="editAdminForm" onsubmit="submitEditAdmin(event)">
            <input type="hidden" id="edit_admin_id" name="admin_id">

            <div class="form-grid">
                <div class="form-group">
                    <label>First Name <span class="required">*</span></label>
                    <input type="text" id="edit_first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label>Last Name <span class="required">*</span></label>
                    <input type="text" id="edit_last_name" name="last_name" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email Address <span class="required">*</span></label>
                <input type="email" id="edit_email" name="email" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" id="edit_phone" name="phone">
            </div>

            <div class="form-group">
                <label>Role <span class="required">*</span></label>
                <select id="edit_role_id" name="role_id" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>"><?= ucfirst($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Account Status</label>
                <select id="edit_is_active" name="is_active">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="hideEditAdminModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* ================================================
   ADMINS MANAGEMENT PAGE - DARK MODE
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

    .btn-add {
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

    .btn-add:hover {
        background: #e55a28;
    }

    /* Table Styling */
    .table-wrapper {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 32px;
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

    /* Role Badges */
    .role-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        background: #1e40af30;
        color: #93c5fd;
    }

    /* Status Badge */
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-badge.active {
        background: #10b98130;
        color: var(--success);
    }

    .status-badge.inactive {
        background: #475569;
        color: #94a3b8;
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

    /* Permissions Section */
    .permissions-section {
        margin-top: 40px;
    }

    .permissions-section h3 {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 20px;
    }

    .permissions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .permission-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s;
    }

    .permission-card:hover {
        border-color: var(--accent);
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.15);
    }

    .permission-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .permission-header i {
        font-size: 28px;
        color: var(--accent);
    }

    .permission-header h4 {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .permission-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .permission-list li {
        font-size: 13px;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .permission-list i.fa-check {
        color: var(--success);
    }

    .permission-list i.fa-times {
        color: var(--danger);
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 2000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.75);
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: var(--bg-card);
        padding: 30px 35px;
        border-radius: 16px;
        width: 100%;
        max-width: 480px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
    }

    .close-modal {
        float: right;
        font-size: 28px;
        cursor: pointer;
        color: #94a3b8;
    }

    .close-modal:hover {
        color: var(--danger);
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: var(--text-secondary);
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 12px 14px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 15px;
        color: var(--text-primary);
    }

    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 25px;
    }

    .btn-secondary {
        background: #475569;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }

    .btn-secondary:hover {
        background: #334155;
    }

    .btn-primary {
        background: var(--accent);
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }

    .btn-primary:hover {
        background: #e55a28;
    }

    .required {
        color: var(--danger);
    }

    .role-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        background: #1e40af30;
        color: #93c5fd;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-badge.active {
        background: #10b98130;
        color: var(--success);
    }

    .status-badge.inactive {
        background: #475569;
        color: #94a3b8;
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

        .permissions-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .filters-group {
            width: 100%;
            flex-direction: column;
        }

        .filters-group select {
            width: 100%;
        }

        .permissions-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .table-wrapper {
            border-radius: 0;
            border-left: none;
            border-right: none;
        }
    }
</style>


<script>
    // ==================== ADD ADMIN FUNCTIONS ====================
    function showAddAdminModal() {
        document.getElementById('addAdminForm').reset();
        document.getElementById('addAdminModal').style.display = 'flex';
    }

    function hideAddAdminModal() {
        document.getElementById('addAdminModal').style.display = 'none';
    }

    async function submitAddAdmin(e) {
        e.preventDefault();
        const formData = new FormData(document.getElementById('addAdminForm'));

        try {
            // Correct path: go up one level from /components/pages/ to /admin/
            const response = await fetch('/tematech-innovation/admin/components/pages/add-admin-process.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message || 'Admin created successfully!');
                hideAddAdminModal();
                location.reload();
            } else {
                alert(result.message || 'Failed to create admin');
            }
        } catch (error) {
            console.error("Add Admin Error:", error);
            alert('Network error. Please check console (F12).');
        }
    }

    // ==================== EDIT ADMIN FUNCTIONS ====================
    function showEditAdminModal(adminId) {
        console.log("Edit clicked for ID:", adminId);

        if (!adminId || adminId.length !== 36) {
            alert("Invalid admin ID");
            return;
        }

        // Correct relative path from /components/pages/
        fetch('/tematech-innovation/admin/components/pages/get-admin.php?id=' + encodeURIComponent(adminId))
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log("Received data:", data);

                if (data.success && data.admin) {
                    document.getElementById('edit_admin_id').value = data.admin.id;
                    document.getElementById('edit_first_name').value = data.admin.first_name || '';
                    document.getElementById('edit_last_name').value = data.admin.last_name || '';
                    document.getElementById('edit_email').value = data.admin.email || '';
                    document.getElementById('edit_phone').value = data.admin.phone || '';
                    document.getElementById('edit_role_id').value = data.admin.role_id || '';
                    document.getElementById('edit_is_active').value = data.admin.is_active ? '1' : '0';

                    document.getElementById('editAdminModal').style.display = 'flex';
                } else {
                    alert(data.message || "Failed to load admin data");
                }
            })
            .catch(error => {
                console.error("Fetch error:", error);
                alert("Error loading admin data.\nCheck console (F12) for details.");
            });
    }

    function hideEditAdminModal() {
        document.getElementById('editAdminModal').style.display = 'none';
    }

    async function submitEditAdmin(e) {
        e.preventDefault();
        const formData = new FormData(document.getElementById('editAdminForm'));

        try {
            // Correct relative path
            const response = await fetch('/tematech-innovation/admin/components/pages/edit-admin-process.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message || 'Admin updated successfully!');
                hideEditAdminModal();
                location.reload();
            } else {
                alert(result.message || 'Failed to update admin');
            }
        } catch (error) {
            console.error("Edit Admin Error:", error);
            alert('Network error. Please check console (F12).');
        }
    }

    // ==================== FILTERS ====================
    function applyFilters() {
        const search = document.getElementById('searchInput').value.trim();
        const status = document.getElementById('statusFilter').value;

        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (status !== 'all') params.append('status', status);

        window.location.href = '../index.php?page=admins' + (params.toString() ? '&' + params.toString() : '');
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const addModal = document.getElementById('addAdminModal');
        const editModal = document.getElementById('editAdminModal');
        if (event.target === addModal) hideAddAdminModal();
        if (event.target === editModal) hideEditAdminModal();
    };
</script>