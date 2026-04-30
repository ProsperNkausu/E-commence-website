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

/* =========================
   FEATURED CATEGORIES
   show only a few (4)
========================= */
$stmt = $pdo->query("
SELECT id, name, icon
FROM categories
ORDER BY id DESC
LIMIT 4
");
$featuredCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);



/* =========================
   FEATURED PRODUCTS
   most recently uploaded
========================= */
$stmt = $pdo->query("
SELECT 
    p.id,
    p.name,
    p.price,
    p.description,
    p.created_at,

    c.name AS category,

    img.image_url AS image

FROM products p

LEFT JOIN product_categories pc 
    ON pc.product_id = p.id

LEFT JOIN categories c 
    ON c.id = pc.category_id

LEFT JOIN product_images img 
    ON img.product_id = p.id 
    AND img.is_primary = 1

ORDER BY p.created_at DESC
LIMIT 5
");

$featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Tema Tech</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include __DIR__ . '/../../includes/navbar-styles.php'; ?>


</head>

<body data-user-id="<?= $_SESSION['customer_id'] ?? '' ?>" data-sync-cart="<?= $syncCart ? '1' : '0' ?>">
    <!-- Include Navbar -->
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>
    <?php include __DIR__ . '/../../includes/page-loader.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="hero-section scroll-reveal">
                <h1>Welcome to <span class="span-tech">TemaTech Innovation</span></h1>
                <p>Discover the latest technology and innovative products</p>
                <a href="<?= pageUrl('products') ?>" class="btn-primary">Shop Now</a>
            </div>

            <!-- Featured Categories -->
            <!-- Featured Categories -->
            <section class="categories-section scroll-reveal">
                <h2>Discover our Featured Categories</h2>
                <div class="categories-grid">

                    <?php foreach ($featuredCategories as $cat): ?>
                        <?php
                        // Determine the correct image path
                        $iconPath = $cat['icon'] ?? '';

                        // If icon starts with 'category/' (from your DB), use public/category/
                        if (!empty($iconPath) && strpos($iconPath, 'category/') === 0) {
                            $imageSrc = '/tematech-innovation/public/' . htmlspecialchars($iconPath);
                        }
                        // Fallback for old font-awesome icons or missing images
                        else {
                            $imageSrc = ''; // We'll handle this with placeholder
                        }
                        ?>

                        <a href="<?= pageUrl('products', ['category' => $cat['name']]) ?>" class="category-card">

                            <div class="category-image-container">
                                <?php if ($imageSrc): ?>
                                    <img
                                        src="<?= $imageSrc ?>"
                                        alt="<?= htmlspecialchars($cat['name']) ?>"
                                        class="category-image"
                                        onerror="this.src='/tematech-innovation/public/images/placeholder-category.jpg'; this.style.display='none';">
                                <?php else: ?>
                                    <!-- Fallback icon if no image -->
                                    <div class="category-icon-fallback">
                                        <i class="fas fa-box"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <h3 class="category-name">
                                <?= htmlspecialchars($cat['name']) ?>
                            </h3>

                            <p class="category-description">
                                Explore <?= htmlspecialchars($cat['name']) ?>
                            </p>

                        </a>

                    <?php endforeach; ?>

                </div>
            </section>

            <!-- Featured Products -->
            <section class="featured-products scroll-reveal">
                <h2>Featured Products</h2>
                <div class="products-grid">
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="product-card">
                            <img
                                src="/tematech-innovation/public/<?php echo htmlspecialchars($product['image'] ?? 'images/placeholder-product.jpg'); ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                class="product-image"
                                onerror="this.src='/tematech-innovation/public/images/placeholder-product.jpg';">
                            <div class="product-info">
                                <div class="product-category">Category: <?php echo htmlspecialchars($product['category'] ?? 'Uncategorized'); ?></div>
                                <h3 class="product-name" onclick="window.location.href='index.php?page=product-details&id=<?php echo $product['id']; ?>'"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="product-price"><span class="current-price">K<?php echo number_format($product['price'], 2); ?></span></div>
                                <button
                                    class="add-to-cart-btn"
                                    onclick="event.stopPropagation(); addToCart(
        '<?= $product['id']; ?>',
        '<?= htmlspecialchars($product['name'], ENT_QUOTES); ?>',
        <?= (float)$product['price']; ?>,
        '/tematech-innovation/public/<?= htmlspecialchars($product['image'] ?? 'images/placeholder-product.jpg', ENT_QUOTES); ?>',
        '<?= htmlspecialchars($product['category'] ?? '', ENT_QUOTES); ?>'
    )">
                                    <i class="fas fa-shopping-cart"></i>
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Why Choose Us -->
            <section class="why-choose-us scroll-reveal">
                <h2>Why Choose Us</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h3 class="feature-title">Free Shipping</h3>
                        <p class="feature-description">Free delivery on orders over K10,000. Fast and reliable shipping worldwide.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="feature-title">Secure Payment</h3>
                        <p class="feature-description">Your payment information is safe with our encrypted checkout process.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3 class="feature-title">24/7 Support</h3>
                        <p class="feature-description">Our customer support team is always here to help you with any questions.</p>
                    </div>


                </div>
            </section>

            <!-- CTA Section -->
            <section class="cta-section scroll-reveal">
                <h2>Ready to Upgrade Your Tech?</h2>
                <p>Browse our collection and find the perfect device for your needs</p>
                <a href="<?= pageUrl('products') ?>" class="btn-cta">Shop All Products</a>
            </section>
        </div>
    </main>
    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>
    <!-- Include Footer -->
    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <script>
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;

            toast.classList.add('show', 'success');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function addToCart(id, name, price, image) {
            cart.addItem(id, 1, {
                id,
                name,
                price,
                image
            });

            document.dispatchEvent(new Event('cartUpdated'));

            showToast(name + ' added to cart');
        }
    </script>

    <!-- Cart Management Script -->
    <script src="/tematech-innovation/includes/cart-manager.js"></script>

    <!-- Scroll Animation Script -->
    <script>
        // alert add to cart and scroll animations
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;

            toast.classList.add('show', 'success');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function addToCart(id, name, price, image) {
            cart.addItem(id, 1, {
                id,
                name,
                price,
                image
            });

            document.dispatchEvent(new Event('cartUpdated'));

            showToast(name + ' added to cart');
        }
        // Intersection Observer for scroll animations
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

        // Observe all elements with animation classes
        document.addEventListener('DOMContentLoaded', function() {
            const scrollElements = document.querySelectorAll(
                '.scroll-reveal, .scroll-fade-in, .scroll-slide-left, .scroll-slide-right, .stagger-item'
            );

            scrollElements.forEach(el => observer.observe(el));

            // Add stagger delay to grid items
            document.querySelectorAll('.categories-grid .category-card').forEach((card, index) => {
                card.classList.add('stagger-item');
                card.style.transitionDelay = `${index * 0.1}s`;
                observer.observe(card);
            });

            document.querySelectorAll('.products-grid .product-card').forEach((card, index) => {
                card.classList.add('stagger-item');
                card.style.transitionDelay = `${index * 0.1}s`;
                observer.observe(card);
            });

            document.querySelectorAll('.features-grid .feature-card').forEach((card, index) => {
                card.classList.add('stagger-item');
                card.style.transitionDelay = `${index * 0.1}s`;
                observer.observe(card);
            });
        });



        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>

<!-- Custom Styles -->
<style>
    * {
        scroll-behavior: smooth;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        line-height: 1.6;
        color: #333;
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

    .scroll-fade-in {
        opacity: 0;
        transition: opacity 1s ease-out;
    }

    .scroll-fade-in.revealed {
        opacity: 1;
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

    .stagger-item {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }

    .stagger-item.revealed {
        opacity: 1;
        transform: translateY(0);
    }

    /* Main Content */
    .main-content {
        padding: 2rem 0;
    }

    .hero-section {
        background: linear-gradient(rgba(0, 0, 0, 0.66), rgba(0, 0, 0, 0.65)),
            url('/tematech-innovation/public/images/hero-img-new.png') center/cover no-repeat;
        background-attachment: fixed;
        /* optional for parallax feel */
        color: white;
        padding: 5rem 0;
        text-align: center;
        border-radius: 1rem;
        margin-bottom: 2rem;
        position: relative;
    }

    .span-tech {
        color: #FF6B35;
    }

    .hero-section h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .hero-section p {
        font-size: 1.25rem;
        margin-bottom: 2rem;
    }

    .btn-primary {
        background: #FF6B35;
        color: white;
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary:hover {
        background: #f3f4f6;
        color: #FF6B35;
        transition: all 0.3s;
    }

    .add-to-cart-btn {
        width: 100%;
        padding: 10px;
        background: #FF6B35;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    /* Featured Categories */
    .categories-section {
        margin-bottom: 3rem;
    }

    .categories-section h2 {
        text-align: center;
        font-size: 2rem;
        margin-bottom: 2rem;
        color: #333;
    }

    /* toasted  */
    .toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #111827;
        color: #fff;
        padding: 14px 18px;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        font-size: 14px;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
        z-index: 9999;
    }

    .toast.show {
        opacity: 1;
        transform: translateY(0);
    }

    .toast.success {
        border-left: 4px solid #FF6B35;
    }

    /* Featured Categories - Updated for Images */
    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .category-card {
        /* background: white; */
        border-radius: 1rem;
        padding: 1.5rem;
        text-align: center;
        /* box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); */
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        text-decoration: none;
        color: #333;
        overflow: hidden;
    }

    .category-card:hover {
        transform: translateY(-8px);
        /* box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12); */
    }

    .category-image-container {
        width: 100%;
        height: 180px;
        margin-bottom: 1.25rem;
        border-radius: 12px;
        overflow: hidden;
        /* background: #f8f9fa; */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .category-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        /* Change to 'contain' if you prefer no cropping */
        transition: transform 0.4s ease;
    }

    .category-icon-fallback {
        font-size: 4rem;
        color: #FF6B35;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
    }

    /* Remove or comment out the old category-icon styles */
    .category-icon {
        /* font-size: 3rem; */
        /* color: #FF6B35; */
        /* margin-bottom: 1rem; */
        display: none;
        /* Hide old icon container */
    }

    .category-name {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .category-description {
        font-size: 0.875rem;
        color: #6b7280;
    }

    @media (max-width: 768px) {
        .categories-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .category-card {
            padding: 1.5rem;
        }

        .category-icon {
            font-size: 2.5rem;
        }

        .category-name {
            font-size: 1rem;
        }
    }

    /* Featured Products */
    .featured-products {
        margin-bottom: 3rem;
    }

    .featured-products h2 {
        text-align: center;
        font-size: 2rem;
        margin-bottom: 2rem;
        color: #333;
    }

    .products-grid {
        display: flex;
        overflow-x: auto;
        gap: 1.5rem;
        padding: 1rem 0;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }

    .products-grid::-webkit-scrollbar {
        height: 8px;
    }

    .products-grid::-webkit-scrollbar-track {
        /* background: #F3F4F6; */
        border-radius: 10px;
    }

    .products-grid::-webkit-scrollbar-thumb {
        background: #FF6B35;
        border-radius: 10px;
    }

    .products-grid::-webkit-scrollbar-thumb:hover {
        background: #E85A2A;
    }

    .product-card {
        flex: 0 0 280px;
        min-width: 280px;
        /* background: #fff; */
        /* border: 1px solid #e5e7eb; */
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s;
        overflow: hidden;


    }

    .product-card:hover {
        transform: translateY(-5px);
        /* box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1); */
        border-color: #FF6B35;

    }

    .product-image {
        width: 100%;
        height: 220px;
        object-fit: contain;
    }

    .product-info {
        padding: 1.5rem;
    }

    .product-name {
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-name:hover {
        color: #FF6B35;
        cursor: pointer;
    }

    .product-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: #FF6B35;
        margin-bottom: 0.75rem;
    }

    .product-description {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 1rem;
    }

    .btn-add-cart {
        width: 100%;
        background: #FF6B35;
        color: white;
        padding: 0.75rem;
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-add-cart:hover {
        background: #E85A2A;
    }

    /* Why Choose Us */
    .why-choose-us {
        background: #F9FAFB;
        padding: 4rem 0;
        margin-bottom: 3rem;
        border-radius: 1rem;
    }

    .why-choose-us h2 {
        text-align: center;
        font-size: 2rem;
        margin-bottom: 3rem;
        color: #333;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
    }

    .feature-card {
        text-align: center;
        padding: 2rem;
    }

    .feature-icon {
        width: 80px;
        height: 80px;
        background: #FF6B35;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 1.5rem;
    }

    .feature-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #333;
    }

    .feature-description {
        font-size: 0.9375rem;
        color: #6b7280;
        line-height: 1.6;
    }

    /* CTA Section */
    .cta-section {
        background: linear-gradient(135deg, #FF6B35 0%, #E85A2A 100%);
        color: white;
        padding: 4rem 2rem;
        text-align: center;
        border-radius: 1rem;
        margin-bottom: 3rem;
    }

    .cta-section h2 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .cta-section p {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        opacity: 0.95;
    }

    .btn-cta {
        background: white;
        color: #FF6B35;
        padding: 1rem 2.5rem;
        border: none;
        border-radius: 0.5rem;
        font-size: 1.125rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s;
    }

    .btn-cta:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    @media (max-width: 768px) {
        .product-card {
            flex: 0 0 240px;
            min-width: 240px;
        }

        .cta-section h2 {
            font-size: 1.75rem;
        }

        .cta-section p {
            font-size: 1rem;
        }
    }
</style>

</html>