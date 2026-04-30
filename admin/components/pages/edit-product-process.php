<?php
// edit-product-process.php
session_start();
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$product_id = $_POST['product_id'] ?? '';
$name        = trim($_POST['name'] ?? '');
$sku         = trim($_POST['sku'] ?? '');
$price       = (float)($_POST['price'] ?? 0);
$stock       = (int)($_POST['stock_quantity'] ?? 0);
$category_id = $_POST['category_id'] ?? '';
$status      = $_POST['status'] ?? 'active';
$description = trim($_POST['description'] ?? '');

if (empty($product_id) || empty($name) || empty($sku) || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Update main product data
    $stmt = $pdo->prepare("
        UPDATE products 
        SET name = ?, sku = ?, price = ?, stock_quantity = ?, 
            status = ?, description = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$name, $sku, $price, $stock, $status, $description, $product_id]);

    // Update category (delete old and insert new)
    $pdo->prepare("DELETE FROM product_categories WHERE product_id = ?")->execute([$product_id]);

    if (!empty($category_id)) {
        $pdo->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)")
            ->execute([$product_id, $category_id]);
    }

    // Handle new image upload (if provided)
    if (!empty($_FILES['product_image']['name'])) {
        $upload_dir = __DIR__ . '/../../../public/images/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $file_ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_ext;
        $target_file = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
            // Delete old primary image
            $pdo->prepare("DELETE FROM product_images WHERE product_id = ? AND is_primary = 1")
                ->execute([$product_id]);

            // Insert new image
            $pdo->prepare("INSERT INTO product_images (id, product_id, image_url, is_primary) 
                           VALUES (UUID(), ?, ?, 1)")
                ->execute([$product_id, 'images/' . $new_filename]);
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Product updated successfully!'
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Edit Product Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
