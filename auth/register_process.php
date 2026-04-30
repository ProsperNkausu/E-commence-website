<?php
require_once '../config/db.php'; // your PDO connection

function redirect($page)
{
    header("Location: $page");
    exit;
}

/* ===============================
   UUID Generator
=================================*/
function uuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php?page=register');
}

/* ===============================
   Collect Data
=================================*/
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$password   = trim($_POST['password'] ?? '');
$confirm    = trim($_POST['confirm_password'] ?? '');
$phone      = trim($_POST['phone'] ?? '');

$errors = [];

/* ===============================
   Validation
=================================*/
if (!$first_name || !$last_name || !$email || !$password) {
    $errors[] = "Please fill in all required fields.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
}

if (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters.";
}

if ($password !== $confirm) {
    $errors[] = "Passwords do not match.";
}

if ($errors) {
    $_SESSION['register_errors'] = $errors;
    redirect('../index.php?page=register');
}

/* ===============================
   Database Logic
=================================*/
try {

    $pdo->beginTransaction();

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $_SESSION['register_errors'] = ["Email already registered."];
        redirect('../index.php?page=register');
    }

    // Create UUID
    $customer_id = uuid();

    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert customer
    $insert = $pdo->prepare("
        INSERT INTO customers
        (id, first_name, last_name, email, password_hash, phone)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $insert->execute([
        $customer_id,
        $first_name,
        $last_name,
        $email,
        $password_hash,
        $phone ?: NULL
    ]);

    // Create cart for customer
    $cart_id = uuid();

    $cart = $pdo->prepare("
        INSERT INTO carts (id, customer_id)
        VALUES (?, ?)
    ");

    $cart->execute([$cart_id, $customer_id]);

    $pdo->commit();

    /* ===============================
       Auto Login
    =================================*/
    $_SESSION['user_id'] = $customer_id;
    $_SESSION['user_name'] = $first_name . ' ' . $last_name;
    $_SESSION['role'] = 'customer';

    redirect('../users/index.php?page=dashboard');
} catch (PDOException $e) {

    $pdo->rollBack();

    error_log("Register error: " . $e->getMessage());

    $_SESSION['register_errors'] = [
        "Registration failed. Please try again."
    ];

    redirect('../index.php?page=register');
}
