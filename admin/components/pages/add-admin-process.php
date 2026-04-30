<?php
// add-admin-process.php
session_start();
require_once __DIR__ . '/../../../config/db.php';

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get form data
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$role_id    = trim($_POST['role_id'] ?? '');
$password   = $_POST['password'] ?? '';
$is_active  = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

// Basic validation
if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($role_id)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'An admin with this email already exists']);
        exit;
    }

    // Check if role exists
    $stmt = $pdo->prepare("SELECT id FROM admin_roles WHERE id = ?");
    $stmt->execute([$role_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Invalid role selected']);
        exit;
    }

    // Generate UUID for new admin
    $new_id = sprintf(
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

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new admin
    $stmt = $pdo->prepare("
        INSERT INTO admins 
        (id, role_id, first_name, last_name, email, password_hash, phone, is_active, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $success = $stmt->execute([
        $new_id,
        $role_id,
        $first_name,
        $last_name,
        $email,
        $password_hash,
        $phone ?: null,
        $is_active
    ]);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'New admin account created successfully!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create admin account'
        ]);
    }
} catch (Exception $e) {
    error_log("Add Admin Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
