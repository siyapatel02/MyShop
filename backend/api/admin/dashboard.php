<?php

require_once __DIR__ . '/../../middleware/adminMiddleware.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

try{

    $database = new Database();
    $db = $database->connect();

    $users = $db->query("
        SELECT COUNT(*) AS total
        FROM users
    ")->fetch(PDO::FETCH_ASSOC);

    $products = $db->query("
        SELECT COUNT(*) AS total
        FROM products
    ")->fetch(PDO::FETCH_ASSOC);

    $orders = $db->query("
        SELECT COUNT(*) AS total
        FROM orders
    ")->fetch(PDO::FETCH_ASSOC);

    $sales = $db->query("
        SELECT COALESCE(SUM(total), 0) AS total
        FROM orders
    ")->fetch(PDO::FETCH_ASSOC);

    $statusStmt = $db->query("
        SELECT
            status,
            COUNT(*) AS total
        FROM orders
        GROUP BY status
    ");

    $orderStatus = [
        'pending' => 0,
        'shipped' => 0,
        'delivered' => 0
    ];

    while($row = $statusStmt->fetch(PDO::FETCH_ASSOC)){

        $orderStatus[$row['status']] =
        (int)$row['total'];
    }

    $monthlyStmt = $db->query("
        SELECT
            DATE_FORMAT(created_at, '%Y-%m') AS month,
            COALESCE(SUM(total), 0) AS sales
        FROM orders
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
        LIMIT 12
    ");

    $monthlySales =
    $monthlyStmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(
        true,
        'Dashboard data fetched successfully',
        [
            'cards' => [
                'users' => (int)$users['total'],
                'products' => (int)$products['total'],
                'orders' => (int)$orders['total'],
                'sales' => (float)$sales['total']
            ],
            'charts' => [
                'order_status' => $orderStatus,
                'monthly_sales' => $monthlySales
            ]
        ]
    );

}catch(Exception $e){

    jsonResponse(
        false,
        'Failed to fetch dashboard data',
        [],
        500
    );
}