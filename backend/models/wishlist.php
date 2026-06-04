<?php

class Wishlist {

    private $conn;

    private $table = "wishlist";

    public function __construct($db){

        $this->conn = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | ADD TO WISHLIST
    |--------------------------------------------------------------------------
    */

    public function add($userId, $productId){

        $checkQuery =

        "SELECT id

        FROM wishlist

        WHERE user_id = :user_id

        AND product_id = :product_id";

        $checkStmt =
        $this->conn->prepare($checkQuery);

        $checkStmt->bindParam(
            ':user_id',
            $userId
        );

        $checkStmt->bindParam(
            ':product_id',
            $productId
        );

        $checkStmt->execute();

        if($checkStmt->rowCount() > 0){

            return false;
        }

        $query =

        "INSERT INTO wishlist

        (

            user_id,
            product_id,
            created_at

        )

        VALUES

        (

            :user_id,
            :product_id,
            NOW()

        )";

        $stmt =
        $this->conn->prepare($query);

        $stmt->bindParam(
            ':user_id',
            $userId
        );

        $stmt->bindParam(
            ':product_id',
            $productId
        );

        return $stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | GET WISHLIST
    |--------------------------------------------------------------------------
    */

    public function getItems($userId){

        $query =

        "SELECT

            w.id as wishlist_id,

            p.*

        FROM wishlist w

        JOIN products p

        ON w.product_id = p.id

        WHERE w.user_id = :user_id

        ORDER BY w.id DESC";

        $stmt =
        $this->conn->prepare($query);

        $stmt->bindParam(
            ':user_id',
            $userId
        );

        $stmt->execute();

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REMOVE WISHLIST
    |--------------------------------------------------------------------------
    */

    public function remove($wishlistId){

        $query =

        "DELETE FROM wishlist

        WHERE id = :id";

        $stmt =
        $this->conn->prepare($query);

        $stmt->bindParam(
            ':id',
            $wishlistId
        );

        return $stmt->execute();
    }
}