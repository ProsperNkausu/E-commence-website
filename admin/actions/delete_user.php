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
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? '';

if (empty($type) || empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

// Prevent deleting yourself
if ($type === 'admin' && $id === $_SESSION['admin_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
    exit;
}

try {
    if ($type === 'customer') {
        // Optional: You can also delete related carts, orders, etc. if needed
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Customer account deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
        }
    } elseif ($type === 'admin') {
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Admin account deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Admin not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid user type']);
    }
} catch (PDOException $e) {
    error_log("Delete user error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
