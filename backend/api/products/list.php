<?php

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

$search = trim($_GET['search'] ?? '');
$categoryId = (int)($_GET['category_id'] ?? 0);
$page = (int)($_GET['page'] ?? 1);
$limit = (int)($_GET['limit'] ?? 8);
$minPrice = trim($_GET['min_price'] ?? '');
$maxPrice = trim($_GET['max_price'] ?? '');
$rating = trim($_GET['rating'] ?? '');
$sort = trim($_GET['sort'] ?? 'default');

if($page < 1){ $page = 1; }
if($limit < 1){ $limit = 8; }
if($limit > 50){ $limit = 50; }

$offset = ($page - 1) * $limit;

try{
    $database = new Database();
    $db = $database->connect();

    $where = [];
    $having = [];
    $params = [];

    if($search !== ''){
        $words = preg_split('/\s+/', $search);
        $searchParts = [];
        foreach($words as $index => $word){
            $key = ':search' . $index;
            $searchParts[] = "(products.name LIKE $key OR products.description LIKE $key OR categories.name LIKE $key)";
            $params[$key] = '%' . $word . '%';
        }
        $where[] = '(' . implode(' OR ', $searchParts) . ')';
    }

    if($categoryId > 0){
        $where[] = "(products.category_id = :category_id OR products.category_id IN (SELECT id FROM categories WHERE parent_id = :category_id))";
        $params[':category_id'] = $categoryId;
    }

    if($minPrice !== '' && is_numeric($minPrice)){
        $where[] = 'products.price >= :min_price';
        $params[':min_price'] = $minPrice;
    }

    if($maxPrice !== '' && is_numeric($maxPrice)){
        $where[] = 'products.price <= :max_price';
        $params[':max_price'] = $maxPrice;
    }

    if($rating !== '' && is_numeric($rating)){
        $having[] = 'average_rating >= :rating';
        $params[':rating'] = $rating;
    }

    $whereSql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    $havingSql = count($having) > 0 ? 'HAVING ' . implode(' AND ', $having) : '';

    $orderSql = 'ORDER BY products.id DESC';
    if($sort === 'price_low_high'){
        $orderSql = 'ORDER BY products.price ASC';
    }elseif($sort === 'price_high_low'){
        $orderSql = 'ORDER BY products.price DESC';
    }elseif($sort === 'newest'){
        $orderSql = 'ORDER BY products.created_at DESC, products.id DESC';
    }elseif($sort === 'high_rated'){
        $orderSql = 'ORDER BY average_rating DESC, total_reviews DESC';
    }

    $baseSelect = "
        FROM products
        LEFT JOIN categories ON products.category_id = categories.id
        LEFT JOIN reviews ON reviews.product_id = products.id AND reviews.status = 'active'
        $whereSql
        GROUP BY products.id, products.name, products.description, products.price, products.image, products.category_id, categories.name, products.created_at
        $havingSql
    ";

    $countQuery = "SELECT COUNT(*) FROM (SELECT products.id, COALESCE(AVG(reviews.rating), 0) AS average_rating $baseSelect) AS filtered_products";
    $countStmt = $db->prepare($countQuery);
    foreach($params as $key => $value){ $countStmt->bindValue($key, $value); }
    $countStmt->execute();
    $totalRecords = (int)$countStmt->fetchColumn();

    $query = "
        SELECT products.id, products.name, products.description, products.price, products.image, products.category_id,
               categories.name AS category_name, products.created_at,
               COALESCE(AVG(reviews.rating), 0) AS average_rating,
               COUNT(reviews.id) AS total_reviews
        $baseSelect
        $orderSql
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $db->prepare($query);
    foreach($params as $key => $value){ $stmt->bindValue($key, $value); }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))) . '/uploads/products/';

    foreach($products as &$product){
        $product['id'] = (int)$product['id'];
        $product['price'] = (float)$product['price'];
        $product['category_id'] = $product['category_id'] !== null ? (int)$product['category_id'] : null;
        $product['average_rating'] = round((float)$product['average_rating'], 1);
        $product['total_reviews'] = (int)$product['total_reviews'];
        $product['image_url'] = $baseUrl . $product['image'];
        $product['image'] = $product['image_url'];
    }

    $totalPages = (int)ceil($totalRecords / $limit);

    jsonResponse(true, 'Products fetched successfully', [
        'products' => $products,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total_records' => $totalRecords,
            'total_pages' => $totalPages
        ]
    ]);
}catch(Exception $e){
    jsonResponse(false, 'Failed to fetch products', [], 500);
}
