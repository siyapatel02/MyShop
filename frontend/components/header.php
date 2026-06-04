<?php
$currentPath = $_SERVER['PHP_SELF'] ?? '';
$isPagesFolder = strpos($currentPath, '/pages/') !== false;
$assetBase = $isPagesFolder ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MyShop</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?= $assetBase ?>assets/css/global.css?v=6">
<link rel="stylesheet" href="<?= $assetBase ?>assets/css/layout.css?v=6">
<link rel="stylesheet" href="<?= $assetBase ?>assets/css/components/navbar.css?v=6">
<link rel="stylesheet" href="<?= $assetBase ?>assets/css/components/product-card.css?v=6">
<link rel="stylesheet" href="<?= $assetBase ?>assets/css/pages/product.css?v=6">
<link rel="stylesheet" href="<?= $assetBase ?>assets/css/pages/cart.css?v=6">
<link rel="stylesheet" href="<?= $assetBase ?>assets/css/pages/checkout.css?v=6">
<link rel="stylesheet" href="<?= $assetBase ?>assets/css/pages/orders.css?v=6">
<link rel="stylesheet" href="<?= $assetBase ?>assets/css/responsive.css?v=6">
</head>
<body>
