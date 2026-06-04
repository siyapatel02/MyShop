<?php

class User {

    private $conn;

    private $table = "users";

    public function __construct($db){

        $this->conn = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | FIND USER BY EMAIL
    |--------------------------------------------------------------------------
    */

    public function findByEmail($email){

        $query =

        "SELECT *

        FROM " . $this->table . "

        WHERE email = :email

        LIMIT 1";

        $stmt =
        $this->conn->prepare($query);

        $stmt->bindParam(

            ':email',

            $email
        );

        $stmt->execute();

        return $stmt->fetch(
            PDO::FETCH_ASSOC
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE USER
    |--------------------------------------------------------------------------
    */

    public function create($data){

        $query =

        "INSERT INTO

        " . $this->table . "

        (

            name,
            email,
            password,
            created_at

        )

        VALUES

        (

            :name,
            :email,
            :password,
            NOW()

        )";

        $stmt =
        $this->conn->prepare($query);

        $stmt->bindParam(
            ':name',
            $data['name']
        );

        $stmt->bindParam(
            ':email',
            $data['email']
        );

        $stmt->bindParam(
            ':password',
            $data['password']
        );

        return $stmt->execute();
    }
}