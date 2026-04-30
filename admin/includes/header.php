<?php
require_once __DIR__ . '/../../config/db.php';

/*
|--------------------------------------------------------------------------
| Admin Authentication Check
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php?page=login");
    exit();
}

$adminId = $_SESSION['admin_id'];

/*
|--------------------------------------------------------------------------
| Fetch Admin Information
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT 
        a.first_name,
        a.last_name,
        r.name AS role
    FROM admins a
    JOIN admin_roles r ON a.role_id = r.id
    WHERE a.id = ?
");
$stmt->execute([$adminId]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

$adminName = $admin['first_name'] . ' ' . $admin['last_name'];
$adminRole = ucfirst($admin['role']);


/*
|--------------------------------------------------------------------------
| Dashboard Counters
|--------------------------------------------------------------------------
*/

// Pending Orders
$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM orders 
    WHERE status = 'pending'
");
$pendingOrders = $stmt->fetchColumn();

// Pending Payments
$stmt = $pdo->query("
    SELECT COUNT(*) 
    FROM orders 
    WHERE payment_status = 'pending'
");
$pendingPayments = $stmt->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Tema Tech</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/tematech-innovation/admin/css/admin-dark.css">
    <style>
        

        .admin-dropdown {
            position: absolute;
            top: 70px;
            right: 32px;
            background: var(--bg-primary);
            /* border: 1px solid #e5e7eb; */
            border-radius: 8px;
            width: 200px;
            display: none;
            flex-direction: column;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow: hidden;
        }

        .admin-dropdown a,
        .admin-dropdown button {
            padding: 12px 16px;
            border: none;
            background: none;
            text-align: left;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-primary);
            transition: all 0.2s;
        }

        .admin-dropdown a i,
        .admin-dropdown button i {
            width: 18px;
            text-align: center;
            color: #FF6B35;
        }

        .admin-dropdown a:hover,
        .admin-dropdown button:hover {
            background: #f9fafb;
            color: #FF6B35;
            padding-left: 20px;
        }

        .admin-dropdown button {
            width: 100%;
            text-align: left;
        }

        /* Side Navbar */
        .side-navbar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: var(--bg-primary);
            border-right: 1px solid #111827;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            overflow-y: auto;
        }

        .side-navbar-header {
            padding: 24px;
            border-bottom: 1px solid var(--accent); 
        }

        .side-navbar-logo {
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
            /* color: var(--text-primary); */
            display: flex;
            align-items: center;

        }

        .side-navbar-logo .tema {
            color: #FF6B35;
        }

        .side-navbar-logo .tech {
            color: #fff;
        }

        .side-navbar-content {
            flex: 1;
            padding: 24px 0;
        }

        .side-navbar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.2s;
            font-size: 15px;
            font-weight: 500;
            position: relative;
        }

        .side-navbar-link:hover,
        .side-navbar-link.active {
            color: #FF6B35;
            background: rgba(255, 107, 53, 0.1);
            border-left: 4px solid #FF6B35;
            padding-left: 20px;
        }

        .side-navbar-link i {
            width: 20px;
            text-align: center;
        }

        .counter-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: #FF6B35;
            color: #fff;
            border-radius: 50%;
            font-size: 11px;
            font-weight: 700;
            margin-left: auto;
        }

        .side-navbar-footer {
            padding: 24px;
            border-top: 1px solid var(--accent);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 12px 16px;
            background: rgba(220, 38, 38, 0.1);
            color: #ef4444;
            border: 1px solid #7f1d1d;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background: rgba(220, 38, 38, 0.2);
            border-color: #dc2626;
        }

        .logout-btn i {
            width: 20px;
            text-align: center;
        }

        /* Top Navbar */
        .top-navbar {
            position: fixed;
            top: 0;
            left: 280px;
            right: 0;
            height: 87px;
            background: var(--bg-primary);
            border-bottom: 1px solid var(--accent);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 52px;
            z-index: 999;
        }

        .top-navbar-left {
            display: flex;
            align-items: center;
            gap: 24px;
            flex: 1;
        }

        .page-title-all {
            font-size: 30px;
            font-weight: 700;
            color: var(--accent);
        }

        .search-bar {
            position: relative;
            flex: 1;
            max-width: 350px;
        }

        .search-input {
            width: 100%;
            padding: 10px 14px 10px 40px;
            border: 1px solid var(--accent);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            background: var(--bg-card);
        }

        .search-input:focus {
            outline: none;
            background: #fff;
            border-color: #FF6B35;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        .search-bar i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-primary);
            pointer-events: none;
        }

        .top-navbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 12px;
            background: var(--bg-card);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .admin-profile:hover {
            background: var(--bg-card-hover);

        }



        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #FF6B35;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            flex-shrink: 0;
        }

        .admin-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .admin-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .admin-role {
            font-size: 12px;
            color: var(--accent);
        }

        .admin-dropdown-icon {
            color: #6b7280;
            font-size: 12px;
        }

        /* Main Layout */
        .admin-container {
            margin-left: 280px;
            margin-top: 70px;
            padding: 32px;
            min-height: calc(100vh - 70px);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .search-bar {
                max-width: 250px;
            }

            .admin-container {
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .side-navbar {
                width: 250px;
            }

            .top-navbar {
                left: 250px;
                padding: 0 16px;
            }

            .admin-container {
                margin-left: 250px;
                padding: 16px;
            }

            .top-navbar-left {
                gap: 12px;
            }

            .search-bar {
                max-width: 150px;
            }

            .admin-info {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .side-navbar {
                width: 60px;
                padding: 0;
            }

            .side-navbar-header,
            .side-navbar-link span,
            .side-navbar-footer {
                display: none;
            }

            .side-navbar-link {
                justify-content: center;
                padding: 12px;
                height: 50px;
            }

            .counter-badge {
                position: absolute;
                right: -8px;
                top: -8px;
            }

            .top-navbar {
                left: 60px;
                padding: 0 12px;
                flex-wrap: wrap;
            }

            .admin-container {
                margin-left: 60px;
                padding: 12px;
            }

            .search-bar {
                display: none;
            }

            .page-title-all {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <!-- Side Navbar -->
    <nav class="side-navbar">
        <div class="side-navbar-header">
            <h1 class="side-navbar-logo">
                <span class="tema">Tema</span><span class="tech">Tech</span>

            </h1>
        </div>
        <div class="side-navbar-content">
            <a href="index.php?page=dashboard" class="side-navbar-link active">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="index.php?page=products" class="side-navbar-link">
                <i class="fas fa-box"></i>
                <span>Products</span>
            </a>
            <a href="index.php?page=orders" class="side-navbar-link">
                <i class="fas fa-shopping-bag"></i>
                <span>Orders</span>
                <span class="counter-badge"><?= $pendingOrders ?></span>
            </a>

            <a href="index.php?page=shipping" class="side-navbar-link">
                <i class="fas fa-truck"></i>
                <span>Shipping</span>
            </a>
            <a href="index.php?page=payments" class="side-navbar-link">
                <i class="fas fa-credit-card"></i>
                <span>Payments</span>
                <span class="counter-badge"><?= $pendingPayments ?></span>
            </a>
            <a href="index.php?page=customers" class="side-navbar-link">
                <i class="fas fa-users"></i>
                <span>Customers</span>
            </a>
            <a href="index.php?page=statistics" class="side-navbar-link">
                <i class="fas fa-chart-bar"></i>
                <span>Statistics</span>
            </a>
            <a href="index.php?page=admins" class="side-navbar-link">
                <i class="fas fa-user-shield"></i>
                <span>Admins</span>
            </a>
            <!-- <a href="index.php?page=settings" class="side-navbar-link">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a> -->
        </div>

        <div class="side-navbar-footer">
            <form action="/tematech-innovation/auth/logout.php" method="POST">
                <input type="hidden" name="logout" value="1">
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </nav>

    <!-- Top Navbar -->
    <nav class="top-navbar">
        <div class="top-navbar-left">
            <h1 class="page-title-all">Dashboard</h1>
            <div class="search-bar">
                <input type="text" class="search-input" placeholder="Search...">
                <i class="fas fa-search"></i>
            </div>
        </div>

        <div class="top-navbar-right">
            <div class="admin-profile">
                <div class="admin-avatar">
                    <?= strtoupper(substr($admin['first_name'], 0, 1)) ?>
                </div>

                <div class="admin-info">
                    <div class="admin-name"><?= htmlspecialchars($adminName) ?></div>
                    <div class="admin-role"><?= htmlspecialchars($adminRole) ?></div>
                </div>
                <i class="fas fa-chevron-down admin-dropdown-icon"></i>
                <div class="admin-dropdown" id="adminDropdown">
                    <a href="index.php?page=profile">
                        <i class="fas fa-user"></i> Profile
                    </a>

                    <a href="index.php?page=settings">
                        <i class="fas fa-cog"></i> Settings
                    </a>

                    <form action="/tematech-innovation/auth/logout.php" method="POST">
                        <button type="submit">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Admin dropdown
        const profile = document.querySelector('.admin-profile');
        const dropdown = document.getElementById('adminDropdown');

        profile.addEventListener('click', () => {
            dropdown.style.display =
                dropdown.style.display === 'flex' ? 'none' : 'flex';
        });

        document.addEventListener('click', function(e) {
            if (!profile.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
        // Set active link based on current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';

            // Update page title
            const pageTitles = {
                'dashboard': 'Dashboard',
                'products': 'Products',
                'orders': 'Orders',
                'payments': 'Payments',
                'customers': 'Customers',
                'statistics': 'Statistics',
                'admins': 'Admins',
                'settings': 'Settings'
            };

            const pageTitle = document.querySelector('.page-title');
            if (pageTitle) {
                pageTitle.textContent = pageTitles[currentPage] || 'Dashboard';
            }

            // Set active link
            const links = document.querySelectorAll('.side-navbar-link');
            links.forEach(link => {
                link.classList.remove('active');
                if (link.href.includes('page=' + currentPage)) {
                    link.classList.add('active');
                }
            });
        });
    </script>