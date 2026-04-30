<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Tema Tech</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            --warning: #fbbf24;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        /* ====================== SIDE NAVBAR ====================== */
        .side-navbar {
            position: fixed;
            left: 0;
            top: 0;
            width: 200px;
            height: 100vh;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            overflow-y: auto;
        }

        .side-navbar-header {
            padding: 24px;
            border-bottom: 1px solid var(--border-color);
        }

        .side-navbar-logo {
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
            color: var(--text-primary);
        }

        .side-navbar-logo .tema {
            color: var(--accent);
        }

        .side-navbar-logo .tech {
            color: var(--text-primary);
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
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 15px;
            font-weight: 500;
        }

        .side-navbar-link:hover,
        .side-navbar-link.active {
            color: var(--accent);
            background: rgba(255, 107, 53, 0.1);
            border-left: 4px solid var(--accent);
            padding-left: 20px;
        }

        .side-navbar-link i {
            width: 20px;
            text-align: center;
        }

        .message-counter-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            background: var(--accent);
            color: #fff;
            border-radius: 50%;
            font-size: 11px;
            font-weight: 700;
            margin-left: auto;
        }

        .side-navbar-footer {
            padding: 24px;
            border-top: 1px solid var(--border-color);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 12px 16px;
            background: rgba(248, 113, 113, 0.15);
            color: var(--danger);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background: rgba(248, 113, 113, 0.25);
            transform: translateX(2px);
        }

        .logout-btn i {
            width: 20px;
            text-align: center;
        }

        /* ====================== TOP NAVBAR ====================== */
        .top-navbar {
            position: fixed;
            top: 0;
            left: 200px;
            right: 0;
            height: 70px;
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            z-index: 999;
        }

        .top-navbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .continue-shopping-btn {
            padding: 10px 20px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .continue-shopping-btn:hover {
            background: #e55a28;
            transform: translateY(-2px);
        }

        .search-container {
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-toggle {
            width: 40px;
            height: 40px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            transition: all 0.2s;
        }

        .search-toggle:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .search-input {
            padding: 10px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            width: 0;
            opacity: 0;
            transition: all 0.3s ease;
            background: var(--bg-card);
            color: var(--text-primary);
            overflow: hidden;
        }

        .search-input.active {
            width: 220px;
            opacity: 1;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.2);
        }

        .top-navbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .navbar-icon-btn {
            width: 40px;
            height: 40px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            transition: all 0.2s;
            font-size: 16px;
        }

        .navbar-icon-btn:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: rgba(255, 107, 53, 0.1);
        }

        /* ====================== MAIN CONTENT ====================== */
        .dashboard-container {
            margin-left: 200px;
            margin-top: 70px;
            padding: 32px;
            min-height: calc(100vh - 70px);
            background: var(--bg-primary);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 32px;
        }

        /* Stat Cards */
        .stat-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .stat-card-title {
            font-size: 14px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card-value {
            font-size: 36px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-card-subtitle {
            font-size: 12px;
            color: #94a3b8;
        }

        /* Orders Section */
        .orders-section {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
        }

        .orders-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .orders-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .orders-filters {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border-color);
        }

        .filter-select {
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            background: var(--bg-secondary);
            color: var(--text-primary);
            transition: all 0.2s;
        }

        .filter-select:hover,
        .filter-select:focus {
            border-color: var(--accent);
            outline: none;
        }

        /* Orders Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table thead {
            background: #334155;
        }

        .orders-table th {
            padding: 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }

        .orders-table td {
            padding: 16px 12px;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
            color: var(--text-secondary);
        }

        .orders-table tbody tr:hover {
            background: #334155;
        }

        .order-id {
            font-weight: 600;
            color: var(--text-primary);
        }

        .order-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-completed {
            background: rgba(52, 211, 153, 0.2);
            color: var(--success);
        }

        .status-pending {
            background: rgba(251, 191, 36, 0.2);
            color: var(--warning);
        }

        .status-cancelled {
            background: rgba(248, 113, 113, 0.2);
            color: var(--danger);
        }

        /* Right Sidebar */
        .right-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .user-card,
        .notifications-section {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
        }

        .user-card-header {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), #ff8c5a);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 24px;
            flex-shrink: 0;
        }

        .user-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .user-email {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .view-profile-btn {
            align-self: flex-end;
            padding: 8px 16px;
            background: var(--bg-secondary);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .view-profile-btn:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        /* Notifications */
        .notifications-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .notifications-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .view-all-btn {
            padding: 6px 12px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .view-all-btn:hover {
            background: #e55a28;
        }

        .notification-card {
            padding: 16px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 12px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(96, 165, 250, 0.2);
            color: #60a5fa;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
        }

        .notification-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .notification-message {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .notification-time {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 6px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
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

            .dashboard-container {
                margin-left: 250px;
                padding: 20px;
            }

            .stat-cards {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .side-navbar {
                width: 60px;
            }

            .side-navbar-header,
            .side-navbar-link span,
            .side-navbar-footer {
                display: none;
            }

            .side-navbar-link {
                justify-content: center;
                padding: 12px;
            }

            .top-navbar {
                left: 60px;
                padding: 0 12px;
            }

            .dashboard-container {
                margin-left: 60px;
                padding: 12px;
            }
        }
    </style>
</head>

<body>
    <!-- Side Navbar -->
    <nav class="side-navbar">
        <div class="side-navbar-header">
            <a href="index.php?page=dashboard" class="side-navbar-logo">
                <span class="tema">Tema</span><span class="tech">Tech</span>
            </a>
        </div>

        <div class="side-navbar-content">
            <a href="index.php?page=dashboard" class="side-navbar-link active">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a href="index.php?page=orders" class="side-navbar-link">
                <i class="fas fa-shopping-bag"></i>
                <span>Orders</span>
            </a>

        </div>

        <div class="side-navbar-footer">
            <form action="../auth/logout.php" method="POST">
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
            <button class="continue-shopping-btn" onclick="window.location.href='../index.php?page=products'">
                <i class="fas fa-shopping-bag"></i>
                Continue Shopping
            </button>

           
        </div>

        <div class="top-navbar-right">
            <button class="navbar-icon-btn" title="Settings" onclick="window.location.href='index.php?page=profile'">
                <i class="fas fa-cog"></i>
            </button>
           
        </div>
    </nav>

    <script>
        // Toggle search input expansion
        function toggleSearch() {
            const searchInput = document.getElementById('searchInput');
            searchInput.classList.toggle('active');
            if (searchInput.classList.contains('active')) {
                searchInput.focus();
            }
        }

        // Set active link based on current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
            const links = document.querySelectorAll('.side-navbar-link');
            links.forEach(link => {
                link.classList.remove('active');
                if (link.href.includes('page=' + currentPage)) {
                    link.classList.add('active');
                }
            });
        });
    </script>