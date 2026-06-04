<?php

class Cart {

    private $conn;

    private $table = "cart";

    public function __construct($db){

        $this->conn = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | ADD TO CART
    |--------------------------------------------------------------------------
    */

    public function add($userId, $productId){

        /*
        | CHECK EXISTING
        */

        $checkQuery =

        "SELECT *

        FROM cart

        WHERE user_id = :user_id

        AND product_id = :product_id

        LIMIT 1";

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

        $existing =
        $checkStmt->fetch(
            PDO::FETCH_ASSOC
        );

        /*
        | UPDATE QUANTITY
        */

        if($existing){

            $updateQuery =

            "UPDATE cart

            SET quantity = quantity + 1

            WHERE id = :id";

            $updateStmt =
            $this->conn->prepare($updateQuery);

            $updateStmt->bindParam(
                ':id',
                $existing['id']
            );

            return $updateStmt->execute();
        }

        /*
        | INSERT NEW
        */

        $query =

        "INSERT INTO cart

        (

            user_id,
            product_id,
            quantity

        )

        VALUES

        (

            :user_id,
            :product_id,
            1

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
    | GET CART ITEMS
    |--------------------------------------------------------------------------
    */

    public function getItems($userId){

        $query =

        "SELECT

            c.id as cart_id,

            c.quantity,

            p.*

        FROM cart c

        JOIN products p

        ON c.product_id = p.id

        WHERE c.user_id = :user_id";

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
    | REMOVE CART ITEM
    |--------------------------------------------------------------------------
    */

    public function remove($cartId){

        $query =

        "DELETE FROM cart

        WHERE id = :id";

        $stmt =
        $this->conn->prepare($query);

        $stmt->bindParam(
            ':id',
            $cartId
        );

        return $stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE QUANTITY
    |--------------------------------------------------------------------------
    */

    public function updateQuantity(

        $cartId,

        $quantity
    ){

        $query =

        "UPDATE cart

        SET quantity = :quantity

        WHERE id = :id";

        $stmt =
        $this->conn->prepare($query);

        $stmt->bindParam(
            ':quantity',
            $quantity
        );

        $stmt->bindParam(
            ':id',
            $cartId
        );

        return $stmt->execute();
    }


    /*
    |--------------------------------------------------------------------------
    | CLEAR CART
    |--------------------------------------------------------------------------
    */

    public function clear($userId){

        $query =

        "DELETE FROM cart

        WHERE user_id = :user_id";

        $stmt =
        $this->conn->prepare($query);

        $stmt->bindParam(
            ':user_id',
            $userId
        );

        return $stmt->execute();
    }
}
