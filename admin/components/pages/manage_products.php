<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/db.php';

// ===========================
// FILTERS & PAGINATION
// ===========================
$searchTerm     = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? 'all';
$statusFilter   = $_GET['status'] ?? 'all';
$currentPage    = max(1, intval($_GET['page_num'] ?? 1));
$itemsPerPage   = 10;

// ===========================
// BUILD WHERE CLAUSE
// ===========================
$where = ["1=1"];
$params = [];

if ($categoryFilter !== 'all') {
    $where[] = "c.name = ?";
    $params[] = $categoryFilter;
}

if ($statusFilter !== 'all') {
    $where[] = "p.status = ?";
    $params[] = $statusFilter;
}

if (!empty($searchTerm)) {
    $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
}

$whereSQL = "WHERE " . implode(" AND ", $where);

// ===========================
// COUNT TOTAL PRODUCTS
// ===========================
$countSql = "
    SELECT COUNT(DISTINCT p.id)
    FROM products p
    LEFT JOIN product_categories pc ON pc.product_id = p.id
    LEFT JOIN categories c ON c.id = pc.category_id
    $whereSQL
";

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalProducts = (int)$countStmt->fetchColumn();

$totalPages = max(1, ceil($totalProducts / $itemsPerPage));

// Prevent invalid page numbers
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $itemsPerPage;

// ===========================
// FETCH PRODUCTS WITH LIMIT
// ===========================
$sql = "
SELECT 
    p.id, p.name, p.sku, p.price, p.stock_quantity, p.status, p.created_at,
    c.name AS category,
    img.image_url AS image
FROM products p
LEFT JOIN product_categories pc ON pc.product_id = p.id
LEFT JOIN categories c ON c.id = pc.category_id
LEFT JOIN product_images img ON img.product_id = p.id AND img.is_primary = 1
$whereSQL
ORDER BY p.created_at DESC
LIMIT ? OFFSET ?
";

// IMPORTANT: use a NEW params array
$dataParams = $params;
$dataParams[] = $itemsPerPage;
$dataParams[] = $offset;

$dataStmt = $pdo->prepare($sql);
$dataStmt->execute($dataParams);

$products = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/../../../admin/includes/header.php'; ?>
<link rel="stylesheet" href="/tematech-innovation/admin/css/admin-dark.css">

<div class="admin-container">

    <!-- Controls Section -->
    <form method="GET" action="index.php" class="controls-section">
        <input type="hidden" name="page" value="products">

        <div class="filters">
            <input type="text" name="search" class="search-input"
                placeholder="Search products..."
                value="<?php echo htmlspecialchars($searchTerm); ?>">

            <select name="category" class="filter-dropdown">
                <option value="all">All Categories</option>
                <option value="Computers" <?= $categoryFilter === 'Computers' ? 'selected' : '' ?>>Computers</option>
                <option value="Laptops" <?= $categoryFilter === 'Laptops' ? 'selected' : '' ?>>Laptops</option>
            </select>

            <select name="status" class="filter-dropdown">
                <option value="all">All Status</option>
                <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>

            <button type="submit" class="btn-filter">
                <i class="fas fa-search"></i> Search
            </button>

            <!-- ✅ RESET BUTTON -->
            <?php if (!empty($searchTerm) || $categoryFilter !== 'all' || $statusFilter !== 'all'): ?>
                <a href="index.php?page=products" class="btn-reset">
                    <i class="fas fa-undo"></i> Reset
                </a>
            <?php endif; ?>
        </div>

        <button type="button" class="btn-add-product"
            onclick="window.location.href='index.php?page=upload-products'">
            <i class="fas fa-plus"></i> Add Product
        </button>
    </form>

    <!-- Products Table -->
    <div class="products-section">
        <table class="products-table">
            <!-- ... your table content remains the same ... -->
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="product-info">
                                <img src="/tematech-innovation/public/<?php echo htmlspecialchars($product['image'] ?? 'images/placeholder-product.jpg'); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    class="product-image"
                                    onerror="this.src='/tematech-innovation/public/images/placeholder-product.jpg';">
                                <div class="product-details">
                                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div class="product-id">SKU: <?php echo htmlspecialchars($product['sku']); ?></div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($product['category'] ?? 'Uncategorized'); ?></td>
                            <td>K<?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <?php
                                $stock = (int)($product['stock_quantity'] ?? 0);
                                $stockClass = ($stock == 0) ? 'out-of-stock' : ($stock < 10 ? 'low-stock' : 'in-stock');
                                ?>
                                <span class="stock-badge <?php echo $stockClass; ?>">
                                    <?php echo $stock; ?> units
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo htmlspecialchars($product['status']); ?>">
                                    <?php echo ucfirst($product['status']); ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button class="btn-action edit" onclick="editProduct('<?php echo $product['id']; ?>')" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-action delete"
                                    onclick="deleteProduct('<?php echo $product['id']; ?>', '<?php echo addslashes($product['name']); ?>')"
                                    title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:50px; color:#888;">
                            No products found matching your filters.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Improved Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php
                // Build clean base URL
                $baseParams = [
                    'page'     => 'products',
                    'search'   => $searchTerm,
                    'category' => $categoryFilter,
                    'status'   => $statusFilter
                ];
                $baseQuery = http_build_query($baseParams);
                ?>

                <?php if ($currentPage > 1): ?>
                    <a href="index.php?<?= $baseQuery ?>&page_num=<?= $currentPage - 1 ?>" class="page-link">← Previous</a>
                <?php endif; ?>

                <?php
                $start = max(1, $currentPage - 2);
                $end   = min($totalPages, $currentPage + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <?php if ($i == $currentPage): ?>
                        <span class="page-link active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="index.php?<?= $baseQuery ?>&page_num=<?= $i ?>" class="page-link"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="index.php?<?= $baseQuery ?>&page_num=<?= $currentPage + 1 ?>" class="page-link">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function applyFilters() {
        const search = document.getElementById('searchInput').value.trim();
        const category = document.getElementById('categoryFilter').value;
        const status = document.getElementById('statusFilter').value;

        let url = 'index.php?page=products';

        if (search) url += '&search=' + encodeURIComponent(search);
        if (category !== 'all') url += '&category=' + encodeURIComponent(category);
        if (status !== 'all') url += '&status=' + encodeURIComponent(status);

        window.location.href = url; // No page_num when filtering → resets to page 1
    }

    function editProduct(productId) {
        window.location.href = 'index.php?page=edit-product&id=' + encodeURIComponent(productId);
    }

    function deleteProduct(productId, productName) {
        if (!confirm(`Are you sure you want to permanently delete "${productName}"?\nThis action cannot be undone!`)) return;

        fetch('delete-product-process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'product_id=' + encodeURIComponent(productId)
            })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    location.reload();
                } else {
                    alert(result.message || 'Failed to delete');
                }
            })
            .catch(() => alert('Network error'));
    }

    // Enter key support
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') applyFilters();
    });
</script>

<style>
    /* Basic Pagination Styling */
    .pagination {
        margin-top: 25px;
        display: flex;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .page-link {
        padding: 10px 14px;
        border: 1px solid #ddd;
        background: var(--text-primary);
        color: #333;
        text-decoration: none;
        border-radius: 6px;
        font-size: 14px;
    }

    .page-link:hover {
        background: var(--accent);
    }

    .page-link.active {
        background: var(--accent);
        color: white;
        border-color: var(--accent);
        font-weight: bold;
    }

    .btn-reset {
        padding: 10px 14px;
        background: #444;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        margin-left: 5px;
    }

    .btn-reset:hover {
        background: #666;
    }

    /* ========================= */
    /* SEARCH INPUT FIX */
    /* ========================= */

    .search-input {
        background: var(--bg-secondary);
        color: #fff;
        /* 👈 always visible */
        border: 1px solid var(--border-color);
        padding: 10px 14px;
        border-radius: 6px;
        outline: none;
        transition: all 0.2s ease;
    }

    /* Placeholder color */
    .search-input::placeholder {
        color: #888;
    }

    /* Focus state (when typing) */
    .search-input:focus {
        background: var(--text-primary);
        /* slightly darker */
        color: black;
        /* 👈 force visible text */
        border-color: #FF6B35;
        box-shadow: 0 0 0 2px var(--accent);
    }

    /* Fix autofill (Chrome annoying bug) */
    .search-input:-webkit-autofill {
        -webkit-box-shadow: 0 0 0 1000px #111 inset !important;
        -webkit-text-fill-color: #fff !important;
    }
</style>