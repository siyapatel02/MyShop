<?php

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/jwt.php';

function getBearerToken(){

    $headers = getallheaders();

    if(!isset($headers['Authorization'])){
        return null;
    }

    if(!preg_match('/Bearer\s+(\S+)/', $headers['Authorization'], $matches)){
        return null;
    }

    return $matches[1];
}

$token = getBearerToken();

if(!$token){
    jsonResponse(false, 'Unauthorized', [], 401);
}

$admin = verifyToken($token);

if(!$admin || empty($admin['id']) || ($admin['role'] ?? '') !== 'admin'){
    jsonResponse(false, 'Admin access required', [], 403);
}

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$page = (int)($_GET['page'] ?? 1);
$limit = (int)($_GET['limit'] ?? 10);

if($page < 1){
    $page = 1;
}

if($limit < 1){
    $limit = 10;
}

if($limit > 50){
    $limit = 50;
}

$allowedStatus = [
    'pending',
    'shipped',
    'delivered'
];

if($status !== '' && !in_array($status, $allowedStatus)){
    jsonResponse(false, 'Invalid status filter', [], 422);
}

$offset = ($page - 1) * $limit;

try{

    $database = new Database();
    $db = $database->connect();

    $where = [];
    $params = [];

    if($search !== ''){

        $where[] = "
        (
            orders.id LIKE :search
            OR users.name LIKE :search
            OR users.email LIKE :search
        )";

        $params[':search'] = '%' . $search . '%';
    }

    if($status !== ''){

        $where[] = "
            orders.status = :status
        ";

        $params[':status'] = $status;
    }

    $whereSql = '';

    if(count($where) > 0){
        $whereSql = 'WHERE ' . implode(' AND ', $where);
    }

    $countQuery = "
        SELECT COUNT(*)
        FROM orders
        LEFT JOIN users
        ON orders.user_id = users.id
        $whereSql
    ";

    $countStmt = $db->prepare($countQuery);

    foreach($params as $key => $value){
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();

    $totalRecords = (int)$countStmt->fetchColumn();

    $query = "
        SELECT
            orders.id,
            orders.user_id,
            users.name AS customer_name,
            users.email AS customer_email,
            orders.total,
            orders.address,
            orders.status,
            orders.payment_method,
            orders.created_at
        FROM orders
        LEFT JOIN users
        ON orders.user_id = users.id
        $whereSql
        ORDER BY orders.id DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $db->prepare($query);

    foreach($params as $key => $value){
        $stmt->bindValue($key, $value);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($orders as &$order){

        $order['id'] = (int)$order['id'];
        $order['user_id'] = $order['user_id'] !== null
            ? (int)$order['user_id']
            : null;

        $order['total'] = (float)$order['total'];
    }

    $totalPages = (int)ceil($totalRecords / $limit);

    jsonResponse(
        true,
        'Orders fetched successfully',
        [
            'orders' => $orders,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_records' => $totalRecords,
                'total_pages' => $totalPages
            ]
        ]
    );

}catch(Exception $e){

    jsonResponse(false, 'Failed to fetch orders', [], 500);
}