<?php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // For UUID if using ramsey/uuid

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Admin info from session
$adminName = ($_SESSION['admin_first_name'] ?? '') . ' ' . ($_SESSION['admin_last_name'] ?? '');
$adminRole = ucfirst($_SESSION['admin_role_name'] ?? '');

// Upload stage
$uploadStage = $_SESSION['upload_stage'] ?? 'initial'; // initial, images-upload, ready-to-activate
$csvProducts = $_SESSION['csv_products'] ?? [];

// Flash messages
$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;
$warningMessage = $_SESSION['warning_message'] ?? null;

// Clear them after reading
unset($_SESSION['success_message'], $_SESSION['error_message'], $_SESSION['warning_message']);

// Fetch categories
$categoriesStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/../../../admin/includes/header.php'; ?>

<div class="admin-container">
    <div class="upload-container">
        <?php if ($successMessage): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <?php if ($warningMessage): ?>
            <div class="alert warning">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($warningMessage) ?>
            </div>
        <?php endif; ?>
        <!-- Progress Indicator -->
        <div class="progress-indicator">
            <div class="progress-step <?= $uploadStage === 'initial' ? 'active' : ($uploadStage !== 'initial' ? 'completed' : ''); ?>">
                <div class="step-number">1</div>
                <div class="step-label">Upload</div>
            </div>
            <div class="progress-line <?= $uploadStage !== 'initial' ? 'active' : ''; ?>"></div>
            <div class="progress-step <?= $uploadStage === 'images-upload' ? 'active' : ($uploadStage === 'ready-to-activate' ? 'completed' : ''); ?>">
                <div class="step-number">2</div>
                <div class="step-label">Images</div>
            </div>
            <div class="progress-line <?= $uploadStage === 'ready-to-activate' ? 'active' : ''; ?>"></div>
            <div class="progress-step <?= $uploadStage === 'ready-to-activate' ? 'active' : ''; ?>">
                <div class="step-number">3</div>
                <div class="step-label">Activate</div>
            </div>
        </div>

        <!-- Step 1: Initial Upload -->
        <?php if ($uploadStage === 'initial'): ?>
            <div class="upload-section active">
                <div class="section-header">
                    <h2>Upload Products</h2>
                    <p>Choose how you want to add new products</p>
                </div>

                <!-- Single Product Upload -->
                <div class="upload-card">
                    <div class="upload-card-header">
                        <i class="fas fa-box"></i>
                        <h3>Single Product</h3>
                    </div>
                    <p class="upload-card-description">Add one product at a time with detailed information</p>

                    <form class="product-form" method="POST" enctype="multipart/form-data" action="actions/add_single_product.php">
                        <div class="form-group">
                            <label>Product Name *</label>
                            <input type="text" name="name" placeholder="e.g., MacBook Pro 16&quot;" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Category *</label>
                                <select name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>SKU *</label>
                                <input type="text" name="sku" placeholder="e.g., MBP-001" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Price *</label>
                                <input type="number" step="0.01" name="price" placeholder="0.00" required>
                            </div>
                            <div class="form-group">
                                <label>Stock Quantity *</label>
                                <input type="number" name="stock_quantity" placeholder="0" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" placeholder="Product description..." rows="4"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Product Image *</label>
                            <div class="file-upload">
                                <label class="file-placeholder">
                                    <input type="file" name="product_image" accept="image/*" required style="display:none;">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Click to upload or drag and drop</p>
                                    <span>PNG, JPG, GIF up to 5MB</span>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit" id="upload-single-btn">
                            <i class="fas fa-plus"></i> Add Product
                        </button>
                    </form>
                </div>

                <!-- CSV Upload -->
                <div class="upload-card">
                    <div class="upload-card-header">
                        <i class="fas fa-file-csv"></i>
                        <h3>Bulk Upload (CSV)</h3>
                    </div>
                    <p class="upload-card-description">Upload multiple products at once using a CSV file. Products will be inactive until images are added.</p>

                    <form class="csv-form" method="POST" enctype="multipart/form-data" action="actions/upload_csv.php">
                        <div class="form-group">
                            <label>CSV File *</label>
                            <div class="file-upload">
                                <label class="file-placeholder">
                                    <input type="file" name="csv_file" accept=".csv" required style="display: none;">
                                    <i class="fas fa-file-csv"></i>
                                    <p>Click to upload or drag and drop</p>
                                    <span>CSV file only, Max 10MB</span>
                                </label>
                            </div>
                        </div>

                        <div class="csv-info">
                            <p><strong>CSV Format:</strong> Product Name, Category Name, SKU, Price, Stock, Description</p>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-upload"></i> Upload CSV
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Step 2: Image Upload -->
        <?php if ($uploadStage === 'images-upload'): ?>
            <div class="upload-section active">
                <div class="section-header">
                    <h2>Add Product Images</h2>
                    <p>Upload images for the <?= count($csvProducts); ?> products from your CSV</p>
                </div>

                <form method="POST" enctype="multipart/form-data" action="actions/upload_images.php">
                    <div class="products-images-grid">
                        <?php foreach ($csvProducts as $index => $product): ?>
                            <div class="product-image-card">
                                <div class="product-image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                                <div class="product-image-info">
                                    <h4><?= htmlspecialchars($product['name']); ?></h4>
                                    <p class="product-sku"><?= htmlspecialchars($product['sku']); ?></p>
                                </div>
                                <label class="btn-upload-image">
                                    <i class="fas fa-cloud-upload-alt"></i> Upload Image
                                    <input type="file" name="images[<?= $index ?>]" accept="image/*" required>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="actions-section">
                        <button type="button" class="btn-back" onclick="goBackToUpload()">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="submit" class="btn-next">
                            <i class="fas fa-arrow-right"></i> Next: Activate Products
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Step 3: Activate Products -->
        <?php if ($uploadStage === 'ready-to-activate'): ?>
            <div class="upload-section active">
                <div class="section-header">
                    <h2>Activate Products</h2>
                    <p>Your products are ready to go live on the main site</p>
                </div>

                <form method="POST" action="actions/activate_products.php">
                    <div class="summary-card">
                        <div class="summary-header">
                            <i class="fas fa-check-circle"></i>
                            <h3>Upload Summary</h3>
                        </div>

                        <div class="summary-info">
                            <div class="summary-row">
                                <span class="label">Total Products</span>
                                <span class="value"><?= count($csvProducts); ?></span>
                            </div>
                            <div class="summary-row">
                                <span class="label">Images Added</span>
                                <span class="value">✓ Complete</span>
                            </div>
                            <div class="summary-row">
                                <span class="label">Status</span>
                                <span class="value" style="color: #FF6B35; font-weight: 700;">Ready for Activation</span>
                            </div>
                        </div>

                        <div class="summary-products">
                            <h4>Products to be Activated:</h4>
                            <div class="products-list">
                                <?php foreach ($csvProducts as $product): ?>
                                    <div class="product-item">
                                        <div class="product-item-info">
                                            <div class="product-item-name"><?= htmlspecialchars($product['name']); ?></div>
                                            <div class="product-item-sku">SKU: <?= htmlspecialchars($product['sku']); ?></div>
                                        </div>
                                        <div class="product-item-price">$<?= number_format($product['price'], 2); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="actions-section">
                        <button type="submit" class="btn-activate">
                            <i class="fas fa-rocket"></i> Activate & Go Live
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Alert for upload status
if (isset($_SESSION['upload_status'])) {
    if ($_SESSION['upload_status'] === 'success') {
        echo '<div class="alert success"><i class="fas fa-check-circle"></i>Items have been uploaded successfully.</div>';
    } elseif ($_SESSION['upload_status'] === 'error') {
        echo '<div class="alert error"><i class="fas fa-exclamation-circle"></i>Items could not be uploaded. Please try again.</div>';
    }
    unset($_SESSION['upload_status']);
}
?>

<style>
    /* Root Variables */
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

    /* Alerts */
    .alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 18px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .alert.success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .alert.error {
        background: #fef2f2;
        color: #7f1d1d;
        border: 1px solid #fca5a5;
    }

    .alert.warning {
        background: #fffbeb;
        color: #92400e;
        border: 1px solid #fcd34d;
    }

    .admin-container {
        max-width: 1000px;
    }

    .upload-container {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 32px;
    }

    /* Progress Indicator */
    .progress-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 40px;
        gap: 0;
    }

    .progress-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
        position: relative;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #334155;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        transition: all 0.3s;
    }

    .progress-step.active .step-number {
        background: #FF6B35;
        color: #fff;
    }

    .progress-step.completed .step-number {
        background: #10b981;
        color: #fff;
    }

    .step-label {
        font-size: 12px;
        color: #FFFFFF;
        font-weight: 600;
    }

    .progress-step.active .step-label {
        color: #FF6B35;
    }

    .progress-line {
        width: 80px;
        height: 2px;
        background: #e5e7eb;
        margin: 0 -40px;
        z-index: -1;
        transition: all 0.3s;
    }

    .progress-line.active {
        background: #FF6B35;
    }

    /* Upload Section */
    .upload-section {
        display: none;
    }

    .upload-section.active {
        display: block;
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .section-header {
        margin-bottom: 32px;
        text-align: center;
    }

    .section-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .section-header p {
        font-size: 15px;
        color: var(--text-secondary);
    }

    /* Upload Cards */
    .upload-card {
        background: var(--border-color);
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
    }

    .upload-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .upload-card-header i {
        font-size: 28px;
        color: #FF6B35;
    }

    .upload-card-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .upload-card-description {
        font-size: 14px;
        color: var(--text-secondary);
        margin-bottom: 24px;
    }

    /* Form Styling */
    .product-form,
    .csv-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .file-upload {
        position: relative;
        border: 2px dashed var(--text-primary);
        border-radius: 8px;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        cursor: pointer;
    }

    .file-upload:hover {
        border-color: #FF6B35;
        background: rgba(255, 107, 53, 0.05);

    }

    .file-upload input {
        display: none;
    }


    .file-placeholder i {
        font-size: 32px;
        color: #FF6B35;
        margin-bottom: 8px;
        cursor: pointer;
    }

    .file-placeholder p {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }

    .file-placeholder span {
        font-size: 12px;
        color: #6b7280;
    }

    .csv-info {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 16px;
        margin-top: 16px;
    }

    .csv-info p {
        font-size: 13px;
        color: var(--accent);
        margin-bottom: 8px;
    }

    .csv-info code {
        display: block;
        background: #eff6ff;
        padding: 8px 12px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 12px;
        color: #0c4a6e;
        overflow-x: auto;
    }

    /* Submit Button */
    .btn-submit {
        padding: 12px 24px;
        background: #FF6B35;
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-submit:hover {
        background: #e55a28;
        transform: translateY(-2px);
    }

    /* Products Images Grid */
    .products-images-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .product-image-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: all 0.2s;
    }

    .product-image-card:hover {
        border-color: #FF6B35;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.1);
    }

    .product-image-placeholder {
        width: 100%;
        height: 150px;
        background: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        color: #9ca3af;
    }

    .product-image-info {
        padding: 16px;
        flex: 1;
    }

    .product-image-info h4 {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .product-sku {
        font-size: 12px;
        color: #6b7280;
    }

    .product-name {
        font-size: 13px;
        color: #374151;
        margin-top: 4px;
    }

    .btn-upload-image {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 16px;
        background: #FF6B35;
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        margin: 12px 16px 16px;
        transition: all 0.2s;
    }

    .btn-upload-image:hover {
        background: #e55a28;
    }

    /* Summary Card */
    .summary-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 32px;
    }

    .summary-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
    }

    .summary-header i {
        font-size: 24px;
        color: #10b981;
    }

    .summary-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
    }

    .summary-info {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding-bottom: 24px;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 24px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .summary-row .label {
        font-size: 14px;
        color: #6b7280;
        font-weight: 600;
    }

    .summary-row .value {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
    }

    .summary-products h4 {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 12px;
    }

    .products-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        max-height: 300px;
        overflow-y: auto;
    }

    .product-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
    }

    .product-item-info {
        flex: 1;
    }

    .product-item-name {
        font-size: 13px;
        font-weight: 600;
        color: #111827;
    }

    .product-item-sku {
        font-size: 11px;
        color: #9ca3af;
    }

    .product-item-price {
        font-size: 14px;
        font-weight: 700;
        color: #FF6B35;
    }

    /* Actions Section */
    .actions-section {
        display: flex;
        gap: 12px;
        justify-content: center;
    }

    .btn-back,
    .btn-next,
    .btn-activate {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-back {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-back:hover {
        background: #e5e7eb;
    }

    .btn-next,
    .btn-activate {
        background: #FF6B35;
        color: #fff;
    }

    .btn-next:hover,
    .btn-activate:hover {
        background: #e55a28;
        transform: translateY(-2px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .upload-container {
            padding: 20px;
        }

        .progress-line {
            width: 40px;
            margin: 0 -20px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .products-images-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }

        .actions-section {
            flex-direction: column;
        }

        .btn-back,
        .btn-next,
        .btn-activate {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .upload-container {
            padding: 16px;
        }

        .section-header h2 {
            font-size: 20px;
        }

        .progress-indicator {
            gap: -10px;
            margin-bottom: 24px;
        }

        .progress-line {
            width: 20px;
            margin: 0 -10px;
        }

        .products-images-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    // error/success message auto-dismiss
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            el.style.opacity = "0";
            setTimeout(() => el.remove(), 500);
        });
    }, 4000);

    // Handle click and file name display
    document.querySelectorAll('.file-upload').forEach(wrapper => {
        const fileInput = wrapper.querySelector('input[type="file"]');
        const placeholder = wrapper.querySelector('.file-placeholder');

        // Click wrapper to trigger file input
        wrapper.addEventListener('click', () => fileInput.click());

        // Update placeholder when file is selected
        fileInput.addEventListener('change', () => {
            const fileName = fileInput.files[0]?.name || "No file selected";
            placeholder.querySelector('p').textContent = fileName;
        });

        // Drag & Drop support
        wrapper.addEventListener('dragover', e => {
            e.preventDefault();
            wrapper.classList.add('dragover');
        });

        wrapper.addEventListener('dragleave', e => {
            e.preventDefault();
            wrapper.classList.remove('dragover');
        });

        wrapper.addEventListener('drop', e => {
            e.preventDefault();
            wrapper.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                const fileName = fileInput.files[0].name;
                placeholder.querySelector('p').textContent = fileName;
            }
        });
    });
</script>

</body>

</html>