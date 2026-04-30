<?php
session_start();
require_once '../../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$admin_id     = $_POST['admin_id'] ?? '';
$first_name   = trim($_POST['first_name'] ?? '');
$last_name    = trim($_POST['last_name'] ?? '');
$email        = trim($_POST['email'] ?? '');
$phone        = trim($_POST['phone'] ?? '');
$role_id      = $_POST['role_id'] ?? '';
$is_active    = $_POST['is_active'] ?? '1';

if (empty($admin_id) || empty($first_name) || empty($last_name) || empty($email) || empty($role_id)) {
    echo json_encode(['success' => false, 'message' => 'Required fields missing']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE admins 
        SET first_name = ?, last_name = ?, email = ?, phone = ?, 
            role_id = ?, is_active = ?
        WHERE id = ?
    ");

    $stmt->execute([$first_name, $last_name, $email, $phone, $role_id, $is_active, $admin_id]);

    echo json_encode(['success' => true, 'message' => 'Admin updated successfully!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()]);
}
exit;
?>