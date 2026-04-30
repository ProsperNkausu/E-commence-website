<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

/* -----------------------------
   Collect Form Data
----------------------------- */

$name = trim($_POST['name'] ?? '');
$category_id = trim($_POST['category_id'] ?? '');
$sku = trim($_POST['sku'] ?? '');
$price = trim($_POST['price'] ?? '');
$stock_quantity = trim($_POST['stock_quantity'] ?? '');
$description = trim($_POST['description'] ?? '');

$errors = [];

if (!$name) $errors[] = "Product name required";
if (!$category_id) $errors[] = "Category required";
if (!$sku) $errors[] = "SKU required";
if (!$price) $errors[] = "Price required";
if (!$stock_quantity) $errors[] = "Stock required";

if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] !== 0) {
    $errors[] = "Image required";
}

if ($errors) {
    $_SESSION['product_upload_errors'] = $errors;
    header("Location: ../index.php?page=upload-products");
    exit;
}

/* -----------------------------
   Upload Image
----------------------------- */

$image = $_FILES['product_image'];

$uploadDir = __DIR__ . '/../../public/images/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$extension = pathinfo($image['name'], PATHINFO_EXTENSION);
$imageName = Uuid::uuid4()->toString() . "." . $extension;

$targetPath = $uploadDir . $imageName;

move_uploaded_file($image['tmp_name'], $targetPath);

/* -----------------------------
   Generate Slug
----------------------------- */

$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

/* -----------------------------
   Insert Data
----------------------------- */

try {

    $pdo->beginTransaction();

    $productId = Uuid::uuid4()->toString();

    /* 1️⃣ Insert Product */

    $stmt = $pdo->prepare("
        INSERT INTO products
        (id, name, slug, description, sku, price, stock_quantity, status)
        VALUES
        (:id, :name, :slug, :description, :sku, :price, :stock, 'active')
    ");

    $stmt->execute([
        ':id' => $productId,
        ':name' => $name,
        ':slug' => $slug,
        ':description' => $description,
        ':sku' => $sku,
        ':price' => $price,
        ':stock' => $stock_quantity
    ]);

    /* 2️⃣ Insert Product Category */

    $stmt = $pdo->prepare("
        INSERT INTO product_categories
        (product_id, category_id)
        VALUES
        (:product_id, :category_id)
    ");

    $stmt->execute([
        ':product_id' => $productId,
        ':category_id' => $category_id
    ]);

    /* 3️⃣ Insert Product Image */

    $imageId = Uuid::uuid4()->toString();

    $stmt = $pdo->prepare("
        INSERT INTO product_images
        (id, product_id, image_url, is_primary)
        VALUES
        (:id, :product_id, :image_url, 1)
    ");

    $stmt->execute([
        ':id' => $imageId,
        ':product_id' => $productId,
        ':image_url' => 'images/' . $imageName
    ]);

    $pdo->commit();

    $_SESSION['success_message'] = "Product added successfully";
} catch (Exception $e) {

    $pdo->rollBack();

    $_SESSION['error_message'] = $e->getMessage();
}

header("Location: ../index.php?page=upload-products");
exit;
