<?php
// User Dashboard Router
// Check if user is logged in
session_start();

// // If user is not logged in, redirect to login page
// if (!isset($_SESSION['user_id']) && !isset($_SESSION['email'])) {
//     header('Location: ../index.php?page=login');
//     exit();
// }

// Get the page parameter from URL, default to dashboard
$page = $_GET['page'] ?? 'dashboard';

// Define available pages
$availablePages = [
    'dashboard' => 'user_dashboard.php',
    'profile' => 'edit-profile.php',
    'addresses' => 'addresses.php',
    'payment' => 'payment-method.php',
    'settings' => 'user_settings.php',
    'orders' => 'user_orders.php',
    'messages' => 'user_messages.php',
    'cart' => 'user_cart.php'
];

// Set the page file
$pageFile = $availablePages[$page] ?? 'user_dashboard.php';
$filePath = __DIR__ . '/components/pages/' . $pageFile;

// Check if file exists, otherwise load dashboard
if (!file_exists($filePath)) {
    $filePath = __DIR__ . '/components/pages/user_dashboard.php';
}

// Include the page
if (file_exists($filePath)) {
    include $filePath;
} else {
    echo '<h1>Page not found</h1>';
}
