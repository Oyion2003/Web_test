<?php

require_once 'models/BaseModel.php';

class Task extends BaseModel {

    public function getTasksByProject($project_id) {
        $sql = "SELECT t.*, u.name AS assignee_name
                FROM tasks t
                LEFT JOIN users u ON u.id = t.assigned_to
                WHERE t.project_id = ?
                ORDER BY FIELD(t.status, 'todo', 'in-progress', 'done'), t.due_date ASC, t.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$project_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTasksByStatus($project_id, $status) {
        $sql = "SELECT t.*, u.name AS assignee_name
                FROM tasks t
                LEFT JOIN users u ON u.id = t.assigned_to
                WHERE t.project_id = ? AND t.status = ?
                ORDER BY t.due_date ASC, t.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$project_id, $status]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO tasks
                (project_id, title, description, assigned_to, priority, due_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?);";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['project_id'],
            $data['title'],
            $data['description'],
            $data['assigned_to'] ?: null,
            $data['priority'],
            $data['due_date'],
            'todo'
        ]);

        return $this->db->lastInsertId();
    }

    public function find($id) {
        $sql = "SELECT * FROM tasks WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE tasks SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    public function getStatusCounts($project_id) {
        $sql = "SELECT status, COUNT(*) AS total
                FROM tasks
                WHERE project_id = ?
                GROUP BY status";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$project_id]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $counts = ['todo' => 0, 'in-progress' => 0, 'done' => 0];

        foreach ($rows as $row) {
            $counts[$row['status']] = (int)$row['total'];
        }

        return $counts;
    }

    public function getAssignedTaskCounts($project_id) {
        $sql = "SELECT u.id, u.name, COUNT(t.id) AS task_count
                FROM users u
                JOIN project_members pm ON pm.user_id = u.id
                LEFT JOIN tasks t ON t.assigned_to = u.id AND t.project_id = ?
                WHERE pm.project_id = ?
                GROUP BY u.id, u.name
                ORDER BY u.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$project_id, $project_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
