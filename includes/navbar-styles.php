<style>
    /* Navbar Color Variables */
    :root {
        --color-primary: #FF6B35;
        --color-primary-dark: #E85A2A;
        --color-text: #333333;
        --color-text-light: #666666;
        --color-border: #E5E7EB;
        --color-gray-50: #F9FAFB;
        --color-gray-100: #F3F4F6;
        --color-gray-200: #E5E7EB;
        --color-gray-500: #6B7280;
        --color-error: #EF4444;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    /* Top Navbar Styles */
    .top-navbar {
        /* background: #F9FAFB; */
        border-bottom: 1px solid var(--color-border);
        padding: 0.5rem 0;
        font-size: 0.875rem;
        transition: transform 0.3s ease, opacity 0.3s ease;
    }

    .top-navbar.hidden {
        transform: translateY(-100%);
        opacity: 0;
        pointer-events: none;
    }

    .top-navbar-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .top-navbar-left {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--color-text);
    }

    .top-navbar-left i {
        font-size: 0.875rem;
    }

    .top-navbar-right {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .social-icon {
        color: var(--color-text);
        font-size: 1rem;
        transition: color 0.2s;
    }

    .social-icon:hover {
        color: var(--color-primary);
    }

    .welcome-text {
        color: var(--color-text);
        margin-right: 0.5rem;
    }

    .top-nav-link {
        color: var(--color-text);
        text-decoration: none;
        transition: color 0.2s;
    }

    .top-nav-link:hover {
        color: var(--color-primary);
    }

    .btn-signin {
        background: var(--color-primary);
        color: white;
        border: none;
        padding: 0.5rem 1.5rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-signin:hover {
        background: var(--color-primary-dark);
    }

    /* Main Navbar */
    .main-navbar {
        background: white;
        padding: 1rem 0;
        position: sticky;
        top: 0;
        z-index: 100;
        transition: top 0.3s ease;
    }

    .main-navbar.scrolled {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .navbar-content {
        display: flex;
        align-items: center;
        gap: 2rem;
        justify-content: space-between;
    }

    .mobile-menu-btn {
        display: none;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--color-text);
    }

    .navbar-logo {
        flex-shrink: 0;
    }

    .navbar-logo a {
        text-decoration: none;
    }

    .navbar-logo h2 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .logo-tema {
        color: var(--color-primary);
    }

    .logo-tech {
        color: var(--color-text);
    }

    .navbar-search {
        flex: 1;
        max-width: 600px;
        display: flex;
        justify-content: center;
        margin: 0 auto;
    }

    .navbar-search form {
        width: 100%;
    }

    .navbar-actions {
        display: flex;
        gap: 1.5rem;
        align-items: center;
        margin-left: auto;
    }

    .search-input-group {
        display: flex;
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        overflow: hidden;
        background: white;
    }

    .search-input-group input {
        flex: 1;
        padding: 0.625rem 1rem;
        border: none;
        outline: none;
        font-size: 0.875rem;
    }

    .search-input-group input::placeholder {
        color: #9CA3AF;
    }

    .search-btn {
        background: white;
        color: var(--color-primary);
        border: none;
        padding: 0 1.25rem;
        cursor: pointer;
        font-size: 1.125rem;
        transition: color 0.2s;
    }

    .search-btn:hover {
        color: var(--color-primary-dark);
    }

    .navbar-action-item {
        position: relative;
    }

    .navbar-action-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        position: relative;
        color: var(--color-text);
        transition: color 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .navbar-action-btn:hover {
        color: var(--color-primary);
    }

    .badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: var(--color-primary);
        color: white;
        border-radius: 50%;
        padding: 0.125rem 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
        min-width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Dropdowns */
    .dropdown-menu {
        display: none;
        position: absolute;
        top: calc(100% + 0.5rem);
        right: 0;
        background: white;
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        min-width: 250px;
        z-index: 1000;
    }

    .dropdown-menu.show {
        display: block;
    }

    .dropdown-header {
        padding: 1rem;
        border-bottom: 1px solid var(--color-border);
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .dropdown-header h4 {
        margin: 0;
        font-size: 1rem;
        color: var(--color-text);
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--color-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.125rem;
    }

    .user-info {
        flex: 1;
    }

    .user-name {
        margin: 0;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--color-text);
    }

    .user-email {
        margin: 0;
        font-size: 0.75rem;
        color: var(--color-text-light);
    }

    .dropdown-content {
        padding: 0.5rem 0;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        color: var(--color-text);
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
        position: relative;
    }

    .dropdown-item:hover {
        background: var(--color-gray-50);
        border-left-color: var(--color-primary);
        padding-left: 1.25rem;
        color: var(--color-primary);
    }

    .dropdown-item.text-danger {
        color: var(--color-error);
    }

    .dropdown-item i {
        width: 1.25rem;
        text-align: center;
    }

    .notification-item {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--color-border);
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-message {
        margin: 0 0 0.25rem 0;
        font-size: 0.875rem;
        color: var(--color-text);
    }

    .notification-time {
        font-size: 0.75rem;
        color: var(--color-text-light);
    }

    .notification-empty {
        padding: 2rem 1rem;
        text-align: center;
        color: var(--color-text-light);
    }

    .notification-empty i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    /* Bottom Navbar */
    .bottom-navbar {
        border-top: 1px solid var(--color-border);
        border-bottom: 1px solid var(--color-border);
        position: sticky;
        top: 0;
        z-index: 99;
        transition: top 0.3s ease;
    }

    .main-navbar.scrolled~.bottom-navbar {
        top: 0;
    }

    .bottom-navbar-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 0;
    }

    .bottom-nav-item {
        position: relative;
    }

    .bottom-nav-item .dropdown-menu {
        left: 0;
        right: auto;
        min-width: 220px;
    }

    .bottom-nav-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: white;
        border: 1px solid var(--color-border);
        padding: 0.625rem 1.25rem;
        border-radius: 0.375rem;
        font-size: 0.9375rem;
        font-weight: 500;
        cursor: pointer;
        color: var(--color-text);
        transition: all 0.2s;
    }

    .bottom-nav-btn:hover {
        background: var(--color-gray-100);
    }

    .bottom-nav-btn i {
        font-size: 1rem;
    }

    .bottom-nav-links {
        display: flex;
        gap: 2rem;
    }

    .bottom-nav-link {
        color: var(--color-text);
        text-decoration: none;
        padding: 0.5rem 0;
        font-size: 0.9375rem;
        font-weight: 500;
        transition: color 0.2s;
        position: relative;
    }

    .bottom-nav-link:hover {
        color: var(--color-primary);
    }

    .bottom-nav-link.active {
        color: var(--color-primary);
    }

    .bottom-nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--color-primary);
    }

    /* Side Panel */
    .side-panel-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    .side-panel-overlay.active {
        display: block;
    }

    .side-panel {
        position: fixed;
        top: 0;
        left: -320px;
        width: 320px;
        height: 100%;
        background: white;
        transition: left 0.3s ease;
        z-index: 1000;
        overflow-y: auto;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .side-panel.open {
        left: 0;
    }

    .side-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--color-border);
        background: var(--color-gray-50);
    }

    .side-panel-header h3 {
        margin: 0;
        font-size: 1.125rem;
        color: var(--color-text);
    }

    .side-panel-close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--color-text);
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.25rem;
        transition: background 0.2s;
    }

    .side-panel-close-btn:hover {
        background: var(--color-gray-100);
    }

    .side-panel-content {
        padding: 1rem 0;
    }

    .side-panel-user-profile {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--color-border);
        margin-bottom: 0.5rem;
    }

    .side-panel-user-profile .user-avatar {
        width: 48px;
        height: 48px;
        font-size: 1.25rem;
    }

    .side-panel-dropdown {
        margin-bottom: 0.5rem;
    }

    .side-panel-dropdown-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 0.875rem 1.5rem;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 0.9375rem;
        font-weight: 500;
        color: var(--color-text);
        text-align: left;
        transition: background 0.2s;
    }

    .side-panel-dropdown-header:hover {
        background: var(--color-gray-50);
    }

    .side-panel-dropdown-header.open .chevron {
        transform: rotate(180deg);
    }

    .side-panel-dropdown-header .chevron {
        transition: transform 0.2s;
        color: var(--color-text-light);
    }

    .side-panel-dropdown-menu {
        display: none;
        padding: 0.5rem 0;
        background: var(--color-gray-50);
    }

    .side-panel-dropdown-menu.open {
        display: block;
    }

    .side-panel-dropdown-menu a {
        display: block;
        padding: 0.625rem 1.5rem 0.625rem 3rem;
        color: var(--color-text);
        text-decoration: none;
        font-size: 0.875rem;
        transition: background 0.2s;
    }

    .side-panel-dropdown-menu a:hover {
        background: white;
        color: var(--color-primary);
    }

    .side-panel-section {
        margin-bottom: 0.5rem;
    }

    .side-panel-section-title {
        font-size: 0.75rem;
        color: var(--color-text-light);
        margin: 1rem 0 0.5rem 0;
        padding: 0 1.5rem;
        font-weight: 600;
        letter-spacing: 0.05em;
    }

    .side-panel-nav {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .side-panel-nav li a {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1.5rem;
        color: var(--color-text);
        text-decoration: none;
        transition: background 0.2s;
        font-size: 0.9375rem;
    }

    .side-panel-nav li a:hover {
        background: var(--color-gray-50);
        color: var(--color-primary);
    }

    .side-panel-nav li a i {
        width: 1.25rem;
        text-align: center;
        color: var(--color-text-light);
    }

    .side-panel-logout-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1.5rem;
        color: var(--color-error);
        text-decoration: none;
        transition: background 0.2s;
        font-size: 0.9375rem;
    }

    .side-panel-logout-btn:hover {
        background: rgba(239, 68, 68, 0.05);
    }

    .side-panel-logout-btn i {
        width: 1.25rem;
        text-align: center;
    }

    .side-panel-divider {
        border-top: 1px solid var(--color-border);
        margin: 0.5rem 0;
    }

    /* Cart Side Panel */
    .cart-panel-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 998;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

    .cart-panel-overlay.active {
        display: block;
    }

    body.cart-open {
        overflow: hidden;
    }

    body.cart-open>*:not(.cart-panel):not(.cart-panel-overlay) {
        filter: blur(2px);
    }

    .cart-panel {
        position: fixed;
        top: 0;
        right: -400px;
        width: 400px;
        height: 100%;
        background: white;
        transition: right 0.3s ease;
        z-index: 999;
        overflow-y: auto;
        box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
    }

    .cart-panel.open {
        right: 0;
    }

    .cart-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid var(--color-border);
        background: var(--color-gray-50);
    }

    .cart-panel-header h3 {
        margin: 0;
        font-size: 1.25rem;
        color: var(--color-text);
        font-weight: 600;
    }

    .cart-panel-close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--color-text);
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.25rem;
        transition: background 0.2s;
    }

    .cart-panel-close-btn:hover {
        background: var(--color-gray-200);
    }

    .cart-panel-content {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
    }

    .cart-items {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .cart-item {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        background: white;
    }

    .cart-item-image {
        width: 80px;
        height: 80px;
        border-radius: 0.375rem;
        overflow: hidden;
        background: var(--color-gray-100);
    }

    .cart-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cart-item-details {
        flex: 1;
    }

    .cart-item-name {
        margin: 0 0 0.5rem 0;
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--color-text);
    }

    .cart-item-price {
        margin: 0 0 0.75rem 0;
        font-size: 1rem;
        font-weight: 600;
        color: var(--color-primary);
    }

    .cart-item-quantity {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .qty-btn {
        width: 28px;
        height: 28px;
        border: 1px solid var(--color-border);
        background: white;
        border-radius: 0.25rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: var(--color-text);
        transition: all 0.2s;
    }

    .qty-btn:hover {
        background: var(--color-primary);
        color: white;
        border-color: var(--color-primary);
    }

    .cart-item-quantity span {
        min-width: 30px;
        text-align: center;
        font-weight: 600;
    }

    .cart-item-remove {
        background: none;
        border: none;
        color: var(--color-error);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 0.25rem;
        transition: background 0.2s;
    }

    .cart-item-remove:hover {
        background: rgba(239, 68, 68, 0.1);
    }

    .cart-empty {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--color-text-light);
    }

    .cart-empty i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: var(--color-border);
    }

    .cart-empty p {
        margin: 0 0 1.5rem 0;
        font-size: 1rem;
    }

    .btn-shop-now {
        display: inline-block;
        background: var(--color-primary);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.375rem;
        text-decoration: none;
        font-weight: 500;
        transition: background 0.2s;
    }

    .btn-shop-now:hover {
        background: var(--color-primary-dark);
    }

    .cart-panel-footer {
        border-top: 1px solid var(--color-border);
        padding: 1.5rem;
        background: var(--color-gray-50);
    }

    .cart-summary {
        margin-bottom: 1rem;
    }

    .cart-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        font-size: 0.9375rem;
        color: var(--color-text);
    }

    .cart-summary-row.cart-total {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--color-text);
        border-top: 2px solid var(--color-border);
        padding-top: 1rem;
        margin-top: 0.5rem;
    }

    .cart-actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .btn-view-cart,
    .btn-checkout {
        display: block;
        text-align: center;
        padding: 0.75rem;
        border-radius: 0.375rem;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9375rem;
        transition: all 0.2s;
    }

    .btn-view-cart {
        background: white;
        color: var(--color-primary);
        border: 2px solid var(--color-primary);
    }

    .btn-view-cart:hover {
        background: var(--color-gray-100);
    }

    .btn-checkout {
        background: var(--color-primary);
        color: white;
        border: 2px solid var(--color-primary);
    }

    .btn-checkout:hover {
        background: var(--color-primary-dark);
        border-color: var(--color-primary-dark);
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .mobile-menu-btn {
            display: block;
        }

        .desktop-only {
            display: none !important;
        }

        .navbar-content {
            flex-wrap: wrap;
            gap: 1rem;
        }

        .navbar-logo h2 {
            font-size: 1.5rem;
        }

        .navbar-search {
            order: 3;
            flex: 1 1 100%;
            max-width: 100%;
        }

        .navbar-actions {
            gap: 1rem;
        }

        .top-navbar-left span {
            display: none;
        }

        .welcome-text {
            display: none;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding: 0 1rem;
        }

        .navbar-logo h2 {
            font-size: 1.25rem;
        }

        .navbar-action-btn {
            font-size: 1.25rem;
        }

        .cart-panel {
            width: 100%;
            right: -100%;
        }

        .cart-panel.open {
            right: 0;
        }
    }

    /* User Menu Styles */
    .user-menu {
        position: relative;
        display: inline-block;
    }

    .user-greeting {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        color: var(--color-text);
        font-weight: 600;
        font-size: 0.875rem;
    }

    .user-greeting:hover {
        background: var(--color-gray-100);
        color: var(--color-primary);
    }

    .user-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        min-width: 200px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1000;
        margin-top: 0.5rem;
    }

    .user-menu:hover .user-dropdown {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .user-menu-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        color: var(--color-text);
        text-decoration: none;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        border-bottom: 1px solid var(--color-border);
    }

    .user-menu-link:last-child {
        border-bottom: none;
    }

    .user-menu-link:hover {
        background: var(--color-gray-50);
        color: var(--color-primary);
        padding-left: 1.25rem;
    }

    .user-menu-link.logout {
        color: var(--color-error);
    }

    .user-menu-link.logout:hover {
        background: rgba(239, 68, 68, 0.1);
        color: var(--color-error);
    }
</style>