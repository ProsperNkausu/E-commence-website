<?php
require_once __DIR__ . '/../../config/db.php';

// GET params
$status = $_GET['status'] ?? 'all';
$date   = $_GET['date'] ?? 'all';
$sort   = $_GET['sort'] ?? 'newest';
$page   = max(1, (int)($_GET['p'] ?? 1));

$limit = 10;
$offset = ($page - 1) * $limit;

// ================================
// FILTER FUNCTION
// ================================
function applyFilters(&$sql, &$params, $status, $date)
{
    if ($status !== 'all') {
        $sql .= " AND o.status = ?";
        $params[] = $status;
    }

    if ($date === 'today') {
        $sql .= " AND DATE(o.created_at) = CURDATE()";
    } elseif ($date === 'week') {
        $sql .= " AND YEARWEEK(o.created_at,1) = YEARWEEK(CURDATE(),1)";
    } elseif ($date === 'month') {
        $sql .= " AND MONTH(o.created_at) = MONTH(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE())";
    } elseif ($date === 'year') {
        $sql .= " AND YEAR(o.created_at) = YEAR(CURDATE())";
    }
}

// ================================
// COUNT TOTAL ORDERS
// ================================
$countSql = "SELECT COUNT(*) FROM orders o WHERE 1=1";
$countParams = [];
applyFilters($countSql, $countParams, $status, $date);

$stmt = $pdo->prepare($countSql);
$stmt->execute($countParams);
$totalOrders = $stmt->fetchColumn();
$totalPages = max(1, ceil($totalOrders / $limit));

// ================================
// FETCH ORDERS
// ================================
$sql = "SELECT o.*, c.first_name, c.last_name
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        WHERE 1=1";

$params = [];
applyFilters($sql, $params, $status, $date);

// SORT
switch ($sort) {
    case 'oldest':
        $sql .= " ORDER BY o.created_at ASC";
        break;
    case 'amount-high':
        $sql .= " ORDER BY o.total_amount DESC";
        break;
    case 'amount-low':
        $sql .= " ORDER BY o.total_amount ASC";
        break;
    default:
        $sql .= " ORDER BY o.created_at DESC";
}

$sql .= " LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// RETURN JSON
echo json_encode([
    'success' => true,
    'orders' => $orders,
    'totalPages' => $totalPages,
    'currentPage' => $page
]);
