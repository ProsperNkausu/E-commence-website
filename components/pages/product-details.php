<?php
session_start();
require_once __DIR__ . '/../../config/db.php'; // DB connection

// ===========================
// Helper functions
// ===========================
if (!function_exists('pageUrl')) {
    function pageUrl(string $page, array $params = []): string
    {
        $url = 'index.php?page=' . urlencode($page);
        foreach ($params as $key => $value) {
            $url .= '&' . urlencode($key) . '=' . urlencode($value);
        }
        return $url;
    }
}

if (!function_exists('externalUrl')) {
    function externalUrl(string $path): string
    {
        return $path;
    }
}

// ===========================
// Get Product ID
// ===========================
$productId = $_GET['id'] ?? null;

// ===========================
// Fetch Product from DB
// ===========================
$product = null;
// Fetch Product with primary image
if ($productId) {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name, img.image_url AS image
        FROM products p
        LEFT JOIN product_categories pc ON p.id = pc.product_id
        LEFT JOIN categories c ON pc.category_id = c.id
        LEFT JOIN product_images img ON img.product_id = p.id AND img.is_primary = 1
        WHERE p.id = ?
        LIMIT 1
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ===========================
// Fetch Related Products (same category)
// ===========================
// ===========================
// Fetch Related Products (same category) + Primary Image
// ===========================
$relatedProducts = [];
if ($product) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT 
            p.id, 
            p.name, 
            p.price, 
            p.created_at,
            c.name AS category_name,
            pi.image_url
        FROM products p
        LEFT JOIN product_categories pc ON p.id = pc.product_id
        LEFT JOIN categories c ON pc.category_id = c.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE p.id != ? 
          AND c.name = ?
        ORDER BY p.created_at DESC
        LIMIT 4
    ");
    $stmt->execute([$product['id'], $product['category_name']]);
    $relatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ===========================
// Cart count from session
// ===========================
$cartCount = $_SESSION['cart_count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? htmlspecialchars($product['name']) : 'Product Not Found'; ?> - Tema Tech</title>
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            opacity: 0;
            animation: pageLoad 0.6s ease-out forwards;
        }

        @keyframes pageLoad {
            to {
                opacity: 1;
            }
        }

        /* Scroll Animations */
        .scroll-reveal,
        .scroll-slide-left,
        .scroll-slide-right {
            opacity: 0;
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .scroll-reveal {
            transform: translateY(30px);
        }

        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        .scroll-slide-left {
            transform: translateX(-50px);
        }

        .scroll-slide-left.revealed {
            opacity: 1;
            transform: translateX(0);
        }

        .scroll-slide-right {
            transform: translateX(50px);
        }

        .scroll-slide-right.revealed {
            opacity: 1;
            transform: translateX(0);
        }

        .stagger-item {
            opacity: 0;
            transform: scale(0.95);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .stagger-item.revealed {
            opacity: 1;
            transform: scale(1);
        }

        /* Breadcrumb */
        .breadcrumb-section {
            padding: 1rem 0;
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
        }

        .breadcrumb {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .breadcrumb a {
            color: #FF6B35;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Main Layout */
        .main-content {
            flex: 1;
            padding: 3rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* ==================== PRODUCT DETAIL GRID ==================== */
        .product-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3.5rem;
            margin-bottom: 5rem;
        }

        .product-image-container {
            position: relative;
            background-color: #f1f3f5;
            border-radius: 16px;
            height: 520px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .product-image-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 20px;
        }

        .stockbadge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Product Details */
        .product-details {
            display: flex;
            flex-direction: column;
        }

        .product-category {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .product-title {
            font-size: 2.1rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1.2rem;
            color: #212529;
        }

        .price-section {
            margin-bottom: 1.8rem;
            padding-bottom: 1.8rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .price {
            font-size: 2.25rem;
            font-weight: 700;
            color: #FF6B35;
        }

        .original-price {
            font-size: 1.3rem;
            color: #6c757d;
            text-decoration: line-through;
        }

        .product-description {
            color: #495057;
            line-height: 1.75;
            margin-bottom: 2rem;
        }

        /* Quantity & Cart */
        .quantity-cart-section {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 2rem;
            align-items: flex-end;
        }

        .quantity-selector label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #495057;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            overflow: hidden;
            width: 130px;
            background: #fff;
        }

        .quantity-btn {
            flex: 1;
            padding: 0.65rem;
            border: none;
            background-color: #f8f9fa;
            cursor: pointer;
            font-size: 1.4rem;
            transition: all 0.2s ease;
        }

        .quantity-btn:hover {
            background-color: #e9ecef;
        }

        .quantity-value {
            flex: 1;
            text-align: center;
            padding: 0.65rem;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .add-to-cart-btn {
            flex: 1;
            padding: 1.1rem 2rem;
            background-color: #FF6B35;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .add-to-cart-btn:hover {
            background-color: #E85A2A;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(255, 107, 53, 0.35);
        }

        /* ==================== RELATED PRODUCTS ==================== */
        .related-products {
            margin-top: 5rem;
            padding-top: 3rem;
            border-top: 2px solid #e9ecef;
        }

        .section-title {
            font-size: 1.85rem;
            font-weight: 700;
            margin-bottom: 2.2rem;
            color: #212529;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(245px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.07);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
        }

        .product-card img {
            width: 100%;
            height: 210px;
            object-fit: contain;
            background: #f8f9fa;
            padding: 15px;
        }

        .product-card-content {
            padding: 1.2rem;
        }

        .product-card-category {
            color: #6c757d;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
        }

        .product-card-title {
            font-size: 1.05rem;
            font-weight: 600;
            line-height: 1.3;
            margin-bottom: 0.6rem;
            color: #212529;
        }

        .product-card-title:hover {
            color: #FF6B35;
        }

        .product-card-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #FF6B35;
        }

        /* Error State */
        .error-state {
            text-align: center;
            padding: 5rem 1rem;
        }

        .error-state h2 {
            font-size: 2.2rem;
            color: #495057;
            margin-bottom: 1rem;
        }

        .error-state p {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 2.5rem;
        }

        .btn-primary {
            padding: 1.1rem 2.2rem;
            background-color: #FF6B35;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.05rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #E85A2A;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(255, 107, 53, 0.35);
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 992px) {
            .product-grid {
                gap: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: 1fr;
                gap: 2.5rem;
            }

            .product-image-container {
                height: 380px;
            }

            .quantity-cart-section {
                flex-direction: column;
                align-items: stretch;
            }

            .add-to-cart-btn {
                width: 100%;
            }

            .section-title {
                font-size: 1.7rem;
            }
        }

        @media (max-width: 480px) {
            .product-image-container {
                height: 320px;
            }

            .product-title {
                font-size: 1.8rem;
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
                <a href="<?php echo pageUrl('home'); ?>">Home</a> /
                <a href="<?php echo pageUrl('products'); ?>">Products</a> /
                <span><?php echo $product ? htmlspecialchars($product['name']) : 'Product Not Found'; ?></span>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <?php if ($product): ?>
                <!-- Product Details Grid -->
                <div class="product-grid">
                    <!-- Left: Image -->
                    <div class="product-image-container scroll-slide-left">
                        <img
                            src="/tematech-innovation/public/<?php echo htmlspecialchars($product['image'] ?? 'images/placeholder-product.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            class="product-image"
                            onerror="this.src='/tematech-innovation/public/images/placeholder-product.jpg';">

                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span class="stockbadge badge-success">In Stock</span>
                        <?php else: ?>
                            <span class="stockbadge badge-error">Out of Stock</span>
                        <?php endif; ?>
                    </div>

                    <!-- Right: Details -->
                    <div class="product-details scroll-slide-right">
                        <div class="product-category">
                            Category: <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                        </div>
                        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>

                        <div class="price-section">
                            <span class="price">K<?php echo number_format($product['price'], 2); ?></span>
                            <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                <span class="original-price">K<?php echo number_format($product['original_price'], 2); ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Quantity & Add to Cart -->
                        <div class="quantity-cart-section">
                            <div class="quantity-selector">
                                <label>Quantity</label>
                                <div class="quantity-controls">
                                    <button class="quantity-btn" type="button">−</button>
                                    <span class="quantity-value" id="quantity">1</span>
                                    <button class="quantity-btn" type="button">+</button>
                                </div>
                            </div>

                            <button class="add-to-cart-btn"
                                onclick="event.stopPropagation(); addToCart(
                        '<?= $product['id']; ?>',
                        '<?= htmlspecialchars($product['name'], ENT_QUOTES); ?>',
                        <?= (float)$product['price']; ?>,
                        '/tematech-innovation/public/<?= htmlspecialchars($product['image'] ?? 'images/placeholder-product.jpg', ENT_QUOTES); ?>',
                        '<?= htmlspecialchars($product['category'] ?? '', ENT_QUOTES); ?>',
                        quantity
                    )">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </div>

                        <div class="product-info">
                            <label>Description:</label><br>
                            <p class="product-description">
                                <?php echo nl2br(htmlspecialchars($product['description'] ?? '')); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Related Products - OUTSIDE the grid -->
                <?php if (!empty($relatedProducts)): ?>
                    <section class="related-products scroll-reveal">
                        <h2 class="section-title">Related Products</h2>
                        <div class="products-grid">
                            <?php foreach ($relatedProducts as $rp): ?>
                                <div class="product-card"
                                    onclick="window.location.href='<?php echo pageUrl('product-details', ['id' => $rp['id']]); ?>'">
                                    <img
                                        src="/tematech-innovation/public/<?php echo htmlspecialchars($rp['image_url'] ?? 'images/placeholder-product.jpg'); ?>"
                                        alt="<?php echo htmlspecialchars($rp['name']); ?>"
                                        class="product-image-related"
                                        onerror="this.src='/tematech-innovation/public/images/placeholder-product.jpg';">
                                    <div class="product-card-content">
                                        <div class="product-card-category">
                                            <?php echo htmlspecialchars($rp['category_name'] ?? 'Uncategorized'); ?>
                                        </div>
                                        <h3 class="product-card-title"><?php echo htmlspecialchars($rp['name']); ?></h3>
                                        <div class="product-card-price">
                                            K<?php echo number_format($rp['price'], 2); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

            <?php else: ?>
                <!-- Product Not Found -->
                <div class="error-state">
                    <h2>Product Not Found</h2>
                    <p>Sorry, the product you're looking for doesn't exist or has been removed.</p>
                    <a href="<?php echo pageUrl('products'); ?>" class="btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
    <script src="/tematech-innovation/includes/cart-manager.js"></script>

    <script>
        let quantity = 1;

        function increaseQuantity() {
            quantity++;
            document.getElementById('quantity').textContent = quantity;
        }

        function decreaseQuantity() {
            if (quantity > 1) {
                quantity--;
                document.getElementById('quantity').textContent = quantity;
            }
        }

        // Attach event listeners to quantity buttons
        document.addEventListener('DOMContentLoaded', function() {
            const minusBtn = document.querySelector('.quantity-controls .quantity-btn:first-child');
            const plusBtn = document.querySelector('.quantity-controls .quantity-btn:last-child');
            if (minusBtn) minusBtn.addEventListener('click', decreaseQuantity);
            if (plusBtn) plusBtn.addEventListener('click', increaseQuantity);
        });


        // Scroll animations
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) entry.target.classList.add('revealed');
                });
            }, {
                threshold: 0.15,
                rootMargin: '0px 0px -50px 0px'
            });
            document.querySelectorAll('.scroll-reveal, .scroll-slide-left, .scroll-slide-right').forEach(el => observer.observe(el));
            document.querySelectorAll('.products-grid .product-card').forEach((card, idx) => {
                card.classList.add('stagger-item');
                card.style.transitionDelay = `${idx*0.1}s`;
                observer.observe(card);
            });
        });
    </script>
</body>

</html>