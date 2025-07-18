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
        $query = "UPDATE " . $this->table_name . " 
                SET username=:username, phone=:phone, document=:document 
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->document = htmlspecialchars(strip_tags($this->document));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":document", $this->document);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
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
                    COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END), 0) as deposit_sum,
                    COALESCE(SUM(CASE WHEN type = 'withdraw' THEN amount ELSE 0 END), 0) as withdraw_sum
                  FROM transactions WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>