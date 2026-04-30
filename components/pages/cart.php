<?php
session_start();

// Get logged-in user
$userId = $_SESSION['user_id'] ?? null;

// Helper function for URLs
if (!function_exists('pageUrl')) {
    function pageUrl($page, $params = [])
    {
        $url = 'index.php?page=' . urlencode($page);
        foreach ($params as $key => $value) {
            $url .= '&' . urlencode($key) . '=' . urlencode($value);
        }
        return $url;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - TemaTech Innovation</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include __DIR__ . '/../../includes/navbar-styles.php'; ?>
</head>

<body data-user-id="<?= htmlspecialchars($userId ?? '') ?>">
    <?php include __DIR__ . '/../../includes/page-loader.php'; ?>

    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <!-- Breadcrumb -->
    <section class="breadcrumb-section">
        <div class="container">
            <div class="breadcrumb">
                <a href="<?= pageUrl('home') ?>">Home</a> / <span>Shopping Cart</span>
            </div>
        </div>
    </section>

    <!-- MAIN -->
    <main class="cart-content">
        <div class="container">

            <!-- Header -->
            <div class="cart-header">
                <h1>Shopping Cart</h1>
                <p id="cart-count">0 items in your cart</p>
            </div>

            <div class="cart-grid">

                <!-- ===================== -->
                <!-- CART ITEMS (JS RENDER) -->
                <!-- ===================== -->
                <div class="cart-items-section" id="cart-items">

                    <div id="cart-loading" style="text-align:center; padding:20px;">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Loading your cart...</p>
                    </div>
                    <!-- JS WILL RENDER HERE -->
                </div>

                <!-- ===================== -->
                <!-- CART SUMMARY -->
                <!-- ===================== -->
                <div class="cart-summary">

                    <h2>Order Summary</h2>

                    <div class="summary-row">
                        <span class="label">Subtotal:</span>
                        <span class="value" id="cart-subtotal">K0.00</span>
                    </div>

                    <div class="summary-row total">
                        <span class="label">Total:</span>
                        <span class="value" id="cart-total">K0.00</span>
                    </div>

                    <!-- LOGIN CHECK -->
                    <?php if (!$userId): ?>
                        <div class="login-warning">
                            <p>
                                <i class="fas fa-exclamation-circle"></i>
                                Please login to proceed to checkout
                            </p>
                            <a href="<?= pageUrl('login') ?>" class="btn-secondary">
                                Login
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="<?= pageUrl('checkout') ?>" class="btn-checkout" id="checkout-btn">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </a>
                    <?php endif; ?>

                    <div class="continue-shopping">
                        <a href="<?= pageUrl('products') ?>">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <!-- ===================== -->
    <!-- CART SCRIPT -->
    <!-- ===================== -->
    <script src="/tematech-innovation/includes/cart-manager.js"></script>

    <script src="/tematech-innovation/includes/cart-manager.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            // Initialize cart (loads DB for logged-in, localStorage for guests)
            await cart.initialize();

            // Render the cart immediately after initialization
            renderCart();

            // Listen for real-time cart updates
            document.addEventListener('cartUpdated', () => {
                renderCart();
            });
        });

        document.getElementById('checkout-btn')?.addEventListener('click', async (e) => {
            e.preventDefault();

            const checkoutUrl = e.currentTarget.getAttribute('href');

            try {
                const res = await fetch('/tematech-innovation/api/order.php', {
                    method: 'POST'
                });

                const data = await res.json();

                if (!data.success) {
                    alert(data.message || 'Checkout failed');
                    return;
                }

                // CLEAR LOCAL STORAGE CART
                if (window.cart) {
                    cart.clearCart(false); // false = DO NOT call API again
                } else {
                    localStorage.removeItem('cart'); // fallback
                }

                // OPTIONAL: trigger UI update immediately
                document.dispatchEvent(new Event('cartUpdated'));

                // SAFE redirect
                const separator = checkoutUrl.includes('?') ? '&' : '?';
                window.location.href = checkoutUrl + separator + 'order_id=' + data.order_id;

            } catch (err) {

                alert('Something went wrong');
            }
        });
    </script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            opacity: 0;
            animation: pageLoad 0.6s ease-out forwards;
        }

        @keyframes pageLoad {
            to {
                opacity: 1;
            }
        }

        /* Scroll animations */
        .scroll-reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        .stagger-item {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .stagger-item.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Breadcrumb */
        .breadcrumb-section {
            padding: 1rem 0;
            background: white;
            border-bottom: 1px solid #e9ecef;
        }

        .breadcrumb {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: #FF6B35;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Main Content */
        .cart-content {
            padding: 3rem 0;
        }

        .cart-header {
            margin-bottom: 2rem;
        }

        .cart-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #212529;
        }

        .cart-header p {
            color: #6c757d;
        }

        .cart-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        /* Cart Items */
        .cart-items-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 1.5rem;
            padding: 1.5rem 0;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item:hover {
            background-color: #f8f9fa;
            padding-left: 1rem;
            padding-right: 1rem;
            margin-left: -1rem;
            margin-right: -1rem;
            border-radius: 8px;
        }

        .item-image {
            width: 100px;
            height: 100px;
            background-color: #f1f3f5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .item-image i {
            font-size: 2.5rem;
            color: #adb5bd;
        }

        .item-details {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .item-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #212529;
        }

        .item-category {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .item-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #FF6B35;
        }

        .item-actions {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }

        .quantity-btn {
            padding: 0.5rem 0.75rem;
            background-color: #f8f9fa;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .quantity-btn:hover {
            background-color: #e9ecef;
        }

        .quantity-value {
            padding: 0.5rem 0.75rem;
            font-weight: 600;
            min-width: 40px;
            text-align: center;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            padding: 0.5rem;
        }

        .remove-btn:hover {
            color: #c82333;
            transform: scale(1.1);
        }

        /* Empty Cart */
        .empty-cart {
            text-align: center;
        }

        .empty-cart i {
            font-size: 1rem;
            color: #FFFFFF;

        }

        .empty-cart h2 {
            font-size: 1.75rem;
            color: #495057;
        }

        .empty-cart p {
            color: #6c757d;
            margin-bottom: 2rem;
        }

        .btn-primary {
            padding: 10px;
            background: #FF6B35;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary:hover {
            background: #E85A2A;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
        }

        /* Cart Summary */
        .cart-summary {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 20px;
        }

        .cart-summary h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #212529;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            color: #495057;
        }

        .summary-row.total {
            font-size: 1.25rem;
            font-weight: 700;
            color: #212529;
            padding-top: 1rem;
            border-top: 2px solid #e9ecef;
            margin-top: 1rem;
        }

        .summary-row .label {
            font-weight: 500;
        }

        .summary-row .value {
            font-weight: 600;
        }

        .summary-row.total .value {
            color: #FF6B35;
        }

        .btn-secondary {
            width: 100%;
            margin: 0 auto;
            padding: 5px;
            background: #91919136;
            color: #FF6B35;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }

        .btn-secondary:hover {
            background: #e9ecef;
        }

        .btn-checkout {
            width: 100%;
            padding: 1rem;
            background: #FF6B35;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .btn-checkout:hover {
            background: #E85A2A;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
        }

        /* Continue Shopping */
        .continue-shopping {
            margin-top: 2rem;
            text-align: center;
        }

        .continue-shopping a {
            color: #FF6B35;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .continue-shopping a:hover {
            gap: 1rem;
        }

        /* Footer */
        .footer {
            background-color: #212529;
            color: #f8f9fa;
            padding: 3rem 0 1.5rem;
            margin-top: 4rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h4 {
            margin-bottom: 1rem;
            color: #FF6B35;
        }

        .footer-section a {
            display: block;
            color: #adb5bd;
            text-decoration: none;
            margin-bottom: 0.5rem;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #FF6B35;
        }

        .footer-divider {
            border-top: 1px solid #495057;
            padding-top: 1.5rem;
            text-align: center;
            color: #6c757d;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .cart-grid {
                grid-template-columns: 1fr;
            }

            .cart-summary {
                position: static;
            }

            .cart-item {
                grid-template-columns: 80px 1fr;
                gap: 1rem;
            }

            .item-actions {
                grid-column: 2;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }
        }
    </style>

</body>

</html>