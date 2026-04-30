<?php
session_start();
require_once __DIR__ . '/../../config/db.php'; // DB connection

// ===========================
// GET FILTERS FROM URL
// ===========================
$currentCategory = $_GET['category'] ?? 'all';
$searchTerm = $_GET['search'] ?? '';
$priceRange = $_GET['price'] ?? 'all';
$sortBy = $_GET['sort'] ?? 'newest';

// Pagination
$itemsPerPage = 6;
$currentPage = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;

// ===========================
// BUILD QUERY
// ===========================
$where = [];
$params = [];

// Join product_categories -> categories and product_images
$joinSQL = "
    LEFT JOIN product_categories pc ON products.id = pc.product_id
    LEFT JOIN categories c ON pc.category_id = c.id
    LEFT JOIN product_images img ON img.product_id = products.id AND img.is_primary = 1
";

// Category filter
if ($currentCategory !== 'all') {
    $where[] = "c.name = ?";
    $params[] = $currentCategory;
}

// Search filter
if (!empty($searchTerm)) {
    $where[] = "products.name LIKE ?";
    $params[] = "%$searchTerm%";
}

// Price filter
if ($priceRange !== 'all') {
    if ($priceRange === '0-100') $where[] = "products.price BETWEEN 0 AND 100";
    if ($priceRange === '100-500') $where[] = "products.price BETWEEN 100 AND 500";
    if ($priceRange === '500-1000') $where[] = "products.price BETWEEN 500 AND 1000";
    if ($priceRange === '1000+') $where[] = "products.price > 1000";
}

// Only products in stock
$where[] = "products.stock_quantity > 0";

// Build WHERE clause
$whereSQL = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Sorting
$orderBy = "ORDER BY products.created_at DESC";
if ($sortBy === 'price-low') $orderBy = "ORDER BY products.price ASC";
if ($sortBy === 'price-high') $orderBy = "ORDER BY products.price DESC";

// ===========================
// COUNT TOTAL PRODUCTS
// ===========================
$countQuery = "SELECT COUNT(DISTINCT products.id) FROM products $joinSQL $whereSQL";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalProducts = $stmt->fetchColumn();

// Pagination calculations
$totalPages = ceil($totalProducts / $itemsPerPage);
$startIndex = ($currentPage - 1) * $itemsPerPage;

// ===========================
// FETCH PRODUCTS WITH PRIMARY IMAGE
// ===========================
$productQuery = "
    SELECT DISTINCT products.*, c.name AS category_name, img.image_url AS image
    FROM products
    $joinSQL
    $whereSQL
    $orderBy
    LIMIT $itemsPerPage OFFSET $startIndex
";

$stmt = $pdo->prepare($productQuery);
$stmt->execute($params);
$paginatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fallback placeholder if no image
foreach ($paginatedProducts as &$prod) {
    if (empty($prod['image'])) {
        $prod['image'] = 'public/images/placeholder-product.jpg';
    }
}
unset($prod);

// For page header
$categoryDisplay = $currentCategory !== 'all' ? ucfirst($currentCategory) : 'All';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $categoryDisplay; ?> Products - Tema Tech</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include __DIR__ . '/../../includes/navbar-styles.php'; ?>
</head>

<body>
    <?php include __DIR__ . '/../../includes/page-loader.php'; ?>
    <?php include __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="main-content">
        <div id="toast" class="toast"></div>
        <!-- Breadcrumb -->
        <section class="breadcrumb-section">
            <div class="container">
                <div class="breadcrumb">
                    <a href="index.php?page=home">Home</a>
                    <span>/</span>
                    <a href="index.php?page=products">Products</a>
                    <?php if ($currentCategory !== 'all'): ?>
                        <span>/</span>
                        <span><?php echo htmlspecialchars($categoryDisplay); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Page Header -->
        <section class="page-header">
            <div class="container">
                <h1>
                    <?php echo $currentCategory !== 'all' ? htmlspecialchars($categoryDisplay) : 'All Products'; ?>
                </h1>
                <p>
                    <?php
                    echo $currentCategory !== 'all'
                        ? 'Explore our collection of ' . strtolower(htmlspecialchars($categoryDisplay)) . ' products'
                        : 'Explore our complete range of electronics and tech products';
                    ?>
                </p>
            </div>
        </section>

        <!-- Products Section -->
        <section class="products-section">
            <div class="container">
                <div class="products-layout">

                    <!-- Sidebar Filters -->
                    <aside class="sidebar">
                        <div class="filter-card">
                            <h3>Filters</h3>
                            <form method="GET" action="index.php" onsubmit="this.page_input.value = '1';">
                                <input type="hidden" name="page" value="products">
                                <input type="hidden" name="page_input" value="1">

                                <!-- Search -->
                                <div class="filter-group">
                                    <label>Search Products</label>
                                    <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                                </div>

                                <!-- Category -->
                                <div class="filter-group">
                                    <label>Category</label>
                                    <select name="category">
                                        <option value="all" <?php echo ($currentCategory === 'all') ? 'selected' : ''; ?>>All Categories</option>
                                        <?php
                                        $categories = $pdo->query("SELECT name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($categories as $cat):
                                            $catName = $cat['name'];
                                        ?>
                                            <option value="<?php echo htmlspecialchars($catName); ?>" <?php echo ($currentCategory === $catName) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($catName); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Price -->
                                <div class="filter-group">
                                    <label>Price Range</label>
                                    <select name="price">
                                        <?php
                                        $priceOptions = ['all' => 'All Prices', '0-100' => 'K0 - K100', '100-500' => 'K100 - K500', '500-1000' => 'K500 - K1000', '1000+' => 'K1000+'];
                                        foreach ($priceOptions as $value => $label):
                                        ?>
                                            <option value="<?php echo $value; ?>" <?php echo ($priceRange === $value) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortBy); ?>">

                                <button type="submit" class="add-to-cart-btn" style="margin-bottom: 12px;"><i class="fas fa-filter"></i> Apply Filters</button>
                                <a href="index.php?page=products" class="reset-btn" style="display: block; text-align: center; text-decoration: none;">Reset Filters</a>
                            </form>
                        </div>
                    </aside>

                    <!-- Products Main -->
                    <main class="products-main">
                        <div class="products-header">
                            <p class="products-count">Showing <?php echo count($paginatedProducts); ?> of <?php echo $totalProducts; ?> products</p>
                            <select class="sort-select" onchange="sortProducts(this.value)">
                                <option value="newest" <?php echo ($sortBy === 'newest') ? 'selected' : ''; ?>>Sort by: Newest</option>
                                <option value="price-low" <?php echo ($sortBy === 'price-low') ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price-high" <?php echo ($sortBy === 'price-high') ? 'selected' : ''; ?>>Price: High to Low</option>
                            </select>
                        </div>

                        <!-- Products Grid -->
                        <?php if (!empty($paginatedProducts)): ?>
                            <div class="products-grid">
                                <?php foreach ($paginatedProducts as $product): ?>
                                    <div class="product-card">
                                        <img
                                            src="/tematech-innovation/public/<?php echo htmlspecialchars($product['image'] ?? 'images/placeholder-product.jpg'); ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                            class="product-image"
                                            onerror="this.src='/tematech-innovation/public/images/placeholder-product.jpg';">
                                        <div class="product-info">
                                            <div class="product-category">Category: <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></div>
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

                            <!-- Pagination -->
                            <!-- Pagination -->
                            <?php if (!empty($paginatedProducts) && $totalPages > 1): ?>
                                <div class="pagination">
                                    <?php
                                    // Build base query string WITHOUT the 'page' parameter
                                    $baseQuery = http_build_query([
                                        'page'      => 'products',        // Important: keep this!
                                        'category'  => $currentCategory,
                                        'search'    => $searchTerm,
                                        'price'     => $priceRange,
                                        'sort'      => $sortBy
                                    ]);
                                    ?>

                                    <?php if ($currentPage > 1): ?>
                                        <a href="index.php?<?= $baseQuery ?>&page_num=<?= $currentPage - 1 ?>">
                                            <button><i class="fas fa-chevron-left"></i> Previous</button>
                                        </a>
                                    <?php else: ?>
                                        <button disabled><i class="fas fa-chevron-left"></i> Previous</button>
                                    <?php endif; ?>

                                    <?php
                                    $startPage = max(1, $currentPage - 2);
                                    $endPage   = min($totalPages, $currentPage + 2);

                                    for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                        <?php if ($i == $currentPage): ?>
                                            <button class="active"><?= $i ?></button>
                                        <?php else: ?>
                                            <a href="index.php?<?= $baseQuery ?>&page_num=<?= $i ?>">
                                                <button><?= $i ?></button>
                                            </a>
                                        <?php endif; ?>
                                    <?php endfor; ?>

                                    <?php if ($currentPage < $totalPages): ?>
                                        <a href="index.php?<?= $baseQuery ?>&page_num=<?= $currentPage + 1 ?>">
                                            <button>Next <i class="fas fa-chevron-right"></i></button>
                                        </a>
                                    <?php else: ?>
                                        <button disabled>Next <i class="fas fa-chevron-right"></i></button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <h3>No products found</h3>
                                <p>Try adjusting your filters or search terms</p>
                            </div>
                        <?php endif; ?>

                    </main>

                </div>
            </div>
        </section>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>

    <!-- Cart Management Script -->
    <script src="/tematech-innovation/includes/cart-manager.js"></script>
    <script>
        function sortProducts(sortValue) {
            const searchParams = new URLSearchParams(window.location.search);
            searchParams.delete('page');
            searchParams.set('sort', sortValue);
            const url = 'index.php?page=products' + (searchParams.toString() ? '&' + searchParams.toString() : '');
            window.location.href = url;
        }

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

        document.querySelectorAll('.filter-group select').forEach(s => s.addEventListener('change', function() {
            const form = this.closest('form');
            const pageInput = form.querySelector('input[name="page_input"]');
            if (pageInput) pageInput.value = '1';
            form.submit();
        }));
    </script>
</body>

</html>

<style>
    * {
        scroll-behavior: smooth;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        line-height: 1.6;
        color: #333;
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
        transform: scale(0.95);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }

    .stagger-item.revealed {
        opacity: 1;
        transform: scale(1);
    }

    .main-content {
        flex: 1;
    }

    /* toasted */
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

    /* Products Page Styles */
    /* Breadcrumb */
    .breadcrumb-section {
        padding: 16px 0;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #6b7280;
    }

    .breadcrumb a {
        color: #FF6B35;
        text-decoration: none;
    }

    .breadcrumb a:hover {
        text-decoration: underline;
    }

    .breadcrumb span {
        color: #333;
    }

    /* Page Header */
    .page-header {
        background: #f9fafb;
        padding: 48px 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .page-header h1 {
        font-size: 36px;
        font-weight: 700;
        margin-bottom: 12px;
        color: #111827;
    }

    .page-header p {
        font-size: 16px;
        color: #6b7280;
    }

    /* Products Section */
    .products-section {
        padding: 48px 0;
    }

    .products-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 32px;
    }

    /* Sidebar */
    .sidebar {
        height: fit-content;
    }

    .filter-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
    }

    .filter-card h3 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #111827;
    }

    .filter-group {
        margin-bottom: 24px;
    }

    .filter-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 14px;
        color: #374151;
    }

    .filter-group input[type="text"],
    .filter-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .filter-group input[type="text"]:focus,
    .filter-group select:focus {
        outline: none;
        border-color: #FF6B35;
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    }

    .reset-btn {
        width: 100%;
        padding: 10px 16px;
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .reset-btn:hover {
        background: #e5e7eb;
    }

    /* Products Main */
    .products-main {
        min-height: 500px;
    }

    .products-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .products-count {
        font-size: 14px;
        color: #6b7280;
    }

    .sort-select {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
    }

    /* Products Grid */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 24px;
        margin-bottom: 48px;
    }

    .product-card {
        /* background: #fff; */
        /* border: 1px solid #e5e7eb; */
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s;

    }

    .product-card:hover {
        transform: translateY(-4px);
        /* box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1); */
        border-color: #FF6B35;
    }

    .product-image {
        width: 100%;
        height: 250px;
        object-fit: contain;
    }

    .product-info {
        padding: 16px;
    }

    .product-category {
        font-size: 12px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .product-name {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-name:hover {
        color: #FF6B35;
        cursor: pointer;
    }

    .product-rating {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-bottom: 12px;
        font-size: 14px;
    }

    .stars {
        color: #fbbf24;
    }

    .reviews {
        color: #6b7280;
        font-size: 12px;
    }

    .product-price {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }

    .current-price {
        font-size: 20px;
        font-weight: 700;
        color: #FF6B35;
    }

    .original-price {
        font-size: 14px;
        color: #9ca3af;
        text-decoration: line-through;
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

    .add-to-cart-btn:hover {
        background: #e55a28;
        transform: translateY(-1px);
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 48px;
    }

    .pagination button {
        padding: 10px 16px;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #374151;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .pagination button:hover:not(:disabled) {
        background: #f3f4f6;
    }

    .pagination button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pagination button.active {
        background: #FF6B35;
        color: #fff;
        border-color: #FF6B35;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 16px;
        opacity: 0.3;
    }

    /* Loading State */
    .loading-state {
        text-align: center;
        padding: 80px 20px;
        color: #6b7280;
    }

    .loading-spinner {
        width: 48px;
        height: 48px;
        border: 4px solid #f3f4f6;
        border-top-color: #FF6B35;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 16px;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .products-layout {
            grid-template-columns: 1fr;
        }

        .sidebar {
            margin-bottom: 32px;
        }

        .page-header h1 {
            font-size: 28px;
        }

        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }

        .product-image {
            height: 200px;
        }

        .products-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .pagination {
            flex-wrap: wrap;
        }
    }

    @media (max-width: 480px) {
        .products-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

</html>