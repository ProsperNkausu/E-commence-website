<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// ===========================
// SESSION CHECK - Using user_id for customers
// ===========================
$isAdminLoggedIn   = isset($_SESSION['admin_id']);
$isCustomerLoggedIn = isset($_SESSION['user_id']);   // Changed to user_id

$displayName = '';
$userType    = '';

if ($isAdminLoggedIn) {
    $displayName = trim(($_SESSION['admin_first_name'] ?? '') . ' ' . ($_SESSION['admin_last_name'] ?? ''));
    $userType = 'admin';
} elseif ($isCustomerLoggedIn) {
    $displayName = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
    $userType = 'customer';
}

// ===========================
// FETCH CATEGORIES
// ===========================
try {
    $stmt = $pdo->query("SELECT name FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = []; // fallback to prevent crash
}
?>

<!-- Top Navbar -->
<div class="top-navbar">
    <div class="container">
        <div class="top-navbar-content">
            <div class="top-navbar-left">
                <i class="fas fa-phone"></i>
                <span>+260-975-30083</span>
            </div>
            <div class="top-navbar-right">
                <a href="https://www.facebook.com/profile.php?id=61552755659465&locale=en_GB#" target="_blank" class="social-icon">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://wa.me/260975300083" target="_blank" class="social-icon">
                    <i class="fab fa-whatsapp"></i>
                </a>

                <?php if ($isCustomerLoggedIn || $isAdminLoggedIn): ?>
                    <div class="user-menu">
                        <span class="user-greeting">
                            <i class="fas fa-user-circle"></i>
                            <?= htmlspecialchars($displayName ?: 'User') ?>
                        </span>
                        <div class="user-dropdown">

                            <?php if ($isAdminLoggedIn): ?>
                                <!-- ADMIN MENU -->
                                <a href="admin/index.php" class="user-menu-link">
                                    <i class="fas fa-chart-line"></i> Admin Dashboard
                                </a>
                                <?php if ($_SESSION['admin_role'] === 'staff'): ?>
                                    <a href="staff/index.php" class="user-menu-link">
                                        <i class="fas fa-briefcase"></i> Staff Panel
                                    </a>
                                <?php endif; ?>

                            <?php else: ?>
                                <!-- CUSTOMER MENU -->
                                <a href="users/index.php?page=dashboard" class="user-menu-link">
                                    <i class="fas fa-user"></i> My Account
                                </a>
                                <a href="users/index.php?page=orders" class="user-menu-link">
                                    <i class="fas fa-shopping-cart"></i> My Orders
                                </a>
                            <?php endif; ?>

                            <a href="auth/logout.php" class="user-menu-link logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <button class="btn-signin" onclick="window.location.href='index.php?page=login'">
                        Sign In
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Main Navbar -->
<nav class="main-navbar" id="main-navbar">
    <div class="container">
        <div class="navbar-content">

            <button class="mobile-menu-btn" id="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>

            <div class="navbar-logo">
                <a href="index.php?page=home">
                    <h2><span class="logo-tema">Tema</span> <span class="logo-tech">Tech</span></h2>
                </a>
            </div>

            <!-- Navbar Search -->
            <div class="navbar-search">
                <form action="index.php" method="GET">
                    <input type="hidden" name="page" value="products">
                    <div class="search-input-group">
                        <input type="text" name="search" placeholder="Search products..."
                            value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div class="navbar-actions">
                <div class="navbar-action-item">
                    <button class="navbar-action-btn" id="cart-btn">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                </div>
            </div>

        </div>
    </div>
</nav>

<!-- Bottom Navbar -->
<div class="bottom-navbar desktop-only">
    <div class="container">
        <div class="bottom-navbar-content">

            <div class="bottom-nav-item dropdown" id="categories-dropdown">
                <button class="bottom-nav-btn" id="categories-btn">
                    <i class="fas fa-th"></i>
                    Browse Categories
                </button>

                <div class="dropdown-menu" id="categories-menu">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <a href="index.php?page=products&category=<?= urlencode($cat['name']) ?>" class="dropdown-item">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="dropdown-item">No categories available</span>
                    <?php endif; ?>
                </div>
            </div>

            <nav class="bottom-nav-links">
                <a href="index.php?page=home" class="bottom-nav-link">Home</a>
                <a href="index.php?page=products" class="bottom-nav-link">Products</a>
                <a href="index.php?page=about" class="bottom-nav-link">About</a>
                <a href="index.php?page=contact" class="bottom-nav-link">Contact</a>
            </nav>

        </div>
    </div>
</div>

<!-- Mobile Side Panel -->
<div class="side-panel-overlay" id="side-panel-overlay"></div>
<div class="side-panel" id="side-panel">
    <div class="side-panel-header">
        <h3>Menu</h3>
        <button class="side-panel-close-btn" id="side-panel-close">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="side-panel-content">

        <div class="side-panel-dropdown">
            <button class="side-panel-dropdown-header" id="mobile-categories-btn">
                <span>Browse Categories</span>
                <span class="chevron"><i class="fas fa-chevron-down"></i></span>
            </button>
            <div class="side-panel-dropdown-menu" id="mobile-categories-menu">
                <?php foreach ($categories as $cat): ?>
                    <a href="index.php?page=products&category=<?= urlencode($cat['name']) ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
                <a href="index.php?page=products">View All Products</a>
            </div>
        </div>

        <nav class="side-panel-section">
            <ul class="side-panel-nav">
                <li><a href="index.php?page=home"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="index.php?page=products"><i class="fas fa-shopping-bag"></i> Products</a></li>
                <li><a href="index.php?page=about"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="index.php?page=contact"><i class="fas fa-envelope"></i> Contact</a></li>

                <?php if ($isCustomerLoggedIn || $isAdminLoggedIn): ?>
                    <?php if ($isAdminLoggedIn): ?>
                        <li><a href="admin/index.php"><i class="fas fa-chart-line"></i> Admin Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="users/index.php?page=dashboard"><i class="fas fa-user"></i> My Account</a></li>
                        <li><a href="index.php?page=orders"><i class="fas fa-shopping-cart"></i> My Orders</a></li>
                    <?php endif; ?>
                    <li><a href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="index.php?page=login"><i class="fas fa-sign-in-alt"></i> Sign In</a></li>
                <?php endif; ?>
            </ul>
        </nav>

    </div>
</div>

<!-- Navbar JavaScript -->
<script src="includes/navbar-script.js"></script>