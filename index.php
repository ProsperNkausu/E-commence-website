<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Navigate to page component
 * Laravel-style routing function
 */
function navigate(string $page = 'home'): void
{
    $basePath = __DIR__ . '/components/pages/';
    $filePath = $basePath . $page . '.php';

    // Verify file exists
    if (!file_exists($filePath)) {
        http_response_code(404);
        include __DIR__ . '/components/pages/404.php';
        exit;
    }

    // Include the component
    require_once $filePath;
}

/**
 * Load page on initialization
 */
function loadPage(): void
{
    $requestedPage = $_GET['page'] ?? 'home';
    navigate($requestedPage);
}

// Run on page load
loadPage();
