<?php

require_once __DIR__ . '/BaseModel.php';

class Comment extends BaseModel {

    public function create($task_id, $user_id, $body) {
        $sql = "INSERT INTO comments (task_id, user_id, body) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$task_id, $user_id, $body]);
        return $this->db->lastInsertId();
    }

    public function delete($id) {
        $sql = "DELETE FROM comments WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function find($id) {
        $sql = "SELECT * FROM comments WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByTaskId($task_id) {
        $sql = "SELECT c.*, u.name AS author_name
                FROM comments c
                JOIN users u ON u.id = c.user_id
                WHERE c.task_id = ?
                ORDER BY c.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$task_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
