<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/db.php';

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$product_id = $_GET['id'] ?? '';

if (empty($product_id)) {
    echo "Invalid Product ID";
    exit;
}

// Fetch product data
$stmt = $pdo->prepare("
    SELECT p.*, 
           c.name AS category_name,
           img.image_url AS primary_image
    FROM products p
    LEFT JOIN product_categories pc ON pc.product_id = p.id
    LEFT JOIN categories c ON c.id = pc.category_id
    LEFT JOIN product_images img ON img.product_id = p.id AND img.is_primary = 1
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "Product not found";
    exit;
}

// Fetch all categories for dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="/tematech-innovation/admin/css/admin-dark.css">
<?php include __DIR__ . '/../../../admin/includes/header.php'; ?>

<div class="admin-container">
    <div class="page-header">
        <h2>Edit Product</h2>
        <a href="index.php?page=products" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <div class="edit-form">
        <form id="editProductForm" onsubmit="submitEditProduct(event)" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">

            <!-- Product Image -->
            <div class="form-group image-section">
                <label class="change-color">Current Product Image</label>
                <div class="current-image">
                    <img src="/tematech-innovation/public/<?= htmlspecialchars($product['primary_image'] ?? 'images/placeholder-product.jpg') ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        onerror="this.src='/tematech-innovation/public/images/placeholder-product.jpg';">
                </div>
                <label class="change-color">New Image (optional)</label>
                <input type="file" name="product_image" accept="image/*" class="file-input">
                <small>Leave empty to keep current image</small>
            </div>

            <!-- Basic Information -->
            <div class="form-grid">
                <div class="form-group">
                    <label class="change-color">Product Name <span class="required">*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="change-color">SKU <span class="required">*</span></label>
                    <input type="text" name="sku" value="<?= htmlspecialchars($product['sku']) ?>" required>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="change-color">Price (K) <span class="required">*</span></label>
                    <input type="number" name="price" value="<?= htmlspecialchars($product['price']) ?>" step="0.01" required>
                </div>

                <div class="form-group">
                    <label class="change-color">Stock Quantity <span class="required">*</span></label>
                    <input type="number" name="stock_quantity" value="<?= htmlspecialchars($product['stock_quantity']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="change-color">Category <span class="required">*</span></label>
                <select name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= ($cat['name'] === $product['category_name']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="change-color">Status</label>
                <select name="status">
                    <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <div class="form-group">
                <label class="change-color">Description</label>
                <textarea name="description" rows="6"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <a href="index.php?page=products" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>


<script>
    async function submitEditProduct(e) {
        e.preventDefault();

        const formData = new FormData(document.getElementById('editProductForm'));

        try {
            const response = await fetch('/tematech-innovation/admin/actions/edit-product-process.php', {
                method: 'POST',
                body: formData
            });

            const text = await response.text();

            let result;
            try {
                result = JSON.parse(text);
            } catch (err) {
                showAlert('error', 'Server Error', 'Unexpected response from server');
                return;
            }

            if (result.success) {
                showAlert('success', 'Success', result.message || 'Product updated successfully!');
                setTimeout(() => {
                    window.location.href = '/tematech-innovation/admin/index.php?page=products';
                }, 1500);
            } else {
                showAlert('error', 'Error', result.message || 'Failed to update product');
            }

        } catch (error) {
            showAlert('error', 'Network Error', 'Please try again.');
        }
    }

    function showAlert(type, title, message) {
        const alertBox = document.createElement('div');
        alertBox.className = `alert-popup alert-${type}`;
        alertBox.innerHTML = `
            <div class="alert-content">
                <div class="alert-header">
                    <span class="alert-icon">${type === 'success' ? '✓' : '✕'}</span>
                    <h3>${title}</h3>
                    <button class="alert-close">&times;</button>
                </div>
                <p class="alert-message">${message}</p>
            </div>
        `;
        document.body.appendChild(alertBox);

        alertBox.querySelector('.alert-close').addEventListener('click', () => alertBox.remove());
        setTimeout(() => alertBox.remove(), 5000);
    }
</script>