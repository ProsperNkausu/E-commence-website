<?php
session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

if (
    !isset($_SESSION['admin_id']) || !isset($_POST['csrf_token']) ||
    $_POST['csrf_token'] !== $_SESSION['csrf_token']
) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing CSRF token']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (empty($type) || empty($id) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
    exit;
}

try {
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    if ($type === 'customer') {
        $stmt = $pdo->prepare("UPDATE customers SET password_hash = ? WHERE id = ?");
        $stmt->execute([$password_hash, $id]);
        $success = $stmt->rowCount() > 0;
        $message = $success ? 'Customer password reset successfully' : 'Customer not found';
    } elseif ($type === 'admin') {
        if ($id === $_SESSION['admin_id']) {
            echo json_encode(['success' => false, 'message' => 'You cannot reset your own password here']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
        $stmt->execute([$password_hash, $id]);
        $success = $stmt->rowCount() > 0;
        $message = $success ? 'Admin password reset successfully' : 'Admin not found';
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid user type']);
        exit;
    }

    echo json_encode(['success' => $success, 'message' => $message]);
} catch (PDOException $e) {
    error_log("Reset password error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
