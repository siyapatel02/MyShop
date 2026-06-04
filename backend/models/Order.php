<?php

class Order {

    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    public function create($userId, $total, $address, $paymentMethod){
        $query = "INSERT INTO orders (user_id, total, address, status, payment_method, created_at, updated_at)
                  VALUES (:user_id, :total, :address, 'pending', :payment_method, NOW(), NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(':total', $total);
        $stmt->bindValue(':address', $address);
        $stmt->bindValue(':payment_method', $paymentMethod);

        if($stmt->execute()){
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function addItem($orderId, $productId, $quantity, $price){
        $query = "INSERT INTO order_items (order_id, product_id, quantity, price)
                  VALUES (:order_id, :product_id, :quantity, :price)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':order_id', (int)$orderId, PDO::PARAM_INT);
        $stmt->bindValue(':product_id', (int)$productId, PDO::PARAM_INT);
        $stmt->bindValue(':quantity', (int)$quantity, PDO::PARAM_INT);
        $stmt->bindValue(':price', $price);

        return $stmt->execute();
    }

    public function addItems($orderId, $items){
        foreach($items as $item){
            $this->addItem(
                $orderId,
                $item['product_id'] ?? $item['id'],
                $item['quantity'],
                $item['price']
            );
        }

        return true;
    }

    public function history($userId){
        $query = "SELECT
                    o.*,
                    (SELECT COALESCE(SUM(oi.quantity), 0)
                     FROM order_items oi
                     WHERE oi.order_id = o.id) AS total_items,
                    (SELECT GROUP_CONCAT(DISTINCT p.name SEPARATOR '||')
                     FROM order_items oi
                     JOIN products p ON oi.product_id = p.id
                     WHERE oi.order_id = o.id) AS product_names
                  FROM orders o
                  WHERE o.user_id = :user_id
                  ORDER BY o.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByUser($orderId, $userId){
        $query = "SELECT * FROM orders WHERE id = :order_id AND user_id = :user_id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':order_id', (int)$orderId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function details($orderId){
        $query = "SELECT
                    oi.*,
                    p.id AS product_id,
                    p.name,
                    p.image
                  FROM order_items oi
                  JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':order_id', (int)$orderId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSavedAddresses($userId){
        $query = "SELECT * FROM user_addresses WHERE user_id = :user_id ORDER BY is_default DESC, id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAddressByUser($addressId, $userId){
        $query = "SELECT * FROM user_addresses WHERE id = :id AND user_id = :user_id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', (int)$addressId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveAddress($userId, $fullname, $phone, $pincode, $addressLine, $city, $state, $addressType){
        $this->conn->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = :user_id")
            ->execute([':user_id' => (int)$userId]);

        $query = "INSERT INTO user_addresses
                  (user_id, fullname, phone, pincode, address_line, city, state, address_type, is_default, label, country)
                  VALUES
                  (:user_id, :fullname, :phone, :pincode, :address_line, :city, :state, :address_type, 1, :label, 'India')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(':fullname', $fullname);
        $stmt->bindValue(':phone', $phone);
        $stmt->bindValue(':pincode', $pincode);
        $stmt->bindValue(':address_line', $addressLine);
        $stmt->bindValue(':city', $city);
        $stmt->bindValue(':state', $state);
        $stmt->bindValue(':address_type', $addressType);
        $stmt->bindValue(':label', $addressType);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function formatAddress($addr){
        return trim($addr['fullname']) . ', ' .
               trim($addr['address_line']) . ', ' .
               trim($addr['city']) . ', ' .
               trim($addr['state']) . ' - ' .
               trim($addr['pincode']) . ' (' .
               trim($addr['phone']) . ')';
    }
}



