<?php
session_start();
require_once '../../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_GET['id'] ?? '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, phone, role_id, is_active 
        FROM admins WHERE id = ?
    ");
    $stmt->execute([$id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo json_encode(['success' => true, 'admin' => $admin]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Admin not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
exit;
?>