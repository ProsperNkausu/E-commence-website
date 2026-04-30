<?php
// ================================
// user-update-pass.php
// Handles password update via API (JSON)
// ================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------------------
// Check user session
// ---------------------------
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not logged in.'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

// ---------------------------
// Only accept POST
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// ---------------------------
// Get input
// ---------------------------
$currentPassword = $_POST['current_password'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// ---------------------------
// Validate input
// ---------------------------
if (!$currentPassword || !$newPassword || !$confirmPassword) {
    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required.'
    ]);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode([
        'status' => 'error',
        'message' => 'New passwords do not match.'
    ]);
    exit;
}

if (strlen($newPassword) < 6) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Password must be at least 6 characters.'
    ]);
    exit;
}

// ---------------------------
// Update password
// ---------------------------
try {
    // 1. Fetch current hash
    $stmt = $pdo->prepare("SELECT password_hash FROM customers WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'status' => 'error',
            'message' => 'User not found.'
        ]);
        exit;
    }

    // 2. Verify current password
    if (!password_verify($currentPassword, $user['password_hash'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Current password is incorrect.'
        ]);
        exit;
    }

    // 3. Prevent reuse of current password
    if (password_verify($newPassword, $user['password_hash'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'New password cannot be the same as the current password.'
        ]);
        exit;
    }

    // 4. Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // 5. Update in DB
    $update = $pdo->prepare("UPDATE customers SET password_hash = ? WHERE id = ?");
    $update->execute([$hashedPassword, $userId]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Password updated successfully.'
    ]);
    exit;
} catch (Exception $e) {
    error_log("Password update error: " . $e->getMessage());

    echo json_encode([
        'status' => 'error',
        'message' => 'Server error.'
    ]);
    exit;
}
