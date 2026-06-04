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

if($status !== '' && !in_array($status, ['active', 'blocked'])){
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
            users.name LIKE :search
            OR users.email LIKE :search
            OR users.phone LIKE :search
        )";

        $params[':search'] = '%' . $search . '%';
    }

    if($status !== ''){

        $where[] = "
            users.status = :status
        ";

        $params[':status'] = $status;
    }

    $whereSql = '';

    if(count($where) > 0){
        $whereSql = 'WHERE ' . implode(' AND ', $where);
    }

    /*
    |--------------------------------------------------------------------------
    | COUNT USERS
    |--------------------------------------------------------------------------
    */

    $countQuery = "
        SELECT COUNT(*)
        FROM users
        $whereSql
    ";

    $countStmt = $db->prepare($countQuery);

    foreach($params as $key => $value){
        $countStmt->bindValue($key, $value);
    }

    $countStmt->execute();

    $totalRecords = (int)$countStmt->fetchColumn();

    /*
    |--------------------------------------------------------------------------
    | FETCH USERS
    |--------------------------------------------------------------------------
    */

    $query = "
        SELECT
            users.id,
            users.name,
            users.email,
            users.phone,
            users.dob,
            users.gender,
            users.status,
            users.created_at,
            COUNT(orders.id) AS total_orders,
            COALESCE(SUM(orders.total), 0) AS total_spent
        FROM users
        LEFT JOIN orders
        ON users.id = orders.user_id
        $whereSql
        GROUP BY
            users.id,
            users.name,
            users.email,
            users.phone,
            users.dob,
            users.gender,
            users.status,
            users.created_at
        ORDER BY users.id DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $db->prepare($query);

    foreach($params as $key => $value){
        $stmt->bindValue($key, $value);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($users as &$user){

        $user['id'] = (int)$user['id'];

        $user['total_orders'] = (int)$user['total_orders'];

        $user['total_spent'] = (float)$user['total_spent'];
    }

    $totalPages = (int)ceil($totalRecords / $limit);

    jsonResponse(
        true,
        'Users fetched successfully',
        [
            'count' => $totalRecords,
            'users' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_records' => $totalRecords,
                'total_pages' => $totalPages
            ]
        ]
    );

}catch(Exception $e){

    jsonResponse(
        false,
        'Failed to fetch users',
        [],
        500
    );
}