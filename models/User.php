<?php

require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel {

    public function register($name, $email, $password) {
        try {
            $sql = "INSERT INTO users (name, email, password_hash, created_at)
                    VALUES (?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT)
            ]);
        } catch (PDOException $e) {
            die("DB ERROR: " . $e->getMessage());
        }
    }

    public function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
