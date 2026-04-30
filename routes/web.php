<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Include and render the home.php file
    ob_start();
    include __DIR__ . '/../pages/home/home.php';
    $content = ob_get_clean();
    return response($content)->header('Content-Type', 'text/html');
});
