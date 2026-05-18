<?php

require_once 'models/BaseModel.php';

class Project extends BaseModel {

    public function create($data) {
        $sql = "INSERT INTO projects
                (workspace_id, name, description, deadline, color_label)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['workspace_id'],
            $data['name'],
            $data['description'],
            $data['deadline'],
            $data['color_label']
        ]);

        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE projects
                SET name = ?, description = ?, deadline = ?, color_label = ?
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['deadline'],
            $data['color_label'],
            $id
        ]);
    }

    public function addMembers($project_id, $members) {
        $sql = "INSERT INTO project_members (project_id, user_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);

        foreach ($members as $member) {
            $stmt->execute([$project_id, $member]);
        }
    }

    public function replaceMembers($project_id, $members) {
        $sql = "DELETE FROM project_members WHERE project_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$project_id]);

        $this->addMembers($project_id, $members);
    }

    public function getWorkspaceMembers($workspace_id) {
        $sql = "SELECT users.*
                FROM users
                JOIN workspace_members ON users.id = workspace_members.user_id
                WHERE workspace_members.workspace_id = ?
                ORDER BY users.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$workspace_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjects($workspace_id) {
        $sql = "SELECT p.*, 
                    (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id) AS total_tasks,
                    (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id AND t.status = 'done') AS completed_tasks
                FROM projects p
                WHERE p.workspace_id = ?
                AND p.is_archived = 0
                ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$workspace_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getArchivedProjects($workspace_id) {
        $sql = "SELECT p.*, 
                    (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id) AS total_tasks,
                    (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id AND t.status = 'done') AS completed_tasks
                FROM projects p
                WHERE p.workspace_id = ?
                AND p.is_archived = 1
                ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$workspace_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $sql = "SELECT * FROM projects WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function archive($id) {
        $sql = "UPDATE projects SET is_archived = 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getProjectMembers($project_id) {
        $sql = "SELECT users.*
                FROM users
                JOIN project_members ON users.id = project_members.user_id
                WHERE project_members.project_id = ?
                ORDER BY users.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$project_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectMemberIds($project_id) {
        $sql = "SELECT user_id FROM project_members WHERE project_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$project_id]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'user_id');
    }
}
