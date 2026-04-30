<?php
session_start();

// Check which type of user is logging out
if (isset($_SESSION['admin_id'])) {
    // === ADMIN LOGOUT ===
    unset(
        $_SESSION['admin_id'],
        $_SESSION['admin_first_name'],
        $_SESSION['admin_last_name'],
        $_SESSION['admin_email'],
        $_SESSION['admin_role']
    );

    $redirect = '../admin/index.php?page=login';   // Change this if your admin login page is different

} elseif (isset($_SESSION['user_id'])) {
    // === CUSTOMER LOGOUT - Using the old 'user_id' key ===
    unset(
        $_SESSION['user_id'],
        $_SESSION['first_name'],
        $_SESSION['last_name'],
        $_SESSION['email'],
        $_SESSION['role']
    );

    $redirect = '../index.php?page=home';
} else {
    // No one logged in
    $redirect = '../index.php?page=home';
}

// Optional: Destroy the entire session only if it's completely empty
if (empty($_SESSION)) {
    session_destroy();
}

header("Location: " . $redirect);
exit;
