/**
 * Navbar JavaScript - Handles all navbar interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // ==========================================
    // Elements
    // ==========================================
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const sidePanelOverlay = document.getElementById('side-panel-overlay');
    const sidePanel = document.getElementById('side-panel');
    const sidePanelClose = document.getElementById('side-panel-close');
    const cartBtn = document.getElementById('cart-btn');
    const categoriesBtn = document.getElementById('categories-btn');
    const categoriesMenu = document.getElementById('categories-menu');
    const mobileCategoriesBtn = document.getElementById('mobile-categories-btn');
    const mobileCategoriesMenu = document.getElementById('mobile-categories-menu');
    const mainNavbar = document.getElementById('main-navbar');

    // ==========================================
    // Mobile Menu Functionality
    // ==========================================
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            sidePanelOverlay.classList.add('active');
            sidePanel.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
    }

    if (sidePanelClose) {
        sidePanelClose.addEventListener('click', function() {
            closeSidePanel();
        });
    }

    if (sidePanelOverlay) {
        sidePanelOverlay.addEventListener('click', function() {
            closeSidePanel();
        });
    }

    function closeSidePanel() {
        sidePanelOverlay.classList.remove('active');
        sidePanel.classList.remove('open');
        document.body.style.overflow = '';
    }

    // ==========================================
    // Categories Dropdown (Desktop)
    // ==========================================
    if (categoriesBtn && categoriesMenu) {
        categoriesBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            categoriesMenu.classList.toggle('show');
        });

        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#categories-dropdown')) {
                categoriesMenu.classList.remove('show');
            }
        });
    }

    // ==========================================
    // Categories Dropdown (Mobile)
    // ==========================================
    if (mobileCategoriesBtn && mobileCategoriesMenu) {
        mobileCategoriesBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            mobileCategoriesBtn.classList.toggle('open');
            mobileCategoriesMenu.classList.toggle('open');
        });
    }

    // ==========================================
    // Cart Button Functionality
    // ==========================================
    if (cartBtn) {
        cartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'index.php?page=cart';
        });
    }
    // ==========================================
    // Navbar Scroll Effect
    // ==========================================

    // ==========================================
    // Cart Counter Badge
    // ==========================================
    function updateCartBadge() {
        // Get cart count from localStorage or session
        const cartCount = getCartCount();

        if (cartCount > 0) {
            let badge = document.querySelector('.navbar-action-item .badge');

            // Create badge if it doesn't exist
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'badge';
                const actionItem = document.querySelector('.navbar-action-item');
                if (actionItem) {
                    actionItem.appendChild(badge);
                }
            }

            // Update badge count
            badge.textContent = cartCount > 99 ? '99+' : cartCount;
        } else {
            // Remove badge if cart is empty
            const badge = document.querySelector('.navbar-action-item .badge');
            if (badge) {
                badge.remove();
            }
        }
    }

    function getCartCount() {
        // Try to get from localStorage first (most common approach)
        const localCart = localStorage.getItem('cart');
        if (localCart) {
            try {
                const cart = JSON.parse(localCart);
                return Array.isArray(cart) ? cart.length : Object.keys(cart).length;
            } catch (e) {
                return 0;
            }
        }

        // Try to get from sessionStorage
        const sessionCart = sessionStorage.getItem('cart');
        if (sessionCart) {
            try {
                const cart = JSON.parse(sessionCart);
                return Array.isArray(cart) ? cart.length : Object.keys(cart).length;
            } catch (e) {
                return 0;
            }
        }

        return 0;
    }

    // Update badge on page load
    updateCartBadge();

    // Listen for cart updates from other tabs/windows
    window.addEventListener('storage', function(e) {
        if (e.key === 'cart') {
            updateCartBadge();
        }
    });

    // ==========================================
    // Custom Event for Cart Updates
    // ==========================================
    // Allow other scripts to update cart count using: 
    // document.dispatchEvent(new Event('cartUpdated'))
    document.addEventListener('cartUpdated', function() {
        updateCartBadge();
    });

    // ==========================================
    // Prevent dropdown close on dropdown-item click
    // ==========================================
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    dropdownItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Allow default behavior (navigation)
            e.stopPropagation();
        });
    });

    // ==========================================
    // Mobile responsive adjustments
    // ==========================================
    function handleResize() {
        const isMobile = window.innerWidth <= 768;

        if (!isMobile) {
            // Close side panel on desktop
            closeSidePanel();
        }
    }

    window.addEventListener('resize', handleResize);

    // ==========================================
    // Close menus when navigating on mobile
    // ==========================================
    const navLinks = document.querySelectorAll('.side-panel-nav a, .side-panel-dropdown-menu a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            closeSidePanel();
            if (mobileCategoriesBtn) {
                mobileCategoriesBtn.classList.remove('open');
                mobileCategoriesMenu.classList.remove('open');
            }
        });
    });
});
