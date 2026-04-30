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

try {
    if ($type === 'customer') {
        $stmt = $pdo->prepare("
            UPDATE customers 
            SET status = IF(status = 'active', 'inactive', 'active') 
            WHERE id = ?
        ");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Customer status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
        }
    } elseif ($type === 'admin') {
        // Prevent deactivating yourself
        if ($id === $_SESSION['admin_id']) {
            echo json_encode(['success' => false, 'message' => 'You cannot change your own status']);
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE admins 
            SET is_active = NOT is_active 
            WHERE id = ?
        ");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Admin status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Admin not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid user type']);
    }
} catch (PDOException $e) {
    error_log("Toggle status error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
