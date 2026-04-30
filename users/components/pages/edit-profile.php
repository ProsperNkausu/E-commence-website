<?php
// ================================
// DB CONNECTION + AUTH
// ================================
require_once __DIR__ . '/../../../config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// FIXED AUTH CHECK - Using correct customer session key
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'customer') {
    header("Location: ../index.php?page=login");
    exit;
}

$customerId = $_SESSION['user_id'];

// Fetch user data
$userData = [];
try {
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, phone, created_at FROM customers WHERE id = ?");
    $stmt->execute([$customerId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Profile fetch error: " . $e->getMessage());
}

$userName    = trim(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? ''));
$userEmail   = $userData['email'] ?? '';
$userPhone   = $userData['phone'] ?? '';
$memberSince = $userData['created_at'] ? date('F Y', strtotime($userData['created_at'])) : 'February 2026';
?>

<?php include __DIR__ . '/../../../users/includes/header.php'; ?>

<div class="dashboard-container">

    <div class="page-header">
        <h1>My Profile</h1>
        <p>Manage your personal information</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert <?= $success ? 'alert-success' : 'alert-error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="profile-main">

        <!-- Profile Overview -->
        <div class="card profile-card">
            <div class="avatar-large">
                <?= strtoupper(substr($userName, 0, 1)) ?>
            </div>
            <h2><?= htmlspecialchars($userName) ?></h2>
            <p class="user-email"><?= htmlspecialchars($userEmail) ?></p>

            <div class="profile-info">
                <div class="info-row">
                    <span class="label">Phone</span>
                    <span class="value"><?= htmlspecialchars($userPhone ?: 'Not provided') ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Member Since</span>
                    <span class="value"><?= htmlspecialchars($memberSince) ?></span>
                </div>
            </div>
        </div>

        <!-- Change Password Section -->
        <div class="card edit-card">
            <h3>Change Password</h3>
            <hr style="margin: 25px 0; border-color: #f3f4f6;">

            <form id="passwordForm" class="password-form">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" id="new_password"
                        onkeyup="checkPasswordStrength()" required minlength="6">

                    <!-- Password Strength Indicator -->
                    <div id="strengthContainer" class="strength-container" style="display:none;">
                        <div class="strength-bar">
                            <div id="strengthBar" class="strength-fill"></div>
                        </div>
                        <div id="strengthText" class="strength-text">Enter password</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>

                <button type="submit" class="btn btn-dark btn-full">
                    Update Password
                </button>
            </form>

            <div id="snackbar"></div>

            <div style="margin-top: 25px; text-align: center;">
                <button onclick="showForgotModal()" class="forgot-link">
                    Forgot Password?
                </button>
            </div>
        </div>

    </div>
</div>

<!-- Forgot Password Modal -->
<div id="forgotModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="hideForgotModal()">&times;</span>
        <h3>Forgot Password</h3>
        <p>Enter your registered email and we'll send you a password reset link.</p>

        <form action="../../../api/forgot-password.php" method="POST" class="forgot-form">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="forgot_email" value="<?= htmlspecialchars($userEmail) ?>" required>
            </div>
            <button type="submit" class="btn btn-dark btn-full">
                Send Reset Link
            </button>
        </form>
    </div>
</div>

<script>
    // Password Strength Indicator
    function checkPasswordStrength() {
        const password = document.getElementById('new_password').value;
        const container = document.getElementById('strengthContainer');
        const bar = document.getElementById('strengthBar');
        const text = document.getElementById('strengthText');

        if (password.length === 0) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';

        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        let width = Math.min(strength * 25, 100);
        let color = '#ef4444';
        let label = 'Very Weak';

        if (strength >= 4) {
            color = '#10b981';
            label = 'Strong';
        } else if (strength === 3) {
            color = '#eab308';
            label = 'Medium';
        } else if (strength === 2) {
            color = '#f59e0b';
            label = 'Weak';
        }

        bar.style.width = width + '%';
        bar.style.backgroundColor = color;
        text.textContent = label;
        text.style.color = color;
    }

    // Password Update Form
    const form = document.getElementById('passwordForm');
    const snackbar = document.getElementById('snackbar');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);

        try {
            const res = await fetch('/tematech-innovation/api/user-pass-update.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            showSnackbar(data.message || 'An error occurred', data.status || 'error');

            if (data.status === 'success') {
                setTimeout(() => location.reload(), 1500);
            }
        } catch (err) {
            showSnackbar('Network error. Please try again.', 'error');
        }
    });

    function showSnackbar(message, status) {
        snackbar.textContent = message;
        snackbar.style.background = (status === 'success') ? '#4caf50' : '#f44336';
        snackbar.classList.add('show');
        setTimeout(() => snackbar.classList.remove('show'), 3000);
    }

    // Forgot Password Modal
    function showForgotModal() {
        document.getElementById('forgotModal').style.display = 'flex';
    }

    function hideForgotModal() {
        document.getElementById('forgotModal').style.display = 'none';
    }

    window.onclick = function(e) {
        const modal = document.getElementById('forgotModal');
        if (e.target === modal) hideForgotModal();
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
        --danger: #f87171;
    }

    /* ====================== SNACKBAR ====================== */
    #snackbar {
        visibility: hidden;
        min-width: 250px;
        margin-left: -125px;
        background-color: #1e2937;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 16px;
        position: fixed;
        z-index: 9999;
        left: 50%;
        bottom: 30px;
        font-size: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        transition: all 0.5s;
        opacity: 0;
        border: 1px solid var(--border-color);
    }

    #snackbar.show {
        visibility: visible;
        opacity: 1;
    }

    /* ====================== PAGE LAYOUT ====================== */
    .dashboard-container {
        font-family: 'Segoe UI', system-ui, sans-serif;
        padding: 32px;
        background: var(--bg-primary);
        color: var(--text-primary);
    }

    .page-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .page-header p {
        color: #94a3b8;
        margin-top: 4px;
    }

    .profile-main {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 25px;
    }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 35px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    /* ====================== AVATAR ====================== */
    .avatar-large {
        width: 110px;
        height: 110px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, var(--accent), #ff8c5a);
        color: white;
        font-size: 42px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
    }

    /* ====================== PROFILE INFO ====================== */
    .profile-info .info-row {
        display: flex;
        justify-content: space-between;
        padding: 14px 0;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-secondary);
    }

    .profile-info .info-row:last-child {
        border-bottom: none;
    }

    /* ====================== FORMS ====================== */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text-secondary);
    }

    .form-group input {
        width: 100%;
        padding: 12px 16px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        font-size: 15px;
        color: var(--text-primary);
        transition: all 0.2s;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.2);
    }

    .btn-dark {
        background: var(--accent);
        color: white;
        padding: 13px 24px;
        border-radius: 10px;
        width: 100%;
        border: none;
        cursor: pointer;
        font-weight: 600;
        font-size: 15px;
        transition: all 0.2s;
    }

    .btn-dark:hover {
        background: #e55a28;
        transform: translateY(-1px);
    }

    /* Password Strength */
    .strength-container {
        margin: 8px 0 15px 0;
    }

    .strength-bar {
        height: 6px;
        background: var(--border-color);
        border-radius: 999px;
        overflow: hidden;
    }

    .strength-fill {
        height: 100%;
        transition: all 0.3s ease;
    }

    .forgot-link {
        background: none;
        border: none;
        color: #818cf8;
        font-size: 14px;
        cursor: pointer;
        text-decoration: underline;
    }

    .forgot-link:hover {
        color: #6366f1;
    }

    /* ====================== MODAL ====================== */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
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
        border: 1px solid var(--border-color);
        padding: 30px;
        border-radius: 16px;
        width: 100%;
        max-width: 420px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    }

    .modal-content h2 {
        color: var(--text-primary);
        margin-bottom: 20px;
    }

    /* ====================== RESPONSIVE ====================== */
    @media (max-width: 768px) {
        .profile-main {
            grid-template-columns: 1fr;
        }

        .dashboard-container {
            padding: 20px;
        }
    }

    /* Small improvements for dark mode */
    .card:hover {
        border-color: #475569;
    }
</style>