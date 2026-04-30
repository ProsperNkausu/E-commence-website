<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Helper function for page URLs
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

// BLOCK access if NOT logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . pageUrl('login'));
    exit;
}

$userId = $_SESSION['user_id'];

// Get pending order
$orderPlaced = isset($_GET['payment']) && $_GET['payment'] === 'success';
$orderReference = $_GET['ref'] ?? null;

$cartItems = [];
$subtotal = 0;

if ($orderPlaced && $orderReference) {

    // AFTER PAYMENT → fetch order by reference (NOT pending)
    $stmt = $pdo->prepare("
        SELECT * FROM orders 
        WHERE order_reference = ? AND customer_id = ?
        LIMIT 1
    ");
    $stmt->execute([$orderReference, $userId]);
    $order = $stmt->fetch();

    if (!$order) {
        header("Location: " . pageUrl('cart'));
        exit;
    }
} else {

    // NORMAL CHECKOUT → pending order
    $stmt = $pdo->prepare("
        SELECT * FROM orders 
        WHERE customer_id = ? AND status = 'pending'
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $order = $stmt->fetch();

    if (!$order) {
        header("Location: " . pageUrl('cart'));
        exit;
    }
}

// Fetch customer details
$stmt = $pdo->prepare("SELECT first_name, last_name, email, phone FROM customers WHERE id = ?");
$stmt->execute([$userId]);
$customer = $stmt->fetch();


// GET ORDER ITEMS (works for BOTH flows)
$stmt = $pdo->prepare("
    SELECT 
        oi.product_id, 
        oi.quantity, 
        oi.price,
        p.name,
        pi.image_url AS image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN product_images pi 
        ON pi.product_id = p.id AND pi.is_primary = 1
    WHERE oi.order_id = ?
");
$stmt->execute([$order['id']]);
$cartItems = $stmt->fetchAll();


// Calculate totals
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = 0;
$tax = 0;
$total = $subtotal + $shipping + $tax;

// Handle order placement
$orderPlaced = isset($_GET['payment']) && $_GET['payment'] === 'success';

// img location for payment icons
$paymentIcons = [
    'airtel' => '/tematech-innovation/public/images/airtel-money.png',
    'mtn' => '/tematech-innovation/public/images/mtn-money.png',
    'zamtel' => '/tematech-innovation/public/images/zamtel-money.png'
];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - TemaTech Innovation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include __DIR__ . '/../../includes/navbar-styles.php'; ?>

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

        .scroll-slide-left {
            opacity: 0;
            transform: translateX(-50px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .scroll-slide-left.revealed {
            opacity: 1;
            transform: translateX(0);
        }

        .scroll-slide-right {
            opacity: 0;
            transform: translateX(50px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .scroll-slide-right.revealed {
            opacity: 1;
            transform: translateX(0);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* imgs style for payments */
        .accepted-payments {
            align-items: center;
            display: flex;
            /* gap: 1rem; */
            margin-top: 1rem;
        }

        .accepted-payments img {
            width: 60px;
            height: 40px;
            object-fit: contain;
        }

        .accepted-payments p {
            font-size: 14px;
            color: #6c757d;
            margin-right: 1rem;
        }

        /* notice */
        .notice {
            background: #0000001A;
            border-left: 4px solid #FF6B35;
            padding: 8px;
            margin-bottom: 1rem;
        }

        .notice .fa-info-circle {
            color: #FF6B35;
            margin-right: 8px;
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

        /* Checkout Steps */
        .checkout-steps {
            background: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }

        .steps-container {
            display: flex;
            justify-content: space-between;
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }

        .steps-container::before {
            content: '';
            position: absolute;
            top: 30px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e9ecef;
            z-index: 0;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .step-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #adb5bd;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .step.active .step-circle {
            background: #FF6B35;
            border-color: #FF6B35;
            color: white;
        }

        .step.completed .step-circle {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }

        .step-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 600;
        }

        .step.active .step-label {
            color: #FF6B35;
        }

        /* Main Content */
        .checkout-content {
            padding: 2rem 0 4rem;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        /* Checkout Form */
        .checkout-form-section {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .form-section h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #212529;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-section h2 i {
            color: #FF6B35;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.875rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #FF6B35;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
            transform: translateY(-2px);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            cursor: pointer;
        }

        .checkbox-group label {
            margin-bottom: 0;
            font-weight: 500;
            cursor: pointer;
        }

        /* Payment Methods */
        .payment-methods {
            display: grid;
            gap: 1rem;
        }

        .payment-option {
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .payment-option:hover {
            border-color: #FF6B35;
            background-color: #fff5f0;
        }

        .payment-option input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .payment-option.selected {
            border-color: #FF6B35;
            background-color: #fff5f0;
        }

        .payment-icon {
            width: 50px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #495057;
        }

        .payment-details {
            flex: 1;
        }

        .payment-details strong {
            display: block;
            margin-bottom: 0.25rem;
            color: #212529;
        }

        .payment-details small {
            color: #6c757d;
        }

        /* Order Summary */
        .order-summary {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 20px;
        }

        .order-summary h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #212529;
        }

        .order-items {
            margin-bottom: 1.5rem;
        }

        .order-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item-image {
            width: 60px;
            height: 60px;

            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .order-item-image i {
            font-size: 1.5rem;
            color: #adb5bd;
        }

        .order-item-details {
            flex: 1;
        }

        .order-item-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #212529;
            margin-bottom: 0.25rem;
        }

        .order-item-qty {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .order-item-price {
            font-weight: 700;
            color: #FF6B35;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
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

        .summary-row.total .value {
            color: #FF6B35;
        }

        .btn-place-order {
            width: 100%;
            padding: 1.25rem;
            background: #FF6B35;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }

        .btn-place-order:hover {
            background: #E85A2A;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 107, 53, 0.3);
        }

        .btn-place-order:active {
            transform: translateY(0);
        }

        .secure-note {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 1rem;
            color: #6c757d;
            font-size: 0.85rem;
        }

        .secure-note i {
            color: #28a745;
        }

        /* Order Confirmation */
        .order-confirmation {
            background: white;
            padding: 4rem 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            margin: 2rem auto;
        }

        .confirmation-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        .order-confirmation h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #212529;
        }

        .order-number {
            font-size: 1.25rem;
            color: #FF6B35;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .order-confirmation p {
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .confirmation-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            padding: 1rem 2rem;
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

        .btn-secondary {
            padding: 1rem 2rem;
            background: #6c757d;
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

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
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
            .checkout-grid {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .steps-container {
                flex-direction: column;
                gap: 1rem;
            }

            .steps-container::before {
                display: none;
            }

            .step {
                flex-direction: row;
                justify-content: flex-start;
                width: 100%;
            }

            .step-circle {
                margin-bottom: 0;
                margin-right: 1rem;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }

            .confirmation-actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../../includes/page-loader.php'; ?>
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <!-- Breadcrumb -->
    <section class="breadcrumb-section">
        <div class="container">
            <div class="breadcrumb">
                <a href="<?= pageUrl('home') ?>">Home</a>
                /
                <a href="<?= pageUrl('cart') ?>">Cart</a>
                /
                <span>Checkout</span>
            </div>
        </div>
    </section>

    <?php if ($orderPlaced): ?>
        <!-- Order Confirmation -->
        <main class="checkout-content">
            <div class="container">
                <div class="order-confirmation scroll-reveal">
                    <div class="confirmation-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2>Order Placed Successfully!</h2>
                    <div class="order-number">Order #<?= htmlspecialchars($_GET['ref'] ?? 'N/A') ?></div>
                    <p>
                        Thank you for your order! We've received your purchase and will begin processing it right away.
                        <br><br>
                        A confirmation email has been sent to your email address with order details and tracking information.
                    </p>
                    <div class="confirmation-actions">
                        <a href="<?= pageUrl('products') ?>" class="btn-primary">
                            <i class="fas fa-shopping-bag"></i> Continue Shopping
                        </a>
                        <a href="/tematech-innovation/users/index.php?page=orders" class="btn-secondary">
                            <i class="fas fa-receipt"></i> View Orders
                        </a>
                    </div>
                </div>
            </div>
        </main>
    <?php else: ?>
        <!-- Checkout Steps -->
        <section class="checkout-steps scroll-reveal">
            <div class="container">
                <div class="steps-container">
                    <div class="step completed">
                        <div class="step-circle">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <span class="step-label">Cart</span>
                    </div>
                    <div class="step active">
                        <div class="step-circle">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <span class="step-label">Checkout</span>
                    </div>
                    <div class="step <?= $orderPlaced ? 'completed' : '' ?>">
                        <div class="step-circle">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="step-label">Complete</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <main class="checkout-content">
            <div class="container">
                <form method="POST" id="checkoutForm">
                    <div class="checkout-grid">
                        <!-- Checkout Form -->
                        <div class="checkout-form-section">
                            <!-- Billing Information -->
                            <div class="form-section scroll-slide-left">
                                <h2><i class="fas fa-user"></i> Billing Information</h2>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="firstName">First Name *</label>
                                        <input type="text" id="firstName" name="firstName" required
                                            value="<?= htmlspecialchars($customer['first_name'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="lastName">Last Name *</label>
                                        <input type="text" id="lastName" name="lastName" required
                                            value="<?= htmlspecialchars($customer['last_name'] ?? '') ?>" readonly>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="email" id="email" name="email" required
                                        value="<?= htmlspecialchars($customer['email'] ?? '') ?> " readonly>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number *</label>
                                    <input type="tel" id="phone" name="phone" required
                                        value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="address">Street Address *</label>
                                    <input type="text" id="address" name="address" required>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="city">City *</label>
                                        <input type="text" id="city" name="city" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="state">State *</label>
                                        <input type="text" id="state" name="state" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="zipCode">Zip Code *</label>
                                        <input type="text" id="zipCode" name="zipCode" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="country">Country *</label>
                                        <select id="country" name="country" required>
                                            <option value="">Select Country</option>
                                            <option value="ZA" selected>Zambia</option>
                                            <option value="CA">Canada</option>
                                            <option value="UK">United Kingdom</option>
                                            <option value="AU">Australia</option>
                                        </select>
                                    </div>
                                </div>
                            </div>




                        </div>

                        <!-- Order Summary -->
                        <div class="order-summary scroll-slide-right">
                            <h2>Order Summary</h2>
                            <div class="order-items">
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="order-item">
                                        <div class="order-item-image">
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="/tematech-innovation/public/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:60px;height:60px;object-fit:contain;">
                                            <?php else: ?>
                                                <i class="fas fa-box"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="order-item-details">
                                            <div class="order-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                            <div class="order-item-qty">Qty: <?= $item['quantity'] ?></div>
                                        </div>
                                        <div class="order-item-price">
                                            K<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span class="value">K<?= number_format($subtotal, 2) ?></span>
                            </div>

                            <div class="summary-row total">
                                <span>Total:</span>
                                <span class="value">K<?= number_format($total, 2) ?></span>
                            </div>

                            <div class="notice">
                                <i class="fas fa-info-circle"></i>
                                Taxes are included in the total price and will be calculated at checkout. Shipping costs are calculated separately based on your location.
                            </div>

                            <button type="submit" class="btn-place-order">
                                <i class="fas fa-lock"></i> Place Order
                            </button>

                            <div class="secure-note">
                                <i class="fas fa-shield-alt"></i>
                                Your payment is secure and encrypted
                            </div>
                            <div class="accepted-payments">
                                <p>Accepted Payments:</p>
                                <img src="<?= $paymentIcons['airtel'] ?>" alt="Airtel Money" class="airtel">
                                <img src="<?= $paymentIcons['mtn'] ?>" alt="MTN Money" class="mtn">
                                <img src="<?= $paymentIcons['zamtel'] ?>" alt="Zamtel Money" class="zamtel">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    <?php endif; ?>

    <!-- Footer -->
    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <!-- Scripts -->
    <script>
        // Scroll animations
        const observerOptions = {
            threshold: 0.15,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, observerOptions);

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.scroll-reveal, .scroll-slide-left, .scroll-slide-right').forEach(el => {
                observer.observe(el);
            });

        });
    </script>
    <!-- Lenco Payment Script -->
    <script src="https://pay.lenco.co/js/v1/inline.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('checkoutForm');
            const btn = document.querySelector('.btn-place-order');

            // FIX: stop if form doesn't exist
            if (!form || !btn) return;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                btn.disabled = true;
                btn.textContent = 'Processing...';

                const formData = {
                    firstName: form.firstName.value.trim(),
                    lastName: form.lastName.value.trim(),
                    email: form.email.value.trim(),
                    phone: form.phone.value.trim(),
                    address: form.address.value.trim(),
                    city: form.city.value.trim(),
                    state: form.state.value.trim(),
                    zipCode: form.zipCode.value.trim(),
                    country: form.country.value,
                    cart: JSON.parse(localStorage.getItem('cart') || '[]')
                };

                try {
                    // Step 1: Create pending order
                    const res = await fetch('/tematech-innovation/api/get-checkout.php', {
                        method: 'POST',
                        headers: {

                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await res.json();

                    if (!data.success) {
                        alert(data.message);
                        btn.disabled = false;
                        btn.textContent = 'Place Order';
                        return;
                    }

                    // Step 2: Initiate Lenco popup
                    startLencoPayment({
                        reference: data.order_reference,
                        amount: data.amount,
                        email: formData.email,
                        customer: formData,
                        lencoKey: data.lenco_public_key
                    });

                } catch (err) {
                    console.error(err);
                    alert('Something went wrong. Try again.');
                    btn.disabled = false;
                    btn.textContent = 'Place Order';
                }
            });
        });

        /**
         * Lenco Payment (Robust + Debug Enabled)
         */
        function startLencoPayment({
            reference,
            amount,
            email,
            customer,
            lencoKey
        }) {
            // Safety check
            if (!window.LencoPay || typeof LencoPay.getPaid !== 'function') {
                alert('Payment service unavailable.');
                return;
            }

            if (!reference || !amount || !email) {
                alert('Invalid payment data.');
                console.error('Missing payment fields:', {
                    reference,
                    amount,
                    email
                });
                return;
            }

            // IMPORTANT: Lenco expects ZMW (not ngwee)
            const finalAmount = Number(amount);

            console.log('Starting Lenco payment:', {
                reference,
                amount: finalAmount,
                email,
                customer
            });

            LencoPay.getPaid({
                key: lencoKey,
                reference: reference,
                email: email,
                amount: finalAmount,
                currency: 'ZMW',
                channels: ['card', 'mobile-money'],

                customer: {
                    firstName: customer.firstName || '',
                    lastName: customer.lastName || '',
                    phone: customer.phone || ''
                },

                /**
                 * SUCCESS → VERIFY ON SERVER
                 */
                onSuccess: async function(response) {
                    console.log('Lenco success response:', response);

                    // VERY IMPORTANT: Handle inconsistent response keys
                    const paymentReference =
                        response.reference ||
                        response.transaction_reference ||
                        response.tx_ref ||
                        null;

                    if (!paymentReference) {
                        alert('Missing payment reference from Lenco.');
                        console.error('Invalid Lenco response:', response);
                        return;
                    }

                    try {
                        const verifyRes = await fetch('/tematech-innovation/api/verify-payment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                reference: paymentReference
                            })
                        });

                        const verifyData = await verifyRes.json();

                        console.log('Verification response:', verifyData);

                        if (verifyData.success) {
                            // Clear cart
                            localStorage.removeItem('cart');

                            // Redirect to success UI
                            window.location.href =
                                `index.php?page=checkout&payment=success&ref=${paymentReference}`;

                        } else {
                            // FULL DEBUG OUTPUT
                            alert(
                                'Verification failed:\n\n' +
                                'Message: ' + (verifyData.message || 'N/A') + '\n\n' +
                                'HTTP Code: ' + (verifyData.http_code || 'N/A') + '\n\n' +
                                'Raw Response:\n' + JSON.stringify(
                                    verifyData.raw_response || verifyData.raw || verifyData,
                                    null,
                                    2
                                )
                            );

                            console.error('Verification failed:', verifyData);
                        }

                    } catch (err) {
                        console.error('Verification fetch error:', err);
                        alert('Payment verification request failed. Check console.');
                    }
                },

                /**
                 * PAYMENT ERROR
                 */
                onError: function(err) {
                    console.error('Lenco payment error:', err);

                    let message = err.message || 'Unknown error';

                    // Handle known Lenco cases
                    if (err.status === 'otp-required') {
                        message = 'Enter OTP sent to your phone.';
                    } else if (err.status === '3ds-auth-required' && err.meta?.authorization) {
                        window.location.href = err.meta.authorization.redirect;
                        return;
                    }

                    alert('Payment failed: ' + message);
                },

                /**
                 * USER CLOSED MODAL
                 */
                onClose: function() {
                    console.warn('Payment modal closed');
                    alert('Payment was not completed.');
                },

                /**
                 * PENDING (Mobile Money)
                 */
                onConfirmationPending: function() {
                    console.warn('Payment pending confirmation');
                    alert('Payment pending. Please confirm on your phone.');
                }
            });
        }
    </script>
</body>

</html>