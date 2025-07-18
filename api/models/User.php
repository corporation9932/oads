<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $phone;
    public $document;
    public $balance;
    public $created_at;
    public $is_admin;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET username=:username, email=:email, password=:password, 
                    phone=:phone, document=:document, balance=0, created_at=NOW()";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->document = htmlspecialchars(strip_tags($this->document));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":document", $this->document);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function emailExists() {
        $query = "SELECT id, username, email, password, phone, document, balance, is_admin 
                FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        $num = $stmt->rowCount();

        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->phone = $row['phone'];
            $this->document = $row['document'];
            $this->balance = $row['balance'];
            $this->is_admin = $row['is_admin'];
            return true;
        }
        return false;
    }

    public function update() {
        $fields = [];
        $params = [];
        
        if(isset($this->username)) {
            $fields[] = "username=:username";
            $params[':username'] = htmlspecialchars(strip_tags($this->username));
        }
        if(isset($this->phone)) {
            $fields[] = "phone=:phone";
            $params[':phone'] = htmlspecialchars(strip_tags($this->phone));
        }
        if(isset($this->document)) {
            $fields[] = "document=:document";
            $params[':document'] = htmlspecialchars(strip_tags($this->document));
        }
        if(isset($this->email)) {
            $fields[] = "email=:email";
            $params[':email'] = htmlspecialchars(strip_tags($this->email));
        }

        if(empty($fields)) return false;

        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $fields) . " WHERE id=:id";
        $params[':id'] = $this->id;

        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    public function updateBalance($amount) {
        $query = "UPDATE " . $this->table_name . " SET balance = balance + :amount WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function getStats() {
        $query = "SELECT 
                    COALESCE(SUM(CASE WHEN type = 'deposit' AND status = 'completed' THEN amount ELSE 0 END), 0) as deposit_sum,
                    COALESCE(SUM(CASE WHEN type = 'withdraw' AND status = 'completed' THEN amount ELSE 0 END), 0) as withdraw_sum
                  FROM transactions WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserById($id) {
        $query = "SELECT id, username, email, phone, document, balance, created_at, is_admin 
                FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->document = $row['document'];
            $this->balance = $row['balance'];
            $this->is_admin = $row['is_admin'];
            return true;
        }
        return false;
    }
}
?>