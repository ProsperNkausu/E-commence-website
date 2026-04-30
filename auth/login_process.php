<?php
session_start();
require_once '../config/db.php';

function redirect($page)
{
    header("Location: $page");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php?page=login');
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// Preserve the email so it stays in the login form after error
$_SESSION['login_email'] = $email;

if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = 'Email and password are required.';
    redirect('../index.php?page=login');
}

try {
    // 1. ADMIN / STAFF LOGIN
    $stmt = $pdo->prepare("
        SELECT a.id, a.first_name, a.last_name, a.email, a.password_hash, r.name AS role
        FROM admins a
        JOIN admin_roles r ON a.role_id = r.id
        WHERE a.email = :email AND a.is_active = 1
        LIMIT 1
    ");
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password_hash'])) {
    $_SESSION['admin_id']          = $admin['id'];
    $_SESSION['admin_first_name']  = $admin['first_name'];
    $_SESSION['admin_last_name']   = $admin['last_name'];
    $_SESSION['admin_email']       = $admin['email'];
    $_SESSION['admin_role']        = $admin['role'];

    $pdo->prepare("UPDATE admins SET last_login_at = NOW() WHERE id = :id")
        ->execute(['id' => $admin['id']]);

    $_SESSION['success_message'] = "Welcome back, " . htmlspecialchars($admin['first_name']) . "! You have successfully logged in.";
    
    unset($_SESSION['login_error'], $_SESSION['login_email']);
    redirect('../admin/index.php?page=dashboard');
}

    // 2. CUSTOMER LOGIN
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, password_hash
        FROM customers
        WHERE email = :email
        LIMIT 1
    ");
    $stmt->execute(['email' => $email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer && password_verify($password, $customer['password_hash'])) {
        $_SESSION['user_id']      = $customer['id'];
        $_SESSION['first_name']   = $customer['first_name'];
        $_SESSION['last_name']    = $customer['last_name'];
        $_SESSION['email']        = $customer['email'];
        $_SESSION['role']         = 'customer';

        $_SESSION['success_message'] = "Welcome back, " . htmlspecialchars($customer['first_name']) . "! You are now logged in.";

        unset($_SESSION['login_error'], $_SESSION['login_email']);
        redirect('../index.php?page=home');
    }

    // Invalid credentials
    $_SESSION['login_error'] = 'Invalid email or password. Please check your credentials and try again.';
    redirect('../index.php?page=login');
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    $_SESSION['login_error'] = 'An unexpected error occurred. Please try again later.';
    redirect('../index.php?page=login');
}
