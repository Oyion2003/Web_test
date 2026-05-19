<?php

require_once __DIR__ . '/BaseModel.php';

class ActivityLog extends BaseModel {

    public function getProjectActivity($project_id, $user_id = null) {
        $sql = "SELECT al.*, u.name AS user_name
                FROM activity_logs al
                JOIN users u ON u.id = al.user_id
                WHERE al.project_id = ?";

        if ($user_id) {
            $sql .= " AND al.user_id = ?";
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($user_id ? [$project_id, $user_id] : [$project_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
