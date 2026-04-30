<?php
// Admin Dashboard Router
// Check if admin is logged in
session_start();


// Get the page parameter from URL, default to dashboard
$page = $_GET['page'] ?? 'dashboard';

// Define available admin pages
$availablePages = [
    'dashboard' => 'admin_dashboard.php',
    'products' => 'manage_products.php',
    'upload-products' => 'upload_products.php',
    'orders' => 'manage_orders.php',
    'payments' => 'manage_payments.php',
    'customers' => 'manage_customer.php',
    'statistics' => 'manage_stats.php',
    'admins' => 'manage_admins.php',
    'settings' => 'settings.php',
    'edit-product' => 'edit_product.php',
    'edit-product-process' => 'edit-product-process.php',
    'shipping' => 'manage_shipping.php',
];

// Set the page file
$pageFile = $availablePages[$page] ?? 'admin_dashboard.php';
$filePath = __DIR__ . '/components/pages/' . $pageFile;

// Check if file exists, otherwise load dashboard
if (!file_exists($filePath)) {
    $filePath = __DIR__ . '/components/pages/admin_dashboard.php';
}

// Include the page
if (file_exists($filePath)) {
    include $filePath;
} else {
    echo '<h1>Page not found</h1>';
}
