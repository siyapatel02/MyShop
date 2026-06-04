<?php

class ProductService {

    /*
    |--------------------------------------------------------------------------
    | FORMAT PRODUCT
    |--------------------------------------------------------------------------
    */

    public static function format($product){

        $product['image'] =

        'http://' . $_SERVER['HTTP_HOST'] . '/' . (explode('/', trim($_SERVER['SCRIPT_NAME'], '/'))[0] ?? '') . '/backend/uploads/products/' .

        $product['image'];

        return $product;
    }
}