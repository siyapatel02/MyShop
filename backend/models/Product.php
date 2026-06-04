<?php

class Product {

    private $conn;

    private $table = "products";

    public function __construct($db){

        $this->conn = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | GET ALL PRODUCTS
    |--------------------------------------------------------------------------
    */

    public function getAll(){

        $query =

        "SELECT

            p.*,

            c.name as category_name

        FROM products p

        LEFT JOIN categories c

        ON p.category_id = c.id

        ORDER BY p.id DESC";

        $stmt =
        $this->conn->prepare($query);

        $stmt->execute();

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET PRODUCT DETAILS
    |--------------------------------------------------------------------------
    */

    public function getById($id){

        $query =

        "SELECT

            p.*,

            c.name as category_name

        FROM products p

        LEFT JOIN categories c

        ON p.category_id = c.id

        WHERE p.id = :id

        LIMIT 1";

        $stmt =
        $this->conn->prepare($query);

        $stmt->bindParam(
            ':id',
            $id
        );

        $stmt->execute();

        return $stmt->fetch(
            PDO::FETCH_ASSOC
        );
    }
}