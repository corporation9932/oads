<?php
class Transaction {
    private $conn;
    private $table_name = "transactions";

    public $id;
    public $user_id;
    public $type;
    public $amount;
    public $status;
    public $payment_method;
    public $external_id;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET user_id=:user_id, type=:type, amount=:amount, 
                    status=:status, payment_method=:payment_method, 
                    external_id=:external_id, created_at=NOW()";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":type", $this->type);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":payment_method", $this->payment_method);
        $stmt->bindParam(":external_id", $this->external_id);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function getUserTransactions($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE user_id = :user_id ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
}
?>