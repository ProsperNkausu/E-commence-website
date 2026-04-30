<?php
session_start();
require_once __DIR__ . '/../../../config/db.php';

header('Content-Type: application/json');

// ===========================
// AUTH CHECK
// ===========================
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// ===========================
// VALIDATE INPUT
// ===========================
$productId = $_POST['product_id'] ?? null;

if (!$productId || !is_numeric($productId)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product ID'
    ]);
    exit;
}

try {
    // ===========================
    // START TRANSACTION
    // ===========================
    $pdo->beginTransaction();

    // ===========================
    // CHECK IF PRODUCT EXISTS
    // ===========================
    $checkStmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $checkStmt->execute([$productId]);

    if (!$checkStmt->fetch()) {
        $pdo->rollBack();

        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        exit;
    }

    // ===========================
    // DELETE RELATED DATA FIRST
    // ===========================

    // Delete images
    $deleteImages = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
    $deleteImages->execute([$productId]);

    // Delete category relations
    $deleteCategories = $pdo->prepare("DELETE FROM product_categories WHERE product_id = ?");
    $deleteCategories->execute([$productId]);

    // ===========================
    // DELETE PRODUCT
    // ===========================
    $deleteProduct = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $deleteProduct->execute([$productId]);

    // ===========================
    // COMMIT
    // ===========================
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Product deleted successfully'
    ]);
} catch (Exception $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log error (important for production)
    error_log("Delete Product Error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Something went wrong while deleting the product'
    ]);
}
